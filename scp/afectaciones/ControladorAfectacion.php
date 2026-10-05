<?php

declare(strict_types=1);

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();

require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../lib/Math.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/funciones.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAfectacion.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaAfectacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/Afectaciones.php';
require_once dirname(__FILE__) . '/../../ent/scp/Contables.php';
require_once dirname(__FILE__) . '/../../ent/scp/Compromisos.php';
require_once dirname(__FILE__) . '/../../ent/scp/Presupuestales.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once dirname(__FILE__) . '/ValidaGarantias.php';

/**
 *Autor @Eduardo Venegas              Fecha de Creación:  8/11/2018
 *Se modifico las variables de sesion y de servicios, ahora se instancian en el contructor.
 *Se actualizo las consultas, por el modo estricto que se emplea en la base de datos.
 *Se actualizo la ruta del reporte rep_afectaciones.php
 *Se corrigio el pdf, detalles de su vista, como el pie de pagina.
 *Se corrigio detalles a la vista en general, como el paginado, ahora se hace cada 10 afectaciones, por ejemplo.
 *Se detectaron situaciones con los procedimientos de la base de datos, los cuales se corrigieron.
 *Se actualio referencias a fechas 2018 a 2019, desde variables de sesion a consultas a tablas.
Ultima Fecha de actualización 03/12/18

 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * update: 21-ene-2019
 * Comment:
 * Se cambia consulta para obtener ncc_gpo, ncc_ur, ncc_clc dentro del metodo pagadoras
 * Se corrigen problemas ux (no se dibuja cabecera en tipo: C)
 * Se cambia el metodo en el backindex desde el detalle mov. contable
 *
 * @update Japhet Mata <japhet.mata@dgtid.oaxaca.gob.mx>
 * update: Febrero 2022
 * Se ajustan algunas instrucciones para compatibilidad con  php 8.0
 */

class ControladorAfectacion extends WebResponse
{
    public $escritura;
    public $cargaMasiva;
    public $pantalla;
    public $gpo_id;
    public $ur_id;
    public $tcl_id;
    public $ip;
    public $tclObra;
    public $amortizable;
    public $usr_id;
    public $nvj;
    public vistaAfectacion $vista;
    public modelAfectacion $model;
    public $grupo2;
    public $ue;
    public $clasAdmUe;
    public $clasAdmEpg;
    public $tipo;
    public $princesa;
    public $tclId;
    public $urs318;

    public function __construct()
    {
        parent::__construct();
        $this->escritura = tienePermiso('s.2.1.2');
        $this->cargaMasiva = tienePermiso('s.2.1.23');
        $this->pantalla = 'scp/afectaciones/ControladorAfectacion.php';
        $this->gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->tclObra = ['F', 'G', 'H'];
        $this->amortizable = ['A', 'B', 'C', 'F', 'G'];
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->vista = new vistaAfectacion();
        $this->model = new modelAfectacion();
        $this->grupo2 = [105, 530, 531, 540, 553];
        $this->vista->setPrincesas($this->grupo2);
        $this->ue = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '';
        $this->clasAdmUe = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : '';
        $this->clasAdmEpg = isset($_SESSION[_S]['clasAdm-epg']) ? $_SESSION[_S]['clasAdm-epg'] : '';
        $this->tipo = isset($_SESSION[_S]['tipo']) ? $_SESSION[_S]['tipo'] : '';
        $this->princesa = in_array($this->gpo_id . $this->ur_id, $this->grupo2) ? true : false;
        $this->tclId = isset($_SESSION[_S]['tcl_id']) ? $_SESSION[_S]['tcl_id'] : null;

        if (!isset($_SESSION[_S]['tcl_id'])) {
            $this->model->setTcls();
        }

        if (!isset($_SESSION[_S]['ursAdministracion']) && $this->gpo_id . $this->ur_id == '118') {
            $this->model->setUrAdministracion();
            $this->urs318 = isset($_SESSION[_S]['ursAdmon']) ? $_SESSION[_S]['ursAdmon'] : [];
        }

        switch ($this->nvj) {
            case 1:
            case 2:
            case 4:
                switch ($this->metodo) {
                    case 'nuevo':
                        $this->nuevo();
                        break;
                    case 'checaFolio':
                        $this->checaFolio();
                        break;
                    case 'duplicate':
                        $this->duplicate();
                        break;
                    case 'imprimir':
                        $this->imprimir();
                        break;
                    case 'llenaTablaAfectaciones':
                        $this->llenaTablaAfectaciones();
                        break;
                    case 'llenaTablaNumerosdeControl':
                        $this->llenaTablaNumerosdeControl();
                        break;
                    case 'comboNomina':
                        $this->comboNomina();
                        break;
                    case 'afectacion':
                        $this->afectacion();
                        break;
                    case 'proveedor':
                        $this->proveedor();
                        break;
                    case 'clcPagar':
                        $this->clcPagar();
                        break;
                    case 'abcAfe':
                        $this->abcAfe();
                        break;
                    case 'abcContable':
                        $this->abcContable();
                        break;
                    case 'abcPresupuestal':
                        $this->abcPresupuestal();
                        break;
                    case 'ChecaCuenta':
                        $this->ChecaCuenta();
                        break;
                    case 'pagadoras':
                        $this->pagadoras();
                        break;
                    case 'graficaPresupuesto':
                        $this->graficaPresupuesto(null);
                        break;
                    case 'infoNumCtrl':
                        $this->infoNumCtrl();
                        break;
                    case 'validaK':
                        $this->validaK();
                        break;
                    case 'AmortizaObra':
                        $this->AmortizaObra();
                        break;
                    case 'getCompromiso':
                        $this->getCompromiso();
                        break;
                    case 'Contrato':
                        $this->Contrato();
                        break;
                    case 'generarReporte':
                        $this->generarReporte();
                        break;
                    case 'infoReferencia':
                        $this->infoReferencia();
                        break;
                    case 'presupuesto':
                        $this->presupuesto();
                        break;
                    case 'infoCompromiso':
                        $this->infoCompromiso();
                        break;
                    case 'AutorizaAfectacion':
                        $this->AutorizaAfectacion();
                        break;
                    case 'CancelaAfectacion':
                        $this->CancelaAfectacion();
                        break;
                    case 'whyscreen':
                        $this->whyscreen();
                        break;
                    case 'nuevoContable':
                        $this->nuevoContable();
                        break;
                    case 'nuevoPresupuestal':
                        $this->nuevoPresupuestal();
                        break;
                    case 'pintaDetalles':
                        $this->pintaDetalles();
                        break;
                    case 'eliminaDetalleAfe':
                        $this->eliminaDetalleAfe();
                        break;
                    case 'pintaTablaAfectacion':
                        $this->pintaTablaAfectacion();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                $this->forbidden();
                break;
        }
        $this->returnJson();
    }

    /**FUNCION PRINCIPAL  MAIN FUNCTION */
    public function main()
    {
        $this->mixScript('$("' . (($this->nvj == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();');
        $this->mixJs('js/charts/highcharts.src.js');
        $this->mixJs('js/charts/highcharts-3d.src.js');
        $this->mixJs('js/charts/modules/data.js');
        $this->mixJs('js/charts/modules/drilldown.js');

        unset($_SESSION[_S]['infAfectacion']);

        /* TIPOS DE CLC QUE MANEJA LA UR */
        $datos = $this->model->getTiposClc();
        if ($datos['registros'] == 1) {
            $this->prm->tipo = $datos['datos'][0]['tcl_id'];
        }
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';

        $radios = $this->vista->vwTipoClc($datos, $this->prm->tipo);
        $tabla = $this->vista->getTablaPrincipal($radios);
        $html = '';
        $html .= $this->wrPonTabla($tabla);
        $html .= '<div class="alert alert-info">CARGA MASIVA: AFECTACIONES GENERADAS EN CARGA '
            . 'MASIVA QUE AUN NO TERMINA, ESPERE A QUE EL PROCESO DE CARGA MASIVA TERMINE</div>';
        $this->mixHtml($html);

        $botones = '';
        $btnnuevo = $this->vista->ponBotonesVarios('nuevon1');
        $botones .= $this->ponBoton($btnnuevo) . ' ';

        if ($this->escritura) {
            $btnautoriza = $this->vista->ponBotonesVarios('autorizan1');
            $botones .= $this->ponBoton($btnautoriza) . ' ';
        } else {
            $botones .= $this->vista->vwSoloLectura('AUTORIZAR:SOLO LECTURA') . ' ';
        }

        $btnimprime = $this->vista->ponBotonesVarios('imprimen1');
        $botones .= $this->ponBoton($btnimprime) . ' ';

        // Marcos: 21/feb/2019
        // Agregamos un campo (opcional) para permitir al usuario buscar un #afectacion específico
        // y no andar paginando para encontrarlo
        // japhet dixit full
        $botonWTF = '
            <input class="form-control m-l-5 m-r-5" id="WTFafe_id" name="WTFafe_id" placeholder="#Afe"
            title="Escribe un # y presiona INTRO para buscarlo dentro del Tipo de Clc actual" type="text"
            onkeyup="this.value=this.value.replace(/[^\d]+/,\'\')" min="0"
            style="width:70px;display:inline">';

        if ($this->cargaMasiva && $this->escritura) {
            $btncargamasiva = $this->vista->ponBotonesVarios('cargaMasiva');
            $botones .= $this->ponBoton($btncargamasiva) . $botonWTF . ' <div id="AfectacionCargaMasiva"></div>';
        } else {
            $botones .= $botonWTF;
        }

        $this->agregaTools($botones);
        if (isset($this->prm->tipo)) {
            $this->mixScript($this->wrLlamaLlenaTabla('tbl_Conceptos'));
        }

        $script = <<<JS
            $("#btnautoriza").hide();
            function getId(){
                r = "X";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida"))
                        r = $(this).prop("id");
                });
                return r;
            }
            function show(id){
                if( !$("#"+id).hasClass( "tcl_ida" ) ){
                    $(".tcl_idfont").removeClass("tcl_ida");
                    $(".tcl_idfont").removeClass("loadingclas");
                    $(".tcl_idfont").addClass("tcl_idn");
                    $(".tcl_idfont").removeAttr("checked");
                    $("#"+ id).removeClass("tcl_idn");

                    $("#"+id).addClass("loadingclas");
                    if (comunica("{$this->pantalla}",{
                            metodo:"llenaTablaAfectaciones",
                            tipo:id,
                            WTFafe_id: $("#WTFafe_id").val().trim(),
                        },false,false)
                    ) {
                        $("#"+id).addClass("tcl_ida");
                    }
                }
            }
            $("#WTFafe_id").keydown( function(e) {
                /* INTRO regresca la visualización, si es que ya seleccionaron un tipo de CLC */
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 13 ) {
                    e.preventDefault();
                    // Si ya tienen seleccionada un tipo de CLC
                    if ( $(".tcl_ida").length == 1 ) {
                        myID = $(".tcl_ida").attr("id");
                        $("#"+myID).removeClass("tcl_ida").addClass("tcl_idn");
                        $("#"+myID).click();
                    }
                }
            });
            $(".tcl_idfont").click(function(){
                if( $(this).hasClass( "tcl_idb" ) )
                    notify("Este tipo de Afectacion no esta disponible","error");
                else
                    show($(this).attr("id"));
            });
        JS;
        $this->mixScript($script);
    }
    /**FIN DEL METODO MAIN */

    /**LLENARA TABLA DE AFECTACIONES DE LA UR */
    private function llenaTablaAfectaciones()
    {
        if (!isset($this->prm->tipo) || $this->prm->tipo == '' || $this->prm->tipo == 'X') {
            return false;
        }

        if (isset($this->prm->tipo)) {
            $tipo = $this->prm->tipo;
        } else {
            $tipo = $this->tipo;
        }

        // Marcos: 20/feb/2019
        // Para encontrar una afectación sin paginar
        $WTFafe_id = isset($this->prm->WTFafe_id) && $this->prm->WTFafe_id != '' ? ' AND a.afe_id = ' . $this->prm->WTFafe_id . ' ' : '';
        $nombre = $this->tclId[$this->prm->tipo];
        $sql = '
            SELECT
            a.gpo_id,a.ur_id,afe_id,ue_id,eaf_nombre,eaf_color,eaf_id,clc_id,tcl_id,
            DATE_FORMAT(DATE(a.afe_fechaElaboracion),"%d%/%m%/%Y")AS afe_fechaElaboracion,
            IF(eaf_id ="G",CONCAT("GENERADA:",afe_fechaElaboracion),
            CONCAT("AUTORIZADA:",afe_fechaAutorizado )) AS Fecha,oa_nombre,
            IF(ecr_id IN( 100,99 ) OR ecr_id IS NULL ,"SI","NO") AS ecr_id,
            "CARGA MASIVA" AS mensaje,
            IF(Percepciones = NULL,"0.00",Percepciones)AS Percepciones,
            IF(Retenciones = NULL,"0.00",Retenciones)as Retenciones,
            IF (Importe =NULL,"0.00",Importe) AS Importe,
            cfi_clave,cfi_nombre,epg_id,
            IF(Detalle IS NULL  OR Detalle = 0 ,"NO","SI") AS Detalle
            FROM scp_Afectacion a
            INNER JOIN cat_EstadoAfectacion USING ( eaf_id )
            INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio )
            LEFT JOIN  enc_Programatico USING ( epg_id,gpo_id,ur_id )
            LEFT JOIN  cat_ObraActividad USING ( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id )
            LEFT JOIN cat_CargaMasiva USING ( tem_carga )
            LEFT JOIN
            ( SELECT afe_id,IF(SUM(Percepcion)=0,0,round(SUM(Percepcion),2)) AS Percepciones, COUNT(nc) AS Detalle,
            IF(SUM(Retencion)= 0,0,round(SUM(Retencion),2)) AS Retenciones,
            round(SUM( IF(Percepcion IS NULL,0,Percepcion) - IF(Retencion IS NULL,0,Retencion)),2) AS Importe
            FROM (
            SELECT afe_id,ncc_id AS nc, ncc_percepcion AS "Percepcion", ncc_retencion AS "Retencion"
            FROM scp_Contable
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            UNION ALL
            SELECT afe_id,ncp_id AS nc, ncp_percepcionNeto as "Percepcion", "0" as "Retencion"
            FROM scp_Presupuestal
            WHERE  gpo_id = :gpo_id AND ur_id = :ur_id
            ) AS tabla
            GROUP BY afe_id
            ) AS Tabla2 USING(afe_id)
            WHERE clc_id = 0  AND eaf_id !="C"
            AND tcl_id = :tcl_id
            AND a.gpo_id=:agpo
            AND a.ur_id =:aur' .
            $WTFafe_id . PHP_EOL;

        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id . str_replace('a.', '', $WTFafe_id),
            ':tcl_id' => $tipo,
            ':agpo'   => $this->gpo_id,
            ':aur'    => $this->ur_id,
        ];

        $this->setParams($params);

        if ($this->nvj == 1 && isset($this->clasAdmUe) && $this->clasAdmUe != '') {
            $sql .= ' AND ue_id=' . $this->clasAdmUe;
        }
        $sql .= ' ORDER BY afe_id ASC;';
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 10;
        $nav->idTabla = 'tbl_Conceptos';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros para ' . $nombre . '","warning");
                $(".tcl_ida").removeClass("loadingclas");');
            return;
        }

        $this->mixScript('
            $(".addModCedula").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo : "nuevo",
                    afe_id : $(this).attr("afe_id"),
                    tipo : "' . $tipo . '",
                });
                $("#tituloPantalla").text("Afectación- Modificar");
            });
            $("#btnautoriza").fadeIn("slow");
            $("#btnimprime").fadeIn("slow");

            $(".addModCedula2").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo : "afectacion",
                        afe_id : $(this).attr("afe_id"),
                        tipo : "' . $tipo . '",
                    });
            });

            $(".tcl_ida").removeClass("loadingclas");

            $(".oa_nombre a").click(function(){
                var epg = $(this).attr("epg_id");
                if ( !$("#detalle_afe"+epg).length == 1 ) {
                    var epg = $(this).attr("epg_id");
                    p = $(this).offset();
                    $(".afectacion_mensaje").remove();
                    n = $("<div/>")
                        .addClass("afectacion_mensaje").attr("id","detalle_afe"+epg)
                        .appendTo($(this).parent())
                        .animate({opacity:1,top: ( p.top +10 ) +"px" ,left : (p.left +30) +"px"},300,function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"pintaDetalles",
                                "tipo":"epg",
                                epg_id:epg
                            },false,false);
                        });
                }
                return false;
            });
            $(document).mouseup(function (e){    var container = $(".afectacion_mensaje");
                if (!container.is(e.target) && container.has(e.target).length === 0 &&  !$(".afectacion_mensaje").is(e.target) && !$(".oa_nombre").is(e.target) ){
                    $(".afectacion_mensaje").remove(); //container.addClass(\'animated bounceOutRight\');
                }
            });
        ');
    }

    /**FUNCION PARA RELLENAR LOS COMBOS DE LAS AFECTACIONES */
    private function comboNomina()
    {
        $ur = $this->gpo_id . $this->ur_id;
        $combo = new stdClass();

        if ($this->prm->combo != 'obra') {
            switch ($this->prm->id) {
                case 1:
                    $combo->sql = 'SELECT tno_id,tno_nombre FROM cat_TipoNomina WHERE tno_id <> 0';
                    break;
                case 2:
                    $sql = 'SELECT mno_id,mno_nombre FROM cat_ModalidadNomina INNER JOIN enc_ModalidadSubtipoClc USING(mno_id) WHERE stc_id = ? ';
                    if ($ur != 104) {
                        $sql .= ' AND mno_id != 6';
                    }
                    $params = [$this->prm->stc_id];
                    $combo->sql = $sql;
                    $combo->params = $params;
                    break;
            }
            $combo->id = $this->prm->combo;
        } else {
            $combo->id = 'ipu_id';
            $combo->attr = 'ipu_id';
            $combo->id = 'ipu_id';
            $combo->attr = 'ipu_id';
            $combo->sql = '
                SELECT
                ipu_id,ipu_numeroContrato
                FROM ' . BD_SINPRES . '.cat_InversionPublica
                INNER JOIN enc_InversionObra USING( ipu_id )
                INNER JOIN enc_Programatico USING ( epg_id,gpo_id,ur_id )
                WHERE
                gpo_id = :gpo_id
                AND ur_id= :ur_id
                AND epg_id = :epg_id
                AND eip_id=2';
            $params = [
                ':gpo_id' => $this->gpo_id,
                ':ur_id'  => $this->ur_id,
                ':epg_id' => $this->clasAdmEpg,
            ];
            $combo->params = $params;
        }
        $this->recargaCombo($combo);
    }

    /**FUNCION NUEVO/EDITAR  AFECTACIONES*/
    public function nuevo()
    {
        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";');

        $abc = '';
        $ue_id = '';
        $nombre = '';
        $afe_id = 0;
        $numerosControl = 0;
        $tipoObra = ['F', 'G', 'H'];
        $urgpo = $this->gpo_id . $this->ur_id;
        $tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';
        $this->prm->afe_id = isset($this->prm->afe_id) ? $this->prm->afe_id : 0;

        if ($this->nvj < 2) {
            $ue_id = (isset($this->clasAdmUe) ? $this->clasAdmUe : (isset($this->ue) ? $this->ue : ''));
        }

        $afectacion = null;

        if ($this->prm->afe_id > 0) {
            $afe_id = $this->prm->afe_id;
            $numerosControl = $this->model->getNumerosControlAfe($this->gpo_id, $this->ur_id, $afe_id);
            $this->prm->gpo_id = $this->gpo_id;
            $this->prm->ur_id = $this->ur_id;
            $this->prm->clc_id = '0';
            $afectacion = new Afectacion($this->prm);
            $abc = 'c';
        } else {
            $afectacion = new Afectacion();
            $abc = 'a';
        }

        $datos = [];
        if ($afectacion->getEpg_id() != '') {
            $sql = 'SELECT prg_id,spr_id,py_id,oa_id FROM enc_Programatico WHERE epg_id = ? ';
            $params = [$afectacion->getEpg_id()];
            $datos = $this->model->getDatos($sql, false, $params);
            $datos = $datos['datos'][0];
        }

        $tipopag = $this->vista->getControlVarios('tipopag', ['tipo' => $tipo, 'ue' => $ue_id]);
        $tipopag->selecciona = $afectacion->getStc_id();

        $obra = $this->vista->getControlVarios('obra', ['tipo' => $tipo, 'ue' => $ue_id]);
        if (in_array($tipo, $tipoObra)) {
            if ($abc == 'c') {
                $obra->selecciona = $afectacion->getAfe_ipu_id();
                $obra->sql = '
                    SELECT ipu_id,ipu_numeroContrato
                    FROM ' . BD_SINPRES . '.cat_InversionPublica
                    INNER JOIN enc_InversionObra USING( ipu_id )
                    INNER JOIN enc_Programatico USING ( epg_id,gpo_id,ur_id )
                    WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                    AND eip_id=2 AND epg_id = :epg_id
                ';
                $params = [
                    ':gpo_id' => $this->gpo_id,
                    ':ur_id'  => $this->ur_id,
                    ':epg_id' => $afectacion->getEpg_id(),
                ];
                $obra->params = $params;
                $obra = $this->ponCombo($obra);
            } else {
                $obra = $this->templateCombo($obra);
            }
        }

        $tipocompra = $this->vista->getControlVarios('tipocompra');
        $tipocompra->selecciona = $afectacion->getTcp_id();

        $tipoNom = $this->vista->getControlVarios('tipoNom');
        if ($afectacion->getTno_id() != '') {
            $tipoNom->sql = 'SELECT tno_id,tno_nombre FROM cat_TipoNomina WHERE tno_id = ? ;';
            $params = [$afectacion->getTno_id()];
            $tipoNom->params = $params;
            $tipoNom = $this->ponCombo($tipoNom);
        } else {
            $tipoNom = $this->templateCombo($tipoNom);
        }

        $modalidad = $this->vista->getControlVarios('modalidad');
        if ($afectacion->getMno_id() != '') {
            $modalidad->sql = 'SELECT mno_id,mno_nombre FROM cat_ModalidadNomina WHERE mno_id = ? ';
            $params = [$afectacion->getMno_id()];
            $modalidad->params = $params;
            $modalidad = $this->ponCombo($modalidad);
        } else {
            $modalidad = $this->templateCombo($modalidad);
        }
        $submod = $this->vista->getControlVarios('submod');
        $submod->selecciona = $afectacion->getSmn_id();

        $fechainicio = $this->vista->getControlVarios('fechainicio');
        $fechainicio->value = $afectacion->getFechaInicio();

        $fechafin = $this->vista->getControlVarios('fechafin');
        $fechafin->value = $afectacion->getFechaFin();

        /* BOTONES DE ACCION REGRESAR/CANCELAR / ELIMINAR */
        $btnEliminar = '';
        $btnCancelar = '';
        $btnGuardar = '';
        $eliminaDetalle = '';
        $btnregresarindex = $this->vista->ponBotonesVarios('regresan1', ['num' => 1, 'tipo' => $tipo, 'nombre' => $nombre]);
        $btnregresarindex = $this->ponBoton($btnregresarindex);

        /* BOTON DE ELIMINAR AFECTACION */
        if ($this->prm->afe_id > 0 && $afectacion->getEaf_id() == 'G' && $numerosControl == 0) {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $afectacion->getUsr_id()) {
                    $btnEliminar = $this->vista->vwSoloLectura('ELIMINA :LA AFECTACION ES DE OTRO USUARIO');
                } else {
                    $btnEliminar = $this->vista->ponBotonesVarios('btnEliminar', ['afe_id' => $afe_id, 'tipo' => $tipo, 'nombre' => $nombre]);
                    $btnEliminar = $this->ponBoton($btnEliminar);
                }
            } else {
                $btnEliminar = $this->vista->vwSoloLectura('ELIMINA :SOLO LECTURA');
            }
        }

        /* BOTON DE ELIMINAR DETALLE DE LA AFECTACION (NUMEROS DE CONTROL) */
        if ($afectacion->getEaf_id() == 'G' && $numerosControl > 0 && $abc == 'a') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $afectacion->getUsr_id()) {
                    $eliminaDetalle = $this->vista->vwSoloLectura('ELIMINA DETALLE:LA AFECTACION ES DE OTRO USUARIO');
                } else {
                    $eliminaDetalle = $this->vista->ponBotonesVarios('eliminaDetalleAfe', ['afe_id' => $this->prm->afe_id, 'tipo' => $this->prm->tipo]);
                    $eliminaDetalle = $this->ponBoton($eliminaDetalle);
                }
            } else {
                $eliminaDetalle = $this->vista->vwSoloLectura('ELIMINA DETALLE:SOLO LECTURA');
            }
        }

        /* BOTON DE CANCELAR AFECTACION */
        if ($afectacion->getEaf_id() == 'A') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $afectacion->getUsr_id()) {
                    $btnCancelar = $this->vista->vwSoloLectura('LA AFECTACION ES DE OTRO USUARIO');
                } else {
                    $btnCancelar = $this->vista->ponBotonesVarios('cancelarAfe', $this->prm->afe_id);
                    $btnCancelar = $this->ponBoton($btnCancelar);
                }
            } else {
                $btnCancelar = $this->vista->vwSoloLectura('CANCELAR:SOLO LECTURA');
            }
        }
        /* BOTON DE GUARDAR / ACUTALIZAR AFECTACION */
        if ($this->escritura) {
            if ($this->nvj == 1 && $this->usr_id != $afectacion->getUsr_id() && $abc == 'c') {
                $btnGuardar = $this->vista->vwSoloLectura('LA AFECTACION ES DE OTRO USUARIO');
            } else {
                if ($afectacion->getEaf_id() == 'G' || $afectacion->getEaf_id() == '') {
                    $prm = ['abc' => $abc, 'tipo' => $this->prm->tipo, 'afe_id' => $this->prm->afe_id, 'princesa' => $this->princesa];
                    $btnGuardar = $this->vista->ponBotonesVarios('guardarAfe', $prm);
                    $btnGuardar = $this->ponBoton($btnGuardar);
                } else {
                    $btnGuardar = '';
                }
            }
        } else {
            $btnGuardar = $this->vista->vwSoloLectura('GUARDAR:SOLO LECTURA');
        }

        $botones = $btnregresarindex . ' ' . $btnGuardar . ' ' . $btnEliminar . ' ' . $btnCancelar . ' ' . $eliminaDetalle;
        $this->agregaTools($botones);

        // SE IMPLEMENTA EL F1 ENCUADRE
        $html = '<div id="contenedorCFModal"></div>';
        $ponF1 = $this->ponF1([
            'tipo'   => $this->prm->tipo,
            'abc'    => $abc,
            'epg_id' => $afectacion->getEpg_id(),
            'ecf_id' => $afectacion->getEcf_id(),
            'ue_id'  => $ue_id,
        ]);

        $html .= $ponF1;
        $cveAdmitiva = $this->vista->vwClasificacionAdministrativa([
            'tipo'   => $this->prm->tipo,
            'abc'    => $abc,
            'epg_id' => $afectacion->getEpg_id(),
            'extra'  => $datos,
            'ue_id'  => $afectacion->getUe_id(),
        ]);


        $cveAdmitiva = $this->vista->vwClasificacionAdministrativa([
            'tipo'   => $this->prm->tipo,
            'abc'    => $abc,
            'epg_id' => $afectacion->getEpg_id(),
            'extra'  => $datos,
            'ue_id'  => $afectacion->getUe_id(),
        ]);


        switch ($this->prm->tipo) {
            case 'J':
                if ($this->gpo_id == 1 && $this->ur_id == 16) {
                    // 002 DEUDA PUBLICA
                    if ($ue_id == '002') {
                        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                    }
                } elseif ($ue_id == '004') {
                    $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                }
                break;
            case 'K':
                if ($this->gpo_id == 1 && $this->ur_id == 16) {
                    // 001 INGRESOS Y DERECHOS AFECTOS A FIDEICOMISOS
                    if ($ue_id == '001') {
                        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                    }
                } elseif ($ue_id == '003') {
                    $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                }
                break;
            case 'L':
                if ($this->gpo_id == 1 && $this->ur_id == 16) {
                    // 004 FIDEICOMISOS, APORTACIONES Y OTROS
                    // 003 SUBSIDIOS, ESTIMULOS FISCALES Y OTROS
                    if ($ue_id == '003' || $ue_id == '004') {
                        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                    }
                } elseif ($ue_id == '005' || $ue_id == '006') {
                    $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                }
                break;
            case 'M':
                break;
            case 'C':
                $cveAdmitiva->comboUe = true;
                $cveAdmitiva->comboPrg = false;
                $cveAdmitiva->comboSpr = false;
                $cveAdmitiva->comboPy = false;
                $cveAdmitiva->comboOa = false;
                $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                break;
            case 'D':
            case 'E':
                if ($ue_id == '') {
                    $cveAdmitiva->comboUe = true;
                }
                $cveAdmitiva->comboPrg = false;
                $cveAdmitiva->comboSpr = false;
                $cveAdmitiva->comboPy = false;
                $cveAdmitiva->comboOa = false;
                $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                break;
            default:
                $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                break;
        }

        if ($abc == 'c') {
            $this->mixScript('bloquearClasAdmin();');
        }
        $html .= '
            <div class="row m-t-10">
                <fieldset class="col-md-6 col-xs-12">
                    <legend>Información de la Afectacion</legend><hr/>';

        switch ($this->prm->tipo) {
            case 'I':
                if ($this->gpo_id . $this->ur_id == 901) {
                    $tipopag->sql = '
                        SELECT stc_id,stc_nombre
                        FROM cat_SubtipoClc
                        WHERE tcl_id = :tcl_id
                        AND stc_id in(1,2,3)
                    ';
                } else {
                    $tipopag->sql = '
                        SELECT stc_id,stc_nombre
                        FROM cat_SubtipoClc
                        WHERE tcl_id = :tcl_id
                        AND stc_id in(4)
                    ';
                }
                $params = [
                    ':tcl_id' => $this->prm->tipo,
                ];
                $tipopag->params = $params;
                $html .= $this->ponCombo($tipopag);
                break;
            case 'A':
                if ($this->princesa) {
                    $tipopag->sql = '
                        SELECT stc_id,stc_nombre
                        FROM cat_SubtipoClc
                        WHERE tcl_id = :tcl_id
                        AND stc_id in(3,5,6,8,10,11,15)
                    ';
                } else {
                    $tipopag->sql = '
                        SELECT stc_id,stc_nombre
                        FROM cat_SubtipoClc
                        WHERE tcl_id = :tcl_id
                    ';
                }
                $params = [
                    ':tcl_id' => $this->prm->tipo,
                ];
                $tipopag->params = $params;
                $html .= $this->ponCombo($tipopag);
                $html .= $tipoNom;
                /**
                 * Usuario: evenegass
                 * Fecha Actualización: 15/11/2019 11:57 PM
                 * Comentarios: La variable $urs era lo que estaba en lugar de $this->grupo2,
                 * pero no estaba definida, por eso decidi manejar mejor grupo2
                 */
                if (!in_array($urgpo, $this->grupo2)) {
                    $html .= $modalidad;
                }

                $html .= '<div id="submod" style="display:' . ($afectacion->getSmn_id() != 0 ? 'block' : 'none') . '">'
                    . $this->ponCombo($submod) . '</div>
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-12">
                        <legend>Periodo de la Afectación</legend>
                        <hr/>
                    <fieldset class="col-md-4 col-xs-12">';
                $html .= $this->ponInput($fechainicio);
                $html .= $this->ponInput($fechafin);
                $html .= '</fieldset></fieldset>';
                break;
            case 'C':
                $html .= $this->ponCombo($tipopag);
                break;
            case 'D':
            case 'E':
            case 'L':
                $html .= $this->ponCombo($tipopag);
                $html .= '</fieldset>';
                break;
            case 'B':
                $html .= $this->ponCombo($tipopag);
                $html .= $this->ponCombo($tipocompra);
                $html .= '<div class="alert alert-warning" style="padding:8px !important; color:#000 !important;">';
                $html .= '<span id="mf1" style="display:inline-block">';
                if ($afectacion->getFondoRotatorio() == 'S') {
                    $html .= 'La Afectación ha sido marcada para su uso de recuperación de Fondo Rotatorio</span>';
                } else {
                    $html .= 'Marcar esta casilla si la Afectación será usada para recuperación de Fondo Rotatorio</span>';
                }
                $html .= '<div class="squaredTwo" style="display: inline-block;text-align: center;vertical-align: middle;margin-left: 8px;">
                        <input type="checkbox" value="None" ' . ($afectacion->getFondoRotatorio() == 'S' ? 'checked="checked"' : '') . ' id="squaredTwo" name="check" style="display:none" />
                        <label for="squaredTwo"></label>
                        </div> <br><span id="mf" style="display:' . ($afectacion->getFondoRotatorio() == 'S' ? 'block' : 'none') . '">';
                $html .= 'Tenga en cuenta que la afectacion solo aceptara documentos marcados para recuperación de Fondo Rotatorio!!</span>';
                $html .= '</div>';
                $html .= '</fieldset>';
                $this->mixScript('
                    $("#squaredTwo").click(function(){
                        if($(this).is(":checked")){
                            $("#mf").show();
                            $("#mf1").html("La Afectación ha sido marcada para su uso de recuperación de Fondo Rotatorio");
                        }else{
                            $("#mf").hide();
                            $("#mf1").html("Marcar esta casilla si la Afectación será usada para recuperación de Fondo Rotatorio");
                        }
                    })
                ');
                break;
            case 'H':
                $html .= $this->ponCombo($tipopag);
                $html .= $obra;
                $html .= '</fieldset></form>';

                $this->mixScript('
                    $("#encProg,#encCveFin").blur(function(){
                        $("#ipu_id").prop("required");
                        n = $("#ipu_id").parent();
                        n.show();
                        comunica("' . $this->pantalla . '",{
                            metodo:"comboNomina",
                            combo:"obra"
                        });
                    });
                ');
                break;
            case 'F':
                $html .= $this->ponCombo($tipopag);
                $html .= $obra;
                $html .= '</fieldset></form>';
                $this->mixScript('
                    $("#encProg,#encCveFin").blur(function(){
                        if ($("#stc_id").val() != "" && $("#stc_id").val() != "02" ){
                            $("#ipu_id").prop("required");
                            n = $("#ipu_id").parent().parent();
                            n.show();
                            comunica("' . $this->pantalla . '",{
                                metodo:"comboNomina",
                                combo:"obra"
                            });
                        } else {
                            $("#ipu_id").removeAttr("required").empty();
                            n = $("#ipu_id").parent().parent();
                            n.hide();
                        }
                    });

                    $("#stc_id").change(function(){
                        if ($(this).val() != "" && $(this).val() != "02" ){
                            comunica("' . $this->pantalla . '",{
                                metodo:"comboNomina",
                                combo:"obra"
                            });
                            $("#ipu_id").prop("required");
                            n = $("#ipu_id").parent().parent();
                            n.show();
                        }else{
                            $("#ipu_id").removeAttr("required").empty();
                            n = $("#ipu_id").parent().parent();
                            n.hide();
                        }
                    })
                ');
                break;
            case 'G':
                $html .= $this->ponCombo($tipopag);
                $html .= $obra;
                $html .= '</fieldset></form>';
                $this->mixScript('
                    $("#encProg,#encCveFin").blur(function(){
                        if ($("#stc_id").val() !="" && $("#stc_id").val()  !="00" ){
                            $("#ipu_id").prop("required");
                            n = $("#ipu_id").parent().parent();
                            n.show();
                            comunica("' . $this->pantalla . '",{
                                metodo:"comboNomina",
                                combo:"obra"
                            });
                        } else {
                            $("#ipu_id").removeAttr("required").empty();
                            n = $("#ipu_id").parent().parent();
                            n.hide();
                        }
                    });
                    $("#stc_id").change(function(){
                        if ($(this).val()!= "" && $(this).val() !="00" ){
                            $("#ipu_id").prop("required");
                            comunica("' . $this->pantalla . '",{
                                metodo:"comboNomina",
                                combo:"obra"
                            });
                            n = $("#ipu_id").parent().parent();
                            n.show();
                        } else {
                            $("#ipu_id").removeAttr("required").empty();
                            n = $("#ipu_id").parent().parent();
                            n.hide();
                        }
                    })
                ');
                break;
            default:
                $html .= $this->ponCombo($tipopag);
                $html .= '</fieldset></form>';
                break;
        }
        $html .= '
            <fieldset class="col-md-12 col-xs-12">' . ($numerosControl > 0 ?
            '<div class="alert alert-warning" style="display:inline-block; padding:9px;">
                Si desea eliminar la afectacion o actualizarla <br/>deberá borrar '
            . ($numerosControl == 1 ? 'el número de control ligado a ella' : 'los ' . $numerosControl . ' números de control ligados a ella! ')
            . '</div>' : '')
            . '</fieldset></div>';
        $this->mixHtml($html);

        if ($tipo == 'A') {
            $this->mixScript('
                $("#tno_id").change();
                $("#tno_id").change(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"comboNomina",
                        id:2,
                        stc_id:$("#stc_id").find("option:selected").attr("stc_id"),
                        combo:"mno_id"
                    });
                });
                $("#mno_id").change(function(){
                    if($(this).find("option:selected").val() ==6){
                        document.getElementById("submod").style.display="block";
                        $("#smn_id").attr("required","1");
                    }else{
                        document.getElementById("submod").style.display="none";
                        $("#smn_id").removeAttr("required");
                    }
                        });
                $("#mno_id").change();
                function validarFechas(fechaInicio, fechaTermino){
                    var arregloFechaInicio = fechaInicio.split("/");
                    var arregloFechaTermino = fechaTermino.split("/");

                    var anioFechaInicio = arregloFechaInicio[2];
                    var mesFechaInicio = arregloFechaInicio[1];
                    var diaFechaInicio = arregloFechaInicio[0];

                    var anioFechaTermino = arregloFechaTermino[2];
                    var mesFechaTermino = arregloFechaTermino[1];
                    var diaFechaTermino = arregloFechaTermino[0];

                    if(anioFechaInicio < anioFechaTermino){
                        return true;
                    }else if(anioFechaInicio > anioFechaTermino){
                        return false;
                    }else{
                        if(mesFechaInicio < mesFechaTermino){
                            return true;
                        }else if(mesFechaInicio > mesFechaTermino){
                            return false;
                        }else{
                            if(diaFechaInicio <= diaFechaTermino){
                                return true;
                            }else{
                                return false;
                            }
                        }
                    }
                }
            ');
        }

        switch ($this->prm->tipo) {
            case 'K':
                $this->mixScript('
                    $("#encCveFin,#encProg").blur(function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"validaK",
                            clave:$("#encCveFin").val()
                        });
                    });
                ');
                break;
            case 'E':
                $this->mixScript('
                    $("#stc_id").change(function(){
                        comunica("srv/ClasificacionFinanciamiento.php",{
                            metodoCveFin:"cargaComboCveFin",
                            filtro:"anticiposFondoRotatorio",
                            afe:1}
                        );
                    });
                ');
                if ($abc == 'c') {
                    $this->mixScript('
                        comunica("srv/ClasificacionFinanciamiento.php",{
                            metodoCveFin:"cargaComboCveFin",
                            filtro:"anticiposFondoRotatorio",
                            afe:1,
                            ecf_id:"' . $afectacion->getEcf_id() . '"
                        });
                    ');
                }
                break;
            case 'D':
                $this->mixScript('
                    $("#stc_id").change(function(){
                        comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin:"cargaComboCveFin",
                        filtro:"AcuerdosMinistracion"});
                    });');
                break;
            case 'M':
                $this->mixScript('
                    $("#stc_id").change(function(){
                        if ($(this).val() == 15) {
                            comunica("srv/ClasificacionFinanciamiento.php",{
                                metodoCveFin:"cargaComboCveFin",
                                filtro:"general",
                                stc_id: $(this).val(),
                                tipo:"' . $this->prm->tipo . '"
                            });
                        }
                    });
                ');
                break;
            default:
                $this->mixScript('
                    $("#stc_id").change(function() {
                        comunica("' . $this->pantalla . '",{
                            metodo:"comboNomina",
                            tipopago:$("#stc_id").find("option:selected").attr("stc_id"),
                            id:1,
                            combo:"tno_id"
                        });
                        $("#mno_id").val("0");
                    });
                ');
                break;
        }
        /********* fin siwtch  */
        $this->mixScript('
            $("#cveFin_id").change(function(){
                var n = "' . $this->prm->tipo . '";
                if (n != "K") {
                    if ($(this).find("option:selected").attr("saldo") == 0 && n != "D"){
                        if (document.getElementById("nuevo_cobertura")) {
                            document.getElementById("nuevo_cobertura").style.display="block";
                        }
                    }
                    if(n != "D"){
                        if($(this).find("option:selected").attr("saldo") == "") {
                            if (document.getElementById("nuevo_cobertura")) {
                                document.getElementById("nuevo_cobertura").innerHTML="La Obra/Actividad no tiene claves de Financiamiento presupuestadas";
                                document.getElementById("nuevo_cobertura").style.display="block";
                            }
                        }
                    }
                    if ($(this).find("option:selected").attr("saldo") > 0) {
                        if (document.getElementById("nuevo_cobertura")) {
                            document.getElementById("nuevo_cobertura").style.display="none";
                        }
                    }
                } else {
                    comunica("' . $this->pantalla . '",{
                        metodo:"validaK",
                        clave:$(this).find("option:selected").attr("cfi_clave")
                    });
                }
            });
        ');
    }

    /**FUNCION ABC PARA AFECTACIONES*/
    public function abcAfe()
    {
        $this->prm->epg_id = (isset($this->clasAdmEpg) && $this->clasAdmEpg != 0) ? $this->clasAdmEpg : null;
        $mensaje = '';
        //20-03-2019 @Jordy Ticket #958932 clave de financiamiento ABAAA0317 con el subtipo de contrato
        if ((isset($this->prm->abc) && $this->prm->abc != 'b') && (isset($this->prm->tcl_id) && $this->prm->tcl_id == 'C') && (isset($this->prm->ecf_id) && $this->prm->ecf_id == '7597') && (isset($this->prm->stc_id) && $this->prm->stc_id != '03') && ($this->gpo_id == 5 && $this->ur_id == 39)
        ) {
            $this->mixScript('notify("No se puede guardar la afectación con la clave de financiamiento ABAAA0317 si no es de subtipo contrato","error");');
            return false;
        }
        /**
         * @Eduardo Venegas : Antes se validaba que el afe_ipu_id no estuviera vacio, ahora se permite que este vacio siempre y cuando el subtipo clc sea "00", es decir ,"NO APLICA"
         */

        if ($this->prm->abc != 'b' && $this->prm->tcl_id == 'H' && $this->prm->stc_id != '00' && $this->prm->afe_ipu_id == '') {
            $this->mixScript(' notify("No se especifico el Contrato de la Obra Seleccionada","error");');
            return false;
        }
        switch ($this->prm->abc) {
            case 'a':
                $m = date('m');
                if (date('Y') > APP_EJERCICIO) {
                    $m = 12;
                }

                switch ($m) {
                    case 1:
                    case 2:
                    case 3:
                        $this->prm->periodo = 1;
                        break;
                    case 4:
                    case 5:
                    case 6:
                        $this->prm->periodo = 2;
                        break;
                    case 7:
                    case 8:
                    case 9:
                        $this->prm->periodo = 3;
                        break;
                    case 10:
                    case 11:
                    case 12:
                        $this->prm->periodo = 4;
                        break;
                }

                $this->prm->eaf_id = 'G';
                $ur = $this->gpo_id . $this->ur_id;
                $this->prm->gpo_id = $this->gpo_id;
                $this->prm->ur_id = $this->ur_id;
                $this->prm->clc_id = '0';

                if ($this->prm->tcl_id == 'B') {
                    if (!isset($this->prm->tcp_id) || $this->prm->tcp_id == '') {
                        $this->mixScript('notify("No selecciono el tipo de Compra","error");');
                        return false;
                    }
                    if (!in_array($this->prm->tcp_id, ['D', 'C'])) {
                        $this->mixScript('notify("El tipo de compra es invalido","error");');
                        return false;
                    }
                } else {
                    $this->prm->tcp_id = 'D';
                }

                if (!isset($this->prm->ecf_id) || $this->prm->ecf_id == '') {
                    $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
                }

                if ($this->prm->ecf_id == '') {
                    $this->mixScript('notify("ocurrio un error al guardar la clave de financiamiento, intente Nuevamente","error");');
                    return false;
                } else {
                    $opc = new stdClass();
                    $opc->sis_id = 'S';
                    $opc->men_id = '2';
                    $opc->opc_id = '1';

                    /**SE ESTA VERIFICANDO EL BLOQUEO DE LAS AFECTACIONES (MODULO DE BLOQUEO DE CLC) POR EL REGISTRO DE LA AFECTACIÓN */
                    $valida = $this->model->gtBloqueos($this->prm->tcl_id, $this->prm->stc_id, $this->prm->ecf_id, $opc);

                    if (!$valida->valido) {
                        $this->mixScript('notify(" ' . $valida->mensaje . '","error");');
                        return false;
                    }

                    /**SE ESTA VERIFICADO QUE NINGUN FOLIO ADECUACIONES PRESUPUESTALES ESTE EN TRANSITO 08-07-2019 TICKET 550148*/
                    if (isset($this->prm->tcl_id) && ($this->prm->tcl_id == 'A' or $this->prm->tcl_id == 'B')) {
                    }
                }

                if (isset($this->prm->smn) && $this->prm->smn != '0') {
                    $this->prm->smn_id = $this->prm->smn;
                }

                if ($ur == 901) {
                    if ($this->prm->stc_id == 2) {
                        $this->prm->tcp_id = null;
                    }
                }

                $this->prm->ue_id = $this->clasAdmUe;

                if (isset($this->prm->fechaInicio) && $this->prm->fechaInicio == '') {
                    $this->prm->fechaInicio = null;
                    unset($this->prm->fechaInicio);
                }

                if (isset($this->prm->fechaFin) == '') {
                    $this->prm->fechaFin = null;
                    unset($this->prm->fechaFin);
                }

                $afe_id = Afectacion::abc($this->prm);
                $this->prm->afe_id = $afe_id;
                $mensaje = 'Se agrego exitosamente la Afectación ' . $afe_id;
                break;
            case 'b': // Eliminar
                $this->prm->gpo_id = $this->gpo_id;
                $this->prm->ur_id = $this->ur_id;
                $this->prm->clc_id = '0';
                $this->prm->tcl_id = $this->tcl_id;
                Afectacion::abc($this->prm);
                $mensaje = 'Se elimino exitosamente la Afectación número ' . $this->prm->afe_id;
                break;
            case 'c': //actualizar
                $this->prm->clc_id = '0';
                if (isset($this->prm->smn) && $this->prm->smn != '0') {
                    $this->prm->smn_id = $this->prm->smn;
                }
                $this->prm->gpo_id = $this->gpo_id;
                $this->prm->ur_id = $this->ur_id;
                $afe = new Afectacion();
                $afe->setAfe_id($this->prm->afe_id);
                $afe->setUr_id($this->prm->ur_id);
                $afe->setGpo_id($this->prm->gpo_id);
                $afe->setClc_id($this->prm->clc_id);

                $afe->select();

                $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();

                if ($this->prm->tcl_id == 'H' || $this->prm->tcl_id == 'G') {
                    if ($this->prm->stc_id == '00') {
                        $this->prm->afe_ipu_id = null;
                    }
                }

                if ($this->prm->tcl_id == 'F') {
                    if ($this->prm->stc_id == '02') {
                        $this->prm->afe_ipu_id = null;
                    }
                }

                $valida = $this->model->gtBloqueos($this->prm->tcl_id, $this->prm->stc_id, $this->prm->ecf_id, $this->prm->opcion);
                if (!$valida->valido) {
                    $this->mixScript('notify("' . $valida->mensaje . '","error");');
                    return false;
                }

                $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
                if ($this->prm->epg_id == 0) {
                    unset($this->prm->epg_id);
                }

                $afe->abc($this->prm);
                $mensaje = 'Se actualizó exitosamente la Afectación número  ' . $this->prm->afe_id;
                break;
        }
        $this->guardaLog($this->prm, 'afe');
        $script = '
            notify("' . $mensaje . '","success");
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                tipo:"' . $this->prm->tcl_id . '",
            });
        ';
        $this->mixScript($script);
    }

    private function validaK()
    {
        $clave = str_replace('-', '', $this->prm->clave);
        $stc_id = $this->model->getClaveBursa($clave);

        $combo = new stdClass();
        $combo->id = 'stc_id';

        if ($stc_id != '00') {
            $combo->sql = 'SELECT stc_id,stc_nombre FROM cat_SubtipoClc WHERE tcl_id = "K" AND stc_id = ? ';
        } else {
            $combo->sql = 'SELECT stc_id,stc_nombre FROM cat_SubtipoClc WHERE tcl_id = "X"';
        }

        $params = [$stc_id];
        $this->setParams($params);
        $this->recargaCombo($combo);
    }

    /**FUNCION PARA AGREGAR AFECTACIONES*/
    public function afectacion()
    {
        $contrato = null;
        $ur_id = $this->ur_id;
        $gpo_id = $this->gpo_id;
        $tipo = $this->prm->tipo;
        $afe_id = $this->prm->afe_id;

        $this->mixScript('$("div[aria-describedby=cargaDatosProveedor]").html("")' . DibujaPantallaBuscarProveedorJS());

        $vars = $this->model->fn_infAfectacion(['gpo_id' => $gpo_id, 'ur_id' => $ur_id, 'afe_id' => $afe_id, 'tipo' => $tipo]);
        $_SESSION[_S]['infAfectacion'] = $vars;

        /* CABECERA DE LA AFECTACION  */
        $html = $this->vista->pintaCabeceraAfectacion($vars);
        $html .= '<div id="afe_container_detail"></div>';
        $html .= '<div id="afe_container_footer">';

        if ($vars['eaf_id'] == 'A') {
            $html .= '<div class="alert alert-warning" style ="display:inline-block; width:auto;">Esta afectación ya no'
                . ' puede ser modificada debido a que ya ha sido Autorizada</div>';
        } elseif ($vars['clc_id'] != 0) {
            $html .= '<div class="alert alert-warning" style ="display:inline-block; width:auto;">Esta afectación ya no'
                . ' puede ser modificada debido a que ya ha sido Vinculada a la CLC ' . $vars['clc_id'] . '</div>';
        }
        if ($vars['tcl_id'] == 'F' && $vars['epg_id'] != '') {
            $contrato = ValidaGarantias::ValidaGarantia($vars['epg_id'], $vars['tcl_id'], $vars['stc_id']);
            $html .= '<div class="alert alert-warning" style ="display:inline-block; width:auto;">
            ' . $contrato->mensaje . '<br/>
            ' . ((isset($contrato->advertencia) && $contrato->advertencia != false) ? 'Advertencia:' . $contrato->advertencia : '') . '
            </div>';
        }
        $html .= '</div>';
        $this->mixHtml($html . '<hr/>');
        $this->mixHtml(DibujaPantallaBuscarProveedorHTML());
        $this->mixScript(DibujaPantallaBuscarProveedorJS());
        $this->pintaTablaAfectacion();
    }

    public function pintaTablaAfectacion()
    {
        $this->setContenedor('afe_container_detail');
        $tipo = $this->prm->tipo;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresaMain';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                tipo:"' . $tipo . '",
                afe_id:' . $this->prm->afe_id . '
            }); ';
        $btnRegresa = $this->ponBoton($btnRegresa);
        $btp = '';
        $btnp = '';
        $btc = '';
        $btnc = '';
        $eliminaDetalle = '';

        if ($_SESSION[_S]['infAfectacion']['eaf_id'] == 'G') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $_SESSION[_S]['infAfectacion']['usr_id']) {
                    $btnp = $this->vista->vwSoloLectura('PRESUPUESTAL:LA AFECTACION ES DE OTRO USUARIO');
                } else {
                    $btnp = $this->vista->ponBotonesVarios('botonNvoPresupuestal', ['afe_id' => $this->prm->afe_id, 'tipo' => $this->prm->tipo]);
                    $btnp = $this->ponBoton($btnp);
                }
            } else {
                $eliminaDetalle = $this->vista->vwSoloLectura('PRESUPUESTAL:SOLO LECTURA');
            }
        }

        if ($_SESSION[_S]['infAfectacion']['eaf_id'] == 'G') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $_SESSION[_S]['infAfectacion']['usr_id']) {
                    $btnc = $this->vista->vwSoloLectura('CONTABLE:LA AFECTACION ES DE OTRO USUARIO');
                } else {
                    $btnc = $this->vista->ponBotonesVarios('botonNvoContable', ['afe_id' => $this->prm->afe_id, 'tipo' => $this->prm->tipo]);
                    $btnc = $this->ponBoton($btnc);
                }
            } else {
                $btnc = $this->vista->vwSoloLectura('CONTABLE:SOLO LECTURA');
            }
        }

        if ($_SESSION[_S]['infAfectacion']['eaf_id'] == 'G' && $_SESSION[_S]['infAfectacion']['n'] > 0) {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $_SESSION[_S]['infAfectacion']['usr_id']) {
                    $eliminaDetalle = $this->vista->vwSoloLectura('ELIMINA DETALLE:LA AFECTACION ES DE OTRO USUARIO');
                } else {
                    $eliminaDetalle = $this->vista->ponBotonesVarios('eliminaDetalleAfe', ['afe_id' => $this->prm->afe_id, 'tipo' => $this->prm->tipo]);
                    $eliminaDetalle = $this->ponBoton($eliminaDetalle);
                }
            } else {
                $eliminaDetalle = $this->vista->vwSoloLectura('ELIMINA DETALLE:SOLO LECTURA');
            }
        }

        switch ($tipo) {
            case 'A':
            case 'B':
            case 'F':
            case 'G':
            case 'J':
            case 'K':
            case 'L':
                $btp = $btnp;
                $btc = $btnc;
                break;
            case 'I':
                switch ($_SESSION[_S]['infAfectacion']['stc_id']) {
                    case '01':
                    case '04':
                        $btp = $btnp;
                        $btc = $btnc;
                        break;
                    case '02':
                        $btc = $btnc;
                        break;
                    case '03':
                        $btp = $btnp;
                        break;
                }
                break;
            case 'M':
                $btc = $btnc;
                break;
            default:
                $btc = $btnc;
                break;
        }

        $tabla = $this->vista->getPonTablaNumerosControl(['afe_id' => $this->prm->afe_id, 'tipo' => $tipo]);
        $html = $this->wrPonTabla($tabla);
        $this->mixScript($this->wrLlamaLlenaTabla('tabla_nums_control'));

        $this->mixHtml($html);
        $valida = $this->model->gtBloqueos(
            $_SESSION[_S]['infAfectacion']['tcl_id'],
            $_SESSION[_S]['infAfectacion']['stc_id'],
            $_SESSION[_S]['infAfectacion']['ecf_id'],
            $this->prm->opcion
        );
        if (!$valida->valido) {
            $this->agregaTools(
                $btnRegresa
                    . '<div class="alert-warning" style="width:60%; padding:5px; display:inline-block; border-radius:4px; margin-left:10px;">
                ' . $valida->mensaje . '</div>'
            );
        } else {
            $this->agregaTools($btnRegresa . '  ' . $btp . '  ' . $btc . '  ' . $eliminaDetalle);
        }
    }

    /**LLENARA TABLA DE NUMEROS DE CONTROL*/
    private function llenaTablaNumerosdeControl()
    {
        if (isset($this->prm->tipo)) {
            $tipo = $this->prm->tipo;
            $_SESSION[_S]['tipo'] = $this->prm->tipo;
        } else {
            $tipo = $this->tipo;
        } //para recargar la tabla con el tipo de afectaciones [gastos de operacion, municipios, etc,]
        $sql = '
        SELECT
        gpo_id,ur_id,clc_id,epg_id,ecf_id,ue_id,afe_id,ncp_id AS num_id,"Presupuestal" AS tipo,
        pre_claveCompleta AS clave,ncp_concepto AS concepto,ncp_percepcionNeto AS Importe,"0" AS factura,
        "N/A" AS Movimiento, ncp_referencia AS  NumRef,cmp_id
        FROM scp_Presupuestal
        INNER JOIN presupuesto USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
        JOIN enc_Programatico USING ( gpo_id,ur_id,epg_id )
        JOIN enc_ClaveFinanciamiento USING ( ecf_id )
        WHERE gpo_id = ? AND ur_id = ?
        AND afe_id = ?
        UNION ALL
        SELECT gpo_id,ur_id,clc_id,"0" AS epg_id,"0" AS ecf_id,"0" AS ue_id,afe_id,ncc_id as num_id,
        "Contable" AS tipo,
        concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,"-",cta_gpo_id,cta_ur_id,"-",cta_ue_id,"-",cgc_id,"-",cta_id)AS clave,
        ncc_concepto AS concepto, IF(ncc_percepcion=0,ncc_retencion,ncc_percepcion) AS Importe,
        "0" AS factura, ncc_tipoMovimiento AS Movimiento,ncc_referencia AS NumRef,"N/A" AS cmp_id
        FROM scp_Contable
        WHERE afe_id = ? AND gpo_id = ? AND ur_id = ? ';
        $params = [$this->gpo_id, $this->ur_id, $this->prm->afe_id, $this->prm->afe_id, $this->gpo_id, $this->ur_id];
        $this->setSql($sql);
        $this->setParams($params);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tabla_nums_control';
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".addModCedula").click(function(){
                if($(this).attr("tipo")=="Presupuestal"){
                    comunica("' . $this->pantalla . '",{
                        metodo :"nuevoPresupuestal",
                        ncp_id :$(this).attr("num_id"),
                        tipo :"' . $tipo . '",
                        cerrada :"' . (isset($this->prm->cerrada) ? ($this->prm->cerrada == 'si' ? 'si' : 'no') : '') . '",
                        type : $(this).attr("tipo"),
                        afe_id :$(this).attr("afe_id"),
                        clc_id :$(this).attr("clc_id"),
                        epg_id :$(this).attr("epg_id"),
                        ecf_id :$(this).attr("ecf_id"),
                        ue_id :$(this).attr("ue_id")
                    },false,false);
                }else{
                    comunica("' . $this->pantalla . '",{
                        metodo :"nuevoContable",
                        ncc_id :$(this).attr("num_id"),
                        afe_id :$(this).attr("afe_id"),
                        clc_id :$(this).attr("clc_id"),
                        tipo: "' . $this->prm->tipo . '"
                    },false,false);
                }
            });

            $(".clave a").click(function(){
                var clave = $(this).attr("num");
                var tipo  = $(this).attr("tipo");
                var p = $(this).offset();
                var x = document.getElementsByClassName("afectacion_mensaje").length

                if ( x != 0 ) {
                    $(".afectacion_mensaje").attr("id","detalle_afe"+clave).html("")
                        .animate({top: ( p.top +10 ) +"px" ,left : (p.left +60) +"px"},200,function(){
                            comunica("' . $this->pantalla . '",{metodo:"pintaDetalles",num:clave,"tipo":tipo},false,false);
                    });
                } else {
                    n = $("<div/>")
                    .addClass("afectacion_mensaje").attr("id","detalle_afe"+clave)
                    .appendTo($(this).parent())
                    .animate({opacity:1,top: ( p.top +10 ) +"px" ,left : (p.left +60) +"px"},300,function(){
                        comunica("' . $this->pantalla . '",{metodo:"pintaDetalles",num:clave,"tipo":tipo},false,false);
                    })
                    .draggable();
                }
            });
            $(document).mouseup(function (e){
                var container = $(".afectacion_mensaje");
                if (!container.is(e.target) && container.has(e.target).length === 0 &&
                        !$(".afectacion_mensaje").is(e.target) && !$(".clave a").is(e.target) ){
                    $(".afectacion_mensaje").remove();
                }
            });
        ');
    }

    /**ELIMINA LOS DETALLES DE LA AFECTACION (CONTABLE Y PRESUPUESTAL)*/
    private function eliminaDetalleAfe()
    {
        if (!isset($this->prm->afe_id)) {
            $this->mixScript('notify("Error en el numero de afectacion","error");');
            return;
        }
        $n = $this->model->eliminaDetalleAfe($this->gpo_id, $this->ur_id, $this->prm->afe_id);
        if ($n !== true) {
            $this->mixScript('notify("' . $n . '","error");');
        } else {
            $this->mixScript('
                notify("Detalles eliminados con exito","success");
                comunica("' . $this->pantalla . '",{
                    metodo:"afectacion",
                    tipo: "' . $this->prm->tipo . '",
                    afe_id: ' . $this->prm->afe_id . '
                });
            ');
        }
    }

    /**BUSCA EL PROVEEDOR*/
    private function proveedor()
    {
        $sql = '
            SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno, soc_nombre
            FROM cat_Proveedor
            INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING ( rfc_id )
            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor
            USING(soc_id)
            WHERE rfc_id = ?';
        $params = [$this->prm->rfc];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $row = $conexion->extraer();
        $conexion->desconectar();

        if (isset($row['rfc_nombre']) && $row['rfc_nombre'] != '') {
            $this->mixScript('$("#rfc_nombre").val("' . str_replace('"', '\"', $row['rfc_nombre']) . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' - ' . $row['soc_nombre'] . '");
                $("#NumReferencia").removeAttr("readonly");
            ');
            if ($this->prm->abc == 'a') {
                $this->mixScript('$("#NumReferencia").val("");');
            }
        } else {
            $this->mixScript(
                '
                $("#rfc_nombre").val("");
                $("#NumReferencia").attr("readonly","readonly");
                notify("Atención \n El Proveedor con RFC ' . $this->prm->rfc . ' no existe en la base de datos o esta desactivado!!","warning");'
            );
            if ($this->prm->abc == 'a') {
                $this->mixScript('$("#NumReferencia").val("");');
            }
            return false;
        }
    }

    /**BUSCA LA CLC  Y NUMEROS DE CONTROL A PAGAR */
    private function clcPagar()
    {
        $buscaur = false;
        $gpo = 0;
        $ur = 0;
        $eclNoAmortizable = [1, 2, 19, 20, 21];
        $cveur = $this->gpo_id . $this->ur_id;

        if ($cveur == 118 && !isset($this->prm->clc_pag_ur)) {
            $this->mixScript(' notify("Seleccione la UR","warnign");');
            return false;
        }

        if ($cveur == 118 && !in_array($this->prm->clc_pag_ur, $this->urs318)) {
            $this->mixScript(' notify("La ur no esta en control de Administración","error");');
            return false;
        }
        if ($cveur == 118) {
            $buscaur = true;
            $gpo = substr($this->prm->clc_pag_ur, 0, 1);
            $ur = substr($this->prm->clc_pag_ur, 1, 2);
        } else {
            $gpo = $this->gpo_id;
            $ur = $this->ur_id;
        }

        $and = '';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $combo = new stdClass();
        $combo->id = 'ncp_control';

        if (isset($this->prm->num_id)) {
            $sql = '
                SELECT * FROM scp_Contable
                WHERE gpo_id = ?
                AND ur_id = ?
                AND ncc_id = ?';
            $tmp = $this->model->getDatos($sql, [$gpo, $ur, $this->prm->num_id])['datos'][0];

            $this->prm->clc_id = $tmp['clc_id'];
            $gpo = $tmp['gpo_id'];
            $ur = $tmp['ur_id'];
            $combo->sql = '
                SELECT
                ncc_id, ncc_concepto as concepto,
                IF(ncc_tipoMovimiento = "-" ,ncc_retencion,ncc_percepcion) as importe
                FROM scp_Contable c
                INNER JOIN scp_Afectacion USING (gpo_id,ur_id,clc_id,afe_id)
                WHERE ncc_id = :ncc_id
                AND c.clc_id = :clc_id ' . $and . '
                AND gpo_id = :gpo_id
                AND ur_id = :ur_id';

            if ($gpo != $this->gpo_id || $ur != $this->ur_id) {
                $this->mixScript('$("#clc_pag_ur").val("' . $gpo . $ur . '");');
            }
            $params = [
                ':ncc_id' => $this->prm->num_id,
                ':clc_id' => $this->prm->clc_id,
                ':gpo_id' => $gpo,
                ':ur_id'  => $ur,
            ];
            $this->setParams($params);
            $combo->selecciona = $this->prm->num_id;
            $this->recargaCombo($combo);
            $this->mixScript('$("#clc_pago").val("' . $tmp['clc_id'] . '");');
            $this->mixScript('$("#ncp_control").change();');
        } else {
            $sql = '
                SELECT clc_id,clc_concepto,clc_percepcion,clc_retencion,ecl_id,tcl_id,stc_id,ecf_id
                FROM scp_Clc
                WHERE clc_id = :clc_id
                AND gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND ecl_id !=15;';
            $params = [
                ':clc_id' => $this->prm->clc_id,
                ':gpo_id' => $gpo,
                ':ur_id'  => $ur,
            ];
            $conexion->setSql($sql);
            $conexion->consultar($params);
            $row = $conexion->extraer();
            $cuentacontablesql = '';

            if (isset($this->prm->tipo) && $conexion->getNumeroRegistros() > 0) {
                $tipoCorrecto = true;
                $subtipoCorrecto = true;

                switch ($this->prm->tipo) {
                    case 'A':
                    case 'B':
                        // si el tipo de afectacion que amortizara es del tipo A o B solo podra amortizar afectaciones tipo D y E
                        if ($row['tcl_id'] != 'D' && $row['tcl_id'] != 'E') {
                            $tipoCorrecto = false;
                        } else {
                            switch ($row['tcl_id']) {
                                case 'D':
                                    if ($row['stc_id'] != '02') {
                                        $subtipoCorrecto = false;
                                    }

                                    break;
                                case 'E':
                                    if ($this->prm->tipo == 'A' && $row['stc_id'] != '01') {
                                        $subtipoCorrecto = false;
                                    } elseif ($row['stc_id'] != '01' && $row['stc_id'] != '02') {
                                        $subtipoCorrecto = false;
                                    }

                                    break;
                            }
                        }
                        break;
                    case 'C':
                        // si el tipo de afectacion que amortizara es del tipo C solo podra amortizar afectaciones tipo A,B y F subtipo:"'.$stc_id.'",
                        switch ($this->prm->subtipo) {
                            case '01':
                                if ($row['tcl_id'] != 'A') {
                                    $tipoCorrecto = false;
                                }

                                break;
                            case '02':
                                if ($row['tcl_id'] != 'B') {
                                    $tipoCorrecto = false;
                                }

                                break;
                            case '03':
                                if ($row['tcl_id'] != 'F') {
                                    $tipoCorrecto = false;
                                }

                                break;
                            case '04':
                                if ($row['tcl_id'] != 'G') {
                                    $tipoCorrecto = false;
                                }

                                break;
                        }
                        break;
                    case 'G':
                        // si el tipo de afectacion que amortizara es del tipo G solo podra amortizar afectaciones tipo D
                        if ($row['tcl_id'] != 'D') {
                            $tipoCorrecto = false;
                        } elseif ($row['stc_id'] != '01') {
                            $subtipoCorrecto = false;
                        }
                        $cuentacontablesql = ' ger_id=2 AND cgp_id=1 AND rub_id =1 AND cgcu_id=9 AND cgsc_id=2 AND ';
                        break;
                    case 'I':
                        if ($row['stc_id'] != '02') {
                            $subtipoCorrecto = false;
                        }

                        break;
                    case 'F':
                        if ($row['tcl_id'] == 'H') {
                            $tipoCorrecto = true;
                            $subtipoCorrecto = true;
                            $cuentacontablesql = ' ger_id=2 AND cgp_id=1 AND rub_id =1 AND cgcu_id=9 AND cgsc_id=4 AND ';
                        } elseif ($row['tcl_id'] == 'D' && $row['stc_id'] == 01) {
                            $tipoCorrecto = true;
                            $subtipoCorrecto = true;
                            $cuentacontablesql = ' ger_id=2 AND cgp_id=1 AND rub_id =1 AND cgcu_id=9 AND cgsc_id=2 AND ';
                        } else {
                            $tipoCorrecto = false;
                            $subtipoCorrecto = false;
                        }
                        break;
                }

                $sql = '
                    SELECT ncc_id, ncc_concepto as concepto,
                        IF(ncc_tipoMovimiento = "-" ,ncc_retencion,ncc_percepcion) as importe
                    FROM scp_Contable
                    JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
                    WHERE scp_Contable.clc_id= :clc_id ' . $and . ' AND';
                $sql .= $cuentacontablesql;
                $sql .= ' gpo_id = :gpo_id AND ur_id = :ur_id';
                $params = [
                    ':clc_id' => $this->prm->clc_id,
                    ':gpo_id' => $gpo,
                    ':ur_id'  => $ur,
                ];
                $combo->params = $params;
                $combo->sql = $sql;

                if ($tipoCorrecto) {
                    if ($row['clc_id'] != '') { // si hay registros
                        if (in_array($row['ecl_id'], $eclNoAmortizable)) {
                            $this->mixScript('notify("Atención La CLC  ' . $this->prm->clc_id . ' no tiene el estado requerido para este movimiento!!","warning");');
                        } elseif ($row['ecf_id'] != $this->prm->cvefin) {
                            $this->mixScript('notify("Atención La CLC  ' . $this->prm->clc_id . ' no tiene la misma clave de financiamiento!!","error");
                                $("#clc_pago").val("");');
                            $combo = new stdClass();
                            $combo->id = 'ncp_control';
                            $combo->sql = 'SELECT "..."';
                            $this->recargaCombo($combo);
                        } elseif ($subtipoCorrecto) {
                            $this->recargaCombo($combo);

                            if (!is_null($this->comboValorUnico)) {
                                $this->mixScript('$("#' . $combo->id . '").change();');
                            }
                        } else {
                            $this->mixScript('notify("El subtipo de CLC no es el adecuado para esta operación","warning");');
                        }
                    } else {
                        $this->mixScript('notify("Atención  La CLC  ' . $this->prm->clc_id . ' no existe en la base de datos o no corresponde al tipo requerido para este movimiento!!","warning");');
                        return false;
                    }
                } else {
                    $this->mixScript('notify("Atención, la CLC  ' . $this->prm->clc_id . ' no es del tipo correcto!","warning");');
                }
            } else {
                $this->mixScript('notify("Atención, la CLC  ' . $this->prm->clc_id . ' no existe!", "warning");');
            }
        }
        $conexion->desconectar();
    }

    /**OBTIENE INFORMACION DEL COMPROMISO*/
    private function getCompromiso($data = null)
    {
        $enviar = false;
        if ($data == null) {
            $data = new stdClass();
            $data->cmp_id = $this->prm->cmp_id;
            if ($this->prm->cmp_id == 'null' || $this->prm->cmp_id == '') {
                return false;
            }
            $enviar = true;
        }
        $objGto = new stdClass();
        $compromiso = null;
        if (!$data->cmp_id == 0) {
            $compromiso = ValidaGarantias::gtCompromiso($data->cmp_id);
            $objGto->pg_id = $compromiso->pg_id;
            $objGto->pe_id = $compromiso->pe_id;
            $objGto->lectura = true;
            $objGto->tipo = $compromiso->tcl_id;
            $objGto->ecf_id = $compromiso->ecf_id;
            $objGto->epg_id = $compromiso->epg_id;
            $objGto->filtro = 'ModNomPartida';
            if ($compromiso->tcl_id == 'A') {
                $objGto->stc_id = $compromiso->stc_id;
                $objGto->mno_id = $compromiso->mno_id;
            }
        } else {
            if ($data->cmp_id == 0) {
                $sql = '
                    SELECT gpo_id,ur_id,ue_id,tcl_id,stc_id,ecf_id,epg_id,mno_id,tcp_id, MONTH(NOW()) AS Mes
                    FROM scp_Afectacion
                    WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id';
                $params = [
                    ':gpo_id' => $this->gpo_id,
                    ':ur_id'  => $this->ur_id,
                    ':afe_id' => $this->prm->afe_id,
                ];
                $con = new AccesoDatos();
                $con->conectar();
                $con->setSql($sql);
                $con->consultar($params);
                $compro = $con->extraer();
                $objGto->tipo = $compro['tcl_id'];
                $objGto->ecf_id = $compro['ecf_id'];
                $objGto->epg_id = $compro['epg_id'];
                $objGto->filtro = 'ModNomPartida';
                if ($compro['tcl_id'] == 'A') {
                    $objGto->stc_id = $compro['stc_id'];
                    $objGto->mno_id = $compro['mno_id'];
                }

                $this->mixScript('
                $("#pe_id").change(function(){
                    var pe = $(this).find("option:selected").val();
                    if( pe !=="" && ( pe !== undefined)){
                        comunica("' . $this->pantalla . '",{
                            metodo:"presupuesto",
                            tipo:"presupuestal",
                            ue_id:"' . $compro['ue_id'] . '",
                            cmp_id:$("#cmp_id").find("option:selected").val(),
                            epg_id:"' . $compro['epg_id'] . '",
                            ecf_id:"' . $compro['ecf_id'] . '",
                            mes:"' . $compro['Mes'] . '",
                            pe_id:$(this).find("option:selected").val(),
                            pg_id:$("#pg_id").find("option:selected").val(),
                            tcp_id:"' . $compro['tcp_id'] . '"
                        });
                    }});
                ');
            }
        }

        $this->setContenedor('objetoGasto');
        $this->mixHtml(ObjetoGasto::ponObjetoGasto($objGto));
        if ($data->cmp_id > 0) {
            $grafica = new stdClass();
            $grafica->ecf_id = $compromiso->ecf_id;
            $grafica->epg_id = $compromiso->epg_id;
            $grafica->tipo = $compromiso->tcl_id;
            $grafica->mes = date('m');
            $grafica->pg_id = $compromiso->pg_id;
            $grafica->pe_id = $compromiso->pe_id;
            $grafica->tcp_id = $compromiso->tcp_id;

            $grafica->ue_id = $compromiso->ue_id;
            $grafica->cmp_id = $this->prm->cmp_id;
            $grafica->grafica = true;
            $grafica->tipo = 'presupuestal';

            if ($enviar) {
                $this->presupuesto($grafica);
                $this->mixScript('
                    $("#cmp_id").attr("importe","' . round(
                    (float) ($compromiso->cmp_importe + $compromiso->ajustado) - ($compromiso->devengado + $compromiso->usado),
                    2
                ) . '");
                    $("#rfc_id").val("' . $compromiso->rfc_id . '");
                    $("#rfc_id").attr("disabled","1");
                    $("#rfc_nombre").val("' . str_replace('"', '', $compromiso->rfc_nombre) . ' ' . $compromiso->rfc_paterno . ' ' . $compromiso->rfc_materno . '");
                    $("#NumReferencia").removeAttr("readonly");
                    importes2();
                    importes();
                ');
            }
        } else {
            $this->mixScript('
                $("#cmp_id").attr("importe","0");
                $("#pg_id").removeAttr("disabled");
                $("#pe_id").removeAttr("disabled");
                $("#rfc_id").removeAttr("disabled");

                $("#graphic").html("");
                $("#graphic").css("height","1px");

                $("#rfc_id").val("");
                $("#rfc_nombre").val("");
                $("#NumReferencia").val("");
                $("#pe_id").change();
                importes2();
                importes();

            ');
        }
    }

    /**AGREGA/EDITA NUMEROS DE CONTROL PRESUPUESTAL*/
    public function nuevoPresupuestal()
    {
        $anioactual = false;
        $lineadecaptura = '<div id="infolc" style="display:none; border:1px solid #AAA; border-radius:5px;padding:10px; background-color:#FFF;">...</div><div id="contenedordetalle"></div><br/>';
        $abc = 'a';
        $PRESUPUESTAL = null;
        $rfcid = '';
        $conceptoedit = '';
        $pg_id = '';
        $pe_id = '';
        $referencia = '';
        $fechareferencia = '';
        $importes = 0;
        $importeiva = 0;
        $importeneto = 0;
        $ponFecha = false;
        $tpd_id = 0;
        $this->prm->ncp_id = isset($this->prm->ncp_id) ? $this->prm->ncp_id : 0;

        /***  EVALUA SI EL REGISTRO ES NUEVO O ES ACTUALIZACION    ******/
        if ($this->prm->ncp_id > 0) {
            $abc = 'c';
            $this->prm->gpo_id = $this->gpo_id;
            $this->prm->ur_id = $this->ur_id;
            $PRESUPUESTAL = new Presupuestal($this->prm);
            $rfcid = $PRESUPUESTAL->getRfc_id();
            $importes = $PRESUPUESTAL->getPercepcionBruto();
            $importeiva = $PRESUPUESTAL->getPercepcionIva();
            $importeneto = $PRESUPUESTAL->getPercepcionNeto();
            $pg_id = $PRESUPUESTAL->getPg_id();
            $pe_id = $PRESUPUESTAL->getPe_id();
            $referencia = $PRESUPUESTAL->getReferencia();
            $tpd_id = $PRESUPUESTAL->getTpd_id();
            $fechareferencia = $PRESUPUESTAL->getFechaReferencia();
            $conceptoedit = htmlspecialchars_decode($PRESUPUESTAL->getConcepto());
        } else {
            $PRESUPUESTAL = new Presupuestal();
        }
        /*** OBTENEMOS LOS DATOS DE LA AFECTACION DE LA CUAL DEPENDE EL NUMERO DE CONTROL QUE SE EDITA O AGREGA ***/
        $prms = [
            'gpo_id' => $this->gpo_id,
            'afe_id' => $this->prm->afe_id,
            'ur_id'  => $this->ur_id,
            'abc'    => $abc,
            'ncp_id' => $this->prm->ncp_id,
        ];
        $AFECTACION = $this->model->getInfoNumPresAfe($prms);

        $tipo = $AFECTACION['tcl_id'];

        if (in_array($AFECTACION['tcl_id'], ['D', 'E', 'C', 'J'])) {
            $anioactual = true;
        }

        /********************* OBJETO DEL GASTO   *****************************/
        $objGto = new stdClass();
        $objGto->tipo = $this->prm->tipo;
        $objGto->ecf_id = $AFECTACION['ecf_id'];
        $objGto->epg_id = $AFECTACION['epg_id'];
        $objGto->filtro = 'ModNomPartida';

        if ($this->prm->tipo == 'A') {
            $objGto->stc_id = $AFECTACION['stc_id'];
            $objGto->mno_id = $AFECTACION['mno_id'];
        }
        if (isset($AFECTACION['mno_id']) && $AFECTACION['mno_id'] != '') {
            $objGto->mno_id = $AFECTACION['mno_id'];
            $objGto->ecf_id = $AFECTACION['ecf_id'];
        }
        if ($pg_id != '') {
            $objGto->pg_id = $pg_id;
            $objGto->pe_id = $pe_id;
            $objGto->lectura = true;
        }
        /****** COMPROMISOS DE LA UR ******/
        $compromiso = $this->vista->getControlesPresupuestal('compromiso', $AFECTACION);
        $compromiso->selecciona = $PRESUPUESTAL->getCmp_id();
        $compromiso->search = true;
        $compromiso = $this->ponCombo($compromiso);

        $documento = $this->vista->getControlesPresupuestal(
            'documentoComprobatorio',
            ['tcl' => $AFECTACION['tcl_id'], 'stc_id' => $AFECTACION['stc_id'], 'pri' => $this->princesa]
        );
        $documento->selecciona = $tpd_id;
        $documento->search = true;
        $documento = $this->ponCombo($documento);

        $otroDocto = $this->vista->getControlesPresupuestal('otroDocto');
        if ($abc == 'c') {
            $otroDocto->value = $PRESUPUESTAL->getOtroDocumento();
        }

        $otroDocto = $this->ponInput($otroDocto);

        /******************FIN OBJETO DEL GASTO   *****************************/
        /*********************HTML DE INFORMACIóN DE LA AFECTACIóN   *****************************/
        $this->setContenedor('afe_container_detail');
        $html = '<input type="hidden" value="' . $importeneto . '" id="ediimporte" />
            <legend>Compromiso </legend>
            <div class="row">
                <fieldset class="col-md-6 col-xs-6">
                    <div style="width:80%; display:table-cell">
                        ' . $compromiso . '
                    </div>
                    <div id="div_info_compro" style="width:20%; display:none; vertical-align:middle; padding-left:5px;">
                        <span style="cursor:pointer; color: #23527c;text-decoration: underline;" id ="div_info_compro_lnk">Detalle del Compromiso</span>
                    </div>
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $documento . '
                    <div id="otroDocumento" style="display:none">
                    ' . $otroDocto . '
                    </div>
                </fieldset>
            </div>
                <fieldset class="col-md-12 col-xs-12" id="objetoGasto">' . ($abc == 'c' ?
            ObjetoGasto::ponObjetoGasto($objGto) : '') . '
                </fieldset>
            <!-- /**** GRÁFICA PARA EL PRESUPUESTO  PARA PRESUPUESTALES Y PARA ANTICIPO DE OBRA *****/ -->
            <fieldset class="col-md-12 col-xs-12">
                    <legend>Gráfica del Presupuesto </legend>
                    <div id="graphic"></div>
            </fieldset>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#pe_id").change(function(){
                var pe = $(this).find("option:selected").val();
                if( pe !=="" && ( pe !== undefined)){
                    comunica("' . $this->pantalla . '",{
                        metodo:"presupuesto",
                        tipo:"presupuestal",
                        ' . ($abc == 'c' ? 'importe:' . $importeneto . ',' : '') . '
                        abc:"' . $abc . '",
                        ue_id:"' . $AFECTACION['ue_id'] . '",
                        cmp_id:$("#cmp_id").find("option:selected").val(),
                        epg_id:"' . $AFECTACION['epg_id'] . '",
                        ecf_id:"' . $AFECTACION['ecf_id'] . '",
                        mes:"' . $AFECTACION['Mes'] . '",
                        pe_id:$(this).find("option:selected").val(),
                        pg_id:$("#pg_id").find("option:selected").val(),
                        tcp_id:"' . $AFECTACION['tcp_id'] . '"
                    });
                }
            });');

        /************************** CAMPOS PARA GUARDAR LOS REGISTROS  *******************/
        $rfc = new stdClass();
        $rfc->id = 'rfc_id';
        $rfc->placeholder = 'F1: Catalogo';
        $rfc->required = true;
        $rfc->maxlength = 20;
        $rfc->class = 'F1Catalogo habilitar';
        $rfc->value = $rfcid;

        if ($this->prm->tipo == 'D' && !$this->princesa) {
            $rfc->value = 'GEO621201KIA';
            if ($this->gpo_id == 3) {
                $rfc->readonly = 'readonly';
            }

            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo:"proveedor",
                    rfc:"GEO621201KIA",
                    abc:"' . $abc . '"
                });
            ');
        }

        $rfc_nombre = $this->vista->getControlesPresupuestal('rfcNombre');
        $refer = $this->vista->getControlesPresupuestal('numeReferencia');
        $refer->value = $referencia;
        $refer->bdo_id = $PRESUPUESTAL->getBdo_id();

        $fecha_referencia = $this->vista->getControlesPresupuestal('fechaReferencia');
        $fecha_referencia->value = $fechareferencia;

        $concepto = $this->vista->getControlesPresupuestal('conceptoNcp');
        $concepto->text = $conceptoedit;

        $importe = $this->vista->getControlesPresupuestal('importeNcp');
        $importe->value = $importes;

        $iva = $this->vista->getControlesPresupuestal('ivaNcp');
        $iva->value = $importeiva;

        $importetotal = $this->vista->getControlesPresupuestal('importeTotalNcp');
        $importetotal->value = $importeneto;

        /******BOTONES DE LA PANTALLA, LAS AGREGAREMOS AL TOOLS  NUMERO DE CONTROL PRESUPUESTAL******/
        $btnregresarindex = $this->vista->ponBotonesVarios('regresan1', ['afe_id' => $this->prm->afe_id, 'tipo' => $tipo, 'num' => '2']);
        $botones = $this->ponBoton($btnregresarindex);
        $btnEliminar = '';
        $llevaiva = false;
        if ($this->prm->tipo == 'B' || $this->prm->tipo == 'F' || $this->prm->tipo == 'G') {
            $llevaiva = true;
        }
        if ($this->escritura) {
            if ($AFECTACION['eaf_id'] == 'G') {
                if ($abc == 'c' && $this->nvj == 1 && $this->usr_id != $PRESUPUESTAL->getUsr_id()) {
                    $btnGuardar = $this->vista->vwSoloLectura(' ELIMINAR:EL NUMERO DE CONTROL ES DE OTRO USUARIO');
                } else {
                    $btnGuardar = $this->vista->getControlesPresupuestal('btnGuardarNcp');
                    $btnGuardar = $this->ponBoton($btnGuardar);
                    $prm = [
                        'anioactual' => $anioactual,
                        'ponFecha'   => $ponFecha,
                        'ncp_id'     => $this->prm->ncp_id,
                        'afe_id'     => $this->prm->afe_id,
                        'abc'        => $abc,
                        'llevaiva'   => $llevaiva,
                        'tipo'       => $tipo,
                    ];
                    $scriptGuarda = $this->vista->getScriptGuardaNCP($prm);
                    $this->mixScript($scriptGuarda);
                }
            } else {
                $btnGuardar = $this->vista->vwSoloLectura('GUARDAR:AFECTACION AUTORIZADA');
            }
        } else {
            $btnGuardar = $this->vista->vwSoloLectura('GUARDAR:SOLO LECTURA');
        }

        if ($abc == 'c') {
            if ($this->escritura) {
                if ($AFECTACION['eaf_id'] == 'G') {
                    if ($abc == 'c' && $this->nvj == 1 && $this->usr_id != $PRESUPUESTAL->getUsr_id()) {
                        $btnGuardar = $this->vista->vwSoloLectura(' ELIMINAR:EL NUMERO DE CONTROL ES DE OTRO USUARIO');
                    } else {
                        $prm = [
                            'afe_id' => $this->prm->afe_id,
                            'ncp_id' => $this->prm->ncp_id,
                            'tipo'   => 'P',
                        ];

                        $btnEliminar = $this->vista->getControlesPresupuestal('btnEliminaNcp', $prm);
                        $btnEliminar = $this->ponBoton($btnEliminar);
                    }
                } else {
                    $btnEliminar = $this->vista->vwSoloLectura('ELIMINAR:AFECTACION YA AUTORIZADA');
                }
            } else {
                $btnEliminar = $this->vista->vwSoloLectura('ELIMINAR:SOLO LECTURA');
            }
        }
        $botones .= $btnGuardar . $btnEliminar;
        /********************FIN DE BOTONES ******************************/

        /******************HTML DEL FORMULARIO    ************************/
        $html = '
            <div class="row">
                <fieldset class="col-md-12 col-xs-12"><legend>Información del número de control presupuestal '
            . (isset($this->prm->ncp_id) ? ':' . $this->prm->ncp_id : '') . '</legend></fieldset>
                    <div>
                        <div class="row">
                            <fieldset class="col-md-6 col-xs-6">
                                <label>R.F.C. del proveedor</label><br />
                                ' . $this->ponInput($rfc) . $this->ponInput($rfc_nombre) . '
                            </fieldset>

                            <fieldset class="col-md-6 col-xs-6">
                                <div style="display:table-cell; width:80%;">
                                    ' . $this->ponInput($refer) . '
                                <div id="div_link_info" style="display:' . ($abc == 'c' ? 'table-cell' : 'none')
            . ';vertical-align:middle; padding:10px; min-height:100%;">
                                    <span id="link_info_ref"  style="cursor:pointer; color: #23527c;text-decoration: underline;">Información Referencia</span>
                                </div>';

        if ($this->prm->tipo != 'A') {
            $html .= $this->ponInput($fecha_referencia);
            $ponFecha = true;
        }
        if ($AFECTACION['tcl_id'] == 'B' || ($AFECTACION['tcl_id'] == 'A' && $AFECTACION['stc_id'] == '06')) {
            $html .= $lineadecaptura;
        }

        if ($AFECTACION['tcl_id'] == 'G' && $this->gpo_id == 4 && $this->ur_id == 30) {
            $html .= $lineadecaptura;
        }

        if ($AFECTACION['tcl_id'] == 'B' || $AFECTACION['tcl_id'] == 'F' || $AFECTACION['tcl_id'] == 'G') {
            $html .= '</fieldset></fieldset><div><div class="row"><fieldset class="col-md-3 col-xs-3">';
        } else {
            $html .= '<div><fieldset class="col-md-6 col-xs-6"></div>';
        }

        $html .= $this->ponInput($importe);

        if ($AFECTACION['tcl_id'] == 'B' || $AFECTACION['tcl_id'] == 'F' || $AFECTACION['tcl_id'] == 'G') {
            $html .= '
                <label for="ncp_iva">Porcentaje IVA $ </label>
                <div style ="padding:5px; text-align:left; border:1px solid #CCC; border-radius:5px;">
                <table  border="0">
                    <tr>
                    <td align="center" style="padding-left:5px;">0 %</td>
                    <td align="left" style="border-right:1px dotted #666;">
                        <input  type="radio" name="radioIVA" id="0" porcentaje="0" style="width:5.5em; required="required" class="radioIVA"/></td>
                    <td align="center"  style="padding-left:5px;">16%</td>
                    <td align="left">
                        <input  type="radio" ' . ($importeiva > 0 ? ' checked="1" ' : '') . ' name="radioIVA" id="1"
                        porcentaje=".16" style="width:5.5em; required="required" class="radioIVA"/></td>
                    </tr>
                </table>
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($iva) . $this->ponInput($importetotal);
        }
        $html .= '
            <div id="porcomprometer" importe="' . ($abc == 'c' ? $PRESUPUESTAL->getPercepcionNeto() : '0') . '" style="display:block">
            <div id="compromensaje2"><strong>..</strong></div>
                    <div style="width:400px; display:block">
                    <input tabindex="10000" id="porcom" readonly="" style="background:transparent;border:1px solid #EAEAEA;width:400px;height:20px;color:#000;float:left; text-align:center;"></input>
                    <div id="compromet" style="transition:.7s all ease-in-out;width:0px; border:1px solid #BBB; border-radius:3px; height:20px;background-color: rgb(0,255,0);">
                    </div>
                    <div id="compromensaje" style="display:block; vertical-align:middle; padding-right:10px;">
                    <strong>Por comprometer' . ($abc == 'c' ? '(incluye el importe actual)' : '') . '</strong></div>
                    <br/><span style="cursor:pointer; color: #23527c;text-decoration: underline;" id="infocompros">Porque solo puedo usar este importe?</span>
                </div>
            </div>
            </fieldset>
                <fieldset class="col-md-6 col-xs-6">'
            . $this->ponTextarea($concepto) . '
                </fieldset>
            </div>
        </div>';

        /* PREVENIR LOS ENTER O SALTOS DE LINEA EN EL CONCEPTO   */
        $this->mixScript('$("#concepto").keypress(function(event) {if (event.keyCode == 13) {event.preventDefault();}});');
        /**************** FIN DEL FORMULARIO  ********************/

        /*********  BOTON GUARDAR  *****************/
        if ($AFECTACION['eaf_id'] != 'G') {
            $html .= '
                <div class="alert alert-warning" style="display:inline-block; padding:9px;">
                    La afectación esta  Autorizada y no se pueden hacer cambios
                </div>
            ';
        }

        $loading = '';
        $html .= '
            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 )
                        $(".documento_fs").animate({opacity:0,height:"0px",width:"0px"},400,function(){ $(".documento_fs").remove(); });
                });
            </script>
        ';
        $this->mixHtml($html . $loading);
        $this->mixScript('$("#loading").hide();');
        $llevaiva = false;
        if ($this->prm->tipo == 'B' || $this->prm->tipo == 'F' || $this->prm->tipo == 'G') {
            $llevaiva = true;
        }
        $this->mixScript('function agregacomas(num){x = num.split(".");x1 = x[0];x2 = x.length > 1 ? "." + x[1] : "";var rgx = /(\d+)(\d{3})/;while (rgx.test(x1)) {x1 = x1.replace(rgx, "$1" + "," + "$2");}return x1 + x2;}');
        $this->agregaTools($botones);

        $anio = 2;
        $dia = intval(date('d'));
        $mes = intval(date('m'));
        $dia = intval(date('d'));
        $mes = intval(date('m'));
        $hora = intval(date('H'));
        $minutos = intval(date('i'));
        if ($this->gpo_id . $this->ur_id == 511) {
            if ($dia < 9 && $mes == 3) {
                $anio = 4;
            }

            if ($dia == 9 && $mes == 3) {
                if ($hora < 23 && $minutos <= 59) {
                    $anio = 4;
                }
            }
        }

        $ajuste = isset($AFECTACION['clc_ajuste']) ? $AFECTACION['clc_ajuste'] : '';
        $this->mixScript('
            $("#NumReferencia").blur(function(){
                if (! $( "#xmlCFDI" ).length ) {
                    $("#infolc").html("");
                    $("#infolc").css("display","none");
                    $("#detallelineacaptura").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"duplicate",
                        tipo:"' . $this->prm->type . '",
                        rfc:$("#rfc_id").val(),
                        folio:$(this).val(),
                        tcl_id:"' . $AFECTACION['tcl_id'] . '",
                        ajuste:"' . $ajuste . '",
                        abc:"' . $abc . '",
                        tdf_id:$(this).attr("tdf"), type:"' . $this->prm->type . '"
                    });
                    importes2();
                }
            });

            $("#fecha_referencia").change(function(){
                validaAnio($(this).val(),' . ($anioactual ? 'true' : 'false') . ');
            });
            function validaAnio(fecha,actual){
                var valido = "false";
                var arregloFecha = fecha.split("/");
                var anio = arregloFecha[2];
                var currentTime = new Date()
                var year = currentTime.getFullYear()

                if(anio<(year-' . $anio . ') || anio>(year)){
                    alertify.alert("Aviso", "El año de la fecha es incorrecto. Es permitido desde el año " + (year-' . $anio . ') + " y hasta el año "+ (year),
                    ).set("labels", {ok:"Aceptar"});
                }else{valido = "true";}
                return valido;
            }

            $("#imprimeDetalle").click(function(){
                var a = document.createElement(\'a\');
                a.href=\'rep/doc/DetalleAmortiza.php?afe_id =' . $this->prm->afe_id . '&ncc_id=' . (isset($this->prm->num_id) ? $this->prm->num_id : '') . '\';
                a.target = \'_blank\';
                document.body.appendChild(a);
                a.click();
            });

            $("#rfc_id").blur(function(){
                if($(this).length!=0){
                    comunica("' . $this->pantalla . '",{
                        metodo:"proveedor",
                        rfc:$(this).val(),
                        abc:"' . $abc . '"
                    },
                    false,false
                    );
                }
            });

            $("#ncp_iva").blur(function(){
                iva = unformatNumber($(this).val());
                total = iva + parseFloat(unformatNumber($("#importe").val()));
                $("#ncp_neto").val(total.toFixed(2)).blur();
                importes();
            });

            $("#importe").blur(function(){ importes();});

            function importes2(){
                cmp = 0;
                xdv = 0;
                fin = 0;
                com = 0;
                doc = 0;
                edi = 0;
                valores  = Array(0);
                edi = (unformatNumber($("#ediimporte").val())).toFixed(2);

                if($("#grafica").attr("pordev") != undefined )
                    xdv = parseFloat($("#grafica").attr("pordev"));

                if ($("#cmp_id").val() == 0) {
                    if ($("#grafica").attr("porcom") != undefined)
                        com = parseFloat($("#grafica").attr("porcom"));
                } else {
                    if ($("#cmp_id").attr("importe") != undefined)
                        com = parseFloat((unformatNumber($("#cmp_id").attr("importe"))).toFixed(2));
                }

                if ($("#NumReferencia").attr("importe") != "" && $("#NumReferencia").attr("importe") != undefined )
                    doc = parseFloat(unformatNumber($("#NumReferencia").attr("importe")).toFixed(2));

                valores = Array(com,xdv,doc);

                fin = Math.min.apply(null,valores);
                fin = fin + parseFloat(edi);

                $("#porcom").val(agregacomas(fin.toFixed(2)));
                if (com< xdv) {
                    if (com < doc) {
                        if ($("#cmp_id").val() == 0)
                            mens ="Por comprometer de la clave";
                        else
                            mens ="Por devengar del compromiso";
                    } else {
                        if ($("#NumReferencia").attr("bdo_id")!=undefined){
                            if (doc == 0 )
                                mens = "El documento no tiene importe para devengar";
                            else
                                mens = "Por devengar del documento";
                        } else {
                            mens = "Seleccione un documento";
                        }
                    }
                } else {
                    if (xdv < doc) {
                        mens = "Por devengar de la clave";
                    } else {
                        if ($("#NumReferencia").attr("bdo_id") != undefined) {
                            if(doc == 0 )
                                mens = "El documento no tiene importe para devengar";
                            else
                                mens = "Por devengar del documento";
                        } else {
                            mens = "Seleccione un documento";
                        }
                    }
                }
                $("#compromensaje2").html( mens);
                importes();
            }

            function importes() {
                iva = 0;
                importe = 0;
                total = 0;
                pc = 0;

                if ($("#importe").length == 1) {
                    importe = parseFloat(unformatNumber($("#importe").val()).toFixed(2));
                }
                if ($("#ncp_iva").length == 1) {
                    iva = parseFloat(unformatNumber($("#ncp_iva").val()));
                }


                total = parseFloat(importe + iva).toFixed(2);
                $("#ncp_neto").val(total);
                $("#ncp_neto").blur();

                if ($("#porcom").length == 1 && $("#porcom").val() != "") {
                    pc = parseFloat(unformatNumber($("#porcom").val()));
                }

                if ($("#compromet").length==1) {
                    if (total == 0 && pc ==0 ) {
                        $("#compromensaje").html("<strong><span style=\"color:#000; font-weight:bold;\" >..</span></strong>");
                    } else if(total !=0 && pc == 0) {
                        $("#compromensaje").html("<strong><span style=\"color:#000; font-weight:bold;\" >No hay presupuesto para devengar</span></strong>");
                        $("#compromet").css("width","0%");
                    } else if (total == 0 && pc >0) {
                        $("#compromensaje").html("<strong><span style=\"color:#000; font-weight:bold;\" >Aún no captura un importe</span></strong>");
                        $("#compromet").css("width","0%");
                    } else if (total <= pc) {
                        var tot=pc-total;
                        var tama = (total * 100 )/pc;
                        var toma = Math.round((tama*255)/100);
                        var dos =Math.abs(255-toma);
                        $("#compromet").css("width",Math.round(tama) + "%");
                        estilo = "rgb("+toma+","+dos+","+0+")";
                        document.getElementById("compromet").style.backgroundColor=estilo;
                        $("#compromensaje").html("<strong><span style=\"color:#000; font-weight:bold;\" >Devengará $ " +  agregacomas(parseFloat(total,2).toFixed(2)) + " y le quedaran $ " + agregacomas(parseFloat(tot, 10).toFixed(2)) +"</span></strong>");
                    } else {
                        $("#compromet").css("width","100%");
                        $("#compromet").css("background-color","rgb(153,0,0)");
                        $("#compromensaje").html("<strong><span style=\"color:#900\">Sobre pasa la cantidad disponible para comprometer: $ " +  agregacomas(parseFloat(pc, 10).toFixed(2)) + "</span></strong>");
                    }
                }
            }

            $(".radioIVA").click(function(){
                importe = unformatNumber($("#importe").val());
                porcentaje = $(this).attr("porcentaje");
                total =importe * porcentaje;
                $("#ncp_iva").val(total.toFixed(2));
                importes();
            });

            $("#NumReferencia").focus( function(e) {
                numDocto = "NumReferencia";
                rfcDocto = "rfc_id";
            });

            $("#NumReferencia").keydown( function(e) {
                /* F1 llama al Banco de Documentos */

                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    var attr = $(this).attr("readonly");
                    if (typeof attr === typeof undefined && attr !== true) {
                        // Mandamos el rfc para que ya muestre solo los documentos del dicho RFC
                        comunica("scp/afectaciones/ControladorBancoDocumentos.php", { rfc : $("#rfc_id").val(),fr:"' . $AFECTACION['afe_fondoRotatorio'] . '" } );
                    }
                }
            });
        ');

        if ($abc == 'c') {
            $this->mixScript('
                $("#porcomprometer").attr("importe","' . $PRESUPUESTAL->getPercepcionNeto() . '");
            ');
        }

        $this->mixScript('
            ' . $this->ponF1Proveedores('rfc_id', [], 'rfc_nombre') . '

            $("#link_info_ref").click(function() {
                if($("#rfc_id").val().trim()!="" && $("#NumReferencia").val().trim()!="") {
                    load_viewer($("#rfc_id").val(),"INFORMACIÓN DEL DOCUMENTO","div");
                    comunica("' . $this->pantalla . '",{
                        metodo:"infoReferencia",
                        rfc_id:$("#rfc_id").val(),
                        referencia:$("#NumReferencia").val(),
                        tdf_id:$("#NumReferencia").attr("tdf")
                    });
                } else {
                    notify("El proveedor y la referencia son necesarias","error");
                }
            });

            $("#infocompros").click(function(){
                load_viewer("info_referencia","DETALLE DE LOS IMPORTES A DEVENGAR","div");
                comunica("' . $this->pantalla . '",{
                    metodo:"whyscreen",
                    pg_id:$("#pg_id").find("option:selected").val(),
                    pe_id:$("#pe_id").find("option:selected").val(),
                    rfc_id:$("#rfc_id").val(),
                    cmp_id:$("#cmp_id").find("option:selected").val(),
                    referencia:$("#NumReferencia").val(),
                    afe_id:' . $this->prm->afe_id . ',
                });
            });

            $("#div_info_compro_lnk").click(function(){
                if ($("#cmp_id").find("option:selected").val() !="" && $("#cmp_id").find("option:selected").val() !=0) {
                    load_viewer("info_referencia","INFORMACIÓN DEL COMPROMISO SELECCIONADO","div");
                    comunica("' . $this->pantalla . '",{
                        metodo:"infoCompromiso",
                        cmp_id:$("#cmp_id").find("option:selected").val()
                    });
                }
            })
        ');
    }

    private function nuevoContable()
    {
        //AGREGA/EDITA NUMEROS DE CONTRON CONTABLE
        //SECCION DE VARIABLES Y DEMAS COSAS  //
        $abc = 'a';
        $this->prm->ncc_id = isset($this->prm->ncc_id) && $this->prm->ncc_id != '' ? $this->prm->ncc_id : 0;
        if ($this->prm->ncc_id > 0) {
            $abc = 'c';
        }
        $script = '';

        // INFORMACIóN DE LA AFECTACIóN  PARA VALIDAR LO NECESARIO CVEFIN, EPG_ID, CUENTA CONTABLE ,ETC
        $AFECTACION = $this->model->getInfoContableAfe($abc, $this->prm->afe_id, $this->prm->ncc_id);
        $tipo = $AFECTACION['tcl_id'];
        $aniocve = $AFECTACION['cfi_anio'];

        if ($tipo != 'C') {
            $this->setContenedor('afe_container_detail');
        }

        $this->prm->gpo_id = $this->gpo_id;
        $this->prm->ur_id = $this->ur_id;

        if ($tipo == 'C') {
            $vars = $this->model->fn_infAfectacion(['gpo_id' => $this->prm->gpo_id, 'ur_id' => $this->prm->ur_id, 'afe_id' => $this->prm->afe_id, 'tipo' => $tipo]);
            /* CABECERA DE LA AFECTACION  */
            $htmlHead = $this->vista->pintaCabeceraAfectacion($vars);
            $this->mixHtml($htmlHead . '<hr/>');
        }

        /****************BOTONES DE LA PANTALLA, NUMERO DE CONTROL CONTABLE  *******/
        $btnregresarindex = $this->vista->ponBotonesVarios('regresan1', ['afe_id' => $this->prm->afe_id, 'tipo' => $tipo, 'num' => '2']);
        $botones = $this->ponBoton($btnregresarindex);

        //SE DETERMINA SI ES ACTUALIZACIóN O ES REGISTRO
        if ($this->prm->ncc_id > 0) {
            $this->prm->clc_id = $AFECTACION['clc_id'];
            $CONTABLE = new Contable($this->prm);
        } else {
            $CONTABLE = new Contable();
        }

        if ($CONTABLE->getTipoMovimiento() == '+') {
            $importes = $CONTABLE->getPercepcion();
            $movimiento = '+';
        } else {
            $importes = $CONTABLE->getRetencion();
            $movimiento = '-';
        }
        $amortiza = $CONTABLE->getNccamortiza();
        $amortizado = $CONTABLE->getAmortizado();

        $html = '<input type="hidden" tipo="' . $movimiento . '" value="' . $importes . '" id="ediimporte" /> ';
        ///INFORMACIóN DE LA AFECTACIóN
        /****** GRÁFICA PARA EL PRESUPUESTO  PARA ANTICIPO DE OBRA Y ANTICIPOS FONDO ROTATORIO******/
        if (($AFECTACION['tcl_id'] == 'E' && $AFECTACION['stc_nombre'] == 'ANTICIPOS') || $AFECTACION['tcl_id'] == 'H') {
            $html .= '<fieldset class="col-md-12 col-xs-12"><div id="graphic"></div></fieldset><br/>';
            $this->mixHtml($html);
            $grafica = new stdClass();
            $grafica->grafica = true;
            $grafica->tipo = ($AFECTACION['tcl_id'] == 'E' ? 'cve' : 'anticipo');
            $grafica->ecf_id = $AFECTACION['ecf_id'];
            $grafica->epg_id = $AFECTACION['epg_id'];
            $grafica->mes = $AFECTACION['Mes'];
            $this->presupuesto($grafica);
            $this->setContenedor('afe_container_detail');
            $html = '';
        }
        /************************* FIN GRÁFICA PARA EL PRESUPUESTO ***********************/

        /************************** CAMPOS PARA GUARDAR LOS REGISTROS  *******************/
        //     CUENTA CONTABLE
        $cuenta = $this->vista->getControlesContable('cuentaContable', ['gpo_id' => $this->gpo_id, 'ur_id' => $this->ur_id]);
        if ($abc == 'c') {
            $cuenta->value = $AFECTACION['cta_cuenta'];
        } else {
            switch ($this->prm->tipo) {
                case 'D':
                    if ($AFECTACION['cfi_clave'] == 'AGAAC0115') {
                        $cuenta->reanonly = 'readonly';
                        $cuenta->value = '11121.116.001.002.0001';
                        $this->mixScript('
                            comunica("' . $this->pantalla . '",{
                                metodo:"ChecaCuenta",
                                cuenta:"' . $cuenta->value . '",
                                tipo:"' . $this->prm->tipo . '",
                                valido:"true",
                                afe_id:"' . $this->prm->afe_id . '"
                            });
                        ');
                    }
                    break;
                case 'E':
                    if ($AFECTACION['stc_id'] == '002') {
                        if ($AFECTACION['cfi_clave'] == 'AGAAC0215') {
                            $cuenta->reanonly = 'readonly';
                            $cuenta->value = '11121.116.001.002.0001';
                            $this->mixScript('
                                comunica("' . $this->pantalla . '",{
                                    metodo:"ChecaCuenta",
                                    cuenta:"' . $cuenta->value . '",
                                    tipo:"' . $this->prm->tipo . '",
                                    valido:"true",
                                    afe_id:"' . $this->prm->afe_id . '"
                                });
                            ');
                        }
                    }
                    break;
                default:
                    $cuenta->value = isset($AFECTACION['cta_cuenta']) ? $AFECTACION['cta_cuenta'] : '';
                    break;
            }
        }
        $this->mixScript('
            $("#cuentacontapre").focus( function(e) {
                ctaCuenta = "cuentacontapre";
                ctaNombre = "cuenta_descripcion";
            });
        ');

        // NOMBRE DE LA CUENTA CONTABLE
        $cuenta_descripcion = $this->vista->getControlesContable('ctaDesc');
        $cuenta_descripcion->value = isset($AFECTACION['cta_nombre']) ? $AFECTACION['cta_nombre'] : '';

        // RFC DEL REGISTRO
        $rfc = new stdClass();
        $rfc->id = 'rfc_id';
        $rfc->placeholder = 'F1: Catalogo';
        $rfc->required = true;
        $rfc->maxlength = 20;
        $rfc->class = 'F1Catalogo habilitar';
        $rfc->value = $CONTABLE->getRfc_id();

        if ($this->prm->tipo == 'D' && $this->princesa) {
            $rfc->value = 'GEO621201KIA';
            if ($this->gpo_id == 3) {
                $rfc->readonly = 'readonly';
            }

            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo:"proveedor",
                    rfc:"GEO621201KIA",
                    abc: "' . $abc . '"
                });
            ');
        }

        // NOMBRE DEL PROVEEDOR EN BASE AL RFC
        $rfc_nombre = $this->vista->getControlesPresupuestal('rfcNombre');
        if ($abc == 'c') {
            $rfc_nombre->value = $AFECTACION['rfc_nombre'] . ' ' . $AFECTACION['rfc_paterno'] . ' ' . $AFECTACION['rfc_materno'] . ' - ' . $AFECTACION['soc_nombre'];

            if ($AFECTACION['tcl_id'] == 'M' && $AFECTACION['stc_id'] == 15) {
                $rfc->disabled = 'true';
            }
        }

        // REFERENCIA DEL REGISTRO BANCO DE DOCUMENTOS
        $refer = $this->vista->getControlesPresupuestal('numeReferencia');
        $refer->value = $CONTABLE->getReferencia();

        if (isset($this->prm->ncc_id)) {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo : "duplicate",
                    rfc : $("#rfc_id").val(),
                    folio : $("#NumReferencia").val()
                });
            ');
        }

        $this->mixScript('
            $("#NumReferencia").focus( function(e) {
                numDocto = "NumReferencia";
                rfcDocto = "rfc_id";
            });
            $("#NumReferencia").blur(function(){
                // Si ya existe el elemento xmlCFDI quiere decir que cargaron con F1 la pantalla de control de documentos fuente  y por lo tanto no checaríamos ahí el blur
                if ( !$( "#xmlCFDI" ).length ) {
                    // Y si la pantalla de documentos fuente no está cargada, tons si validamos
                    $("#infolc").html("");
                    $("#infolc").css("display","none");
                    $("#detallelineacaptura").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo : "duplicate",
                        rfc : $("#rfc_id").val(),
                        folio : $(this).val(),
                        tdf_id : $(this).attr("tdf")
                    });
                }
                importes2();
            });
        ');

        //FECHA DEL DOCUMENTO
        $fecha_referencia = $this->vista->getControlesPresupuestal('fechaReferencia');
        $fecha_referencia->value = $CONTABLE->getFechaReferencia();

        /* CONCEPTO DDLE NUMERO DE CONTROL */
        $concepto = $this->vista->getControlesPresupuestal('conceptoNcp');
        $concepto->text = $CONTABLE->getConcepto();

        $importe = $this->vista->getControlesPresupuestal('importeNcp');
        $importe->value = $importes;
        if ($CONTABLE->getAmortizado() == 1) {
            $importe->readonly = 'readonly';
        }

        if ($this->escritura) {
            if ($AFECTACION['eaf_id'] == 'G') {
                if ($abc == 'c' && $this->nvj == 1 && $this->usr_id != $AFECTACION['usr_id']) {
                    $btnGuardar = $this->vista->vwSoloLectura(' ELIMINAR:EL NUMERO DE CONTROL ES DE OTRO USUARIO');
                } else {
                    $p = [
                        'ncc_id' => $this->prm->ncc_id,
                        'afe_id' => $this->prm->afe_id,
                        'abc'    => $abc,
                        'tipo'   => $this->prm->tipo,
                    ];
                    $btnGuardar = $this->vista->getControlesPresupuestal('btnGuardarNcp');
                    $btnGuardar = $this->ponBoton($btnGuardar);
                    $this->mixScript($this->vista->getScriptGuardarNCC($p));
                }
            } else {
                $btnGuardar = $this->vista->vwSoloLectura('GUARDAR:AFECTACION AUTORIZADA');
            }
        } else {
            $btnGuardar = $this->vista->vwSoloLectura('GUARDAR:SOLO LECTURA');
        }

        $btnEliminar = '';
        if ($abc == 'c') {
            if ($this->escritura) {
                if ($AFECTACION['eaf_id'] == 'G') {
                    if ($abc == 'c' && $this->nvj == 1 && $this->usr_id != $AFECTACION['usr_id']) {
                        $btnGuardar = $this->vista->vwSoloLectura(' ELIMINAR:EL NUMERO DE CONTROL ES DE OTRO USUARIO');
                    } else {
                        $prm = ['ncc_id' => $this->prm->ncc_id, 'afe_id' => $this->prm->afe_id, 'tipo' => 'C'];
                        $btnEliminar = $this->vista->getControlesPresupuestal('btnEliminaNcp', $prm);
                        $btnEliminar = $this->ponBoton($btnEliminar);
                    }
                } else {
                    $btnEliminar = $this->vista->vwSoloLectura('ELIMINAR:AFECTACION YA AUTORIZADA');
                }
            } else {
                $btnEliminar = $this->vista->vwSoloLectura('ELIMINAR:SOLO LECTURA');
            }
        }
        $botones .= $btnGuardar . $btnEliminar;

        /******** SI ES NECESARIO IMPRIMIR DETALLE DE AMORTIZACION *******/
        $imprimeDetalle = $this->vista->getControlesPresupuestal('imprimeDetalleNcp');

        /****   FORMULARIO HTML    **************/
        $html .= '
            <div class="row m-t-10">
                <fieldset class="col-md-6 col-xs-12">
                    ' . $this->ponInput($cuenta)
            . ($this->gpo_id . $this->ur_id != 116 ? '' :
                '<input type="checkbox" id="seisniveles">¿Cuenta de 6 niveles?</input>') . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12">'
            . $this->ponInput($cuenta_descripcion) . '
                </fieldset>
            </div>
            <div class="row m-t-10">
                <fieldset class="col-md-6 col-xs-12">
                    <label>RFC del proveedor</label><br/>'
            . $this->ponInput($rfc)
            . $this->ponInput($rfc_nombre) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12">
                    ' . $this->ponInput($refer) . '
                    <div id="div_link_info" style="cursor:pointer;display:none;vertical-align:middle; min-height:100%;">
                        <span id="link_info_ref" style="cursor:pointer; color: #23527c;text-decoration: underline;">Información del documento</span>
                    </div>
            <div class="row m-t-10">
                <fieldset class="col-md-12 col-xs-12">'
            . $this->ponInput($importe) . '</fieldset>
            </div>';

        if ($AFECTACION['tcl_id'] != 'A') {
            $ponFecha = true;
        }

        $ponretenciones = false;
        $ponpercepciones = false;
        $esp = ' display:table-cell;';
        $esr = ' display:table-cell;';

        switch ($AFECTACION['tcl_id']) {
            case 'M':
                $ponpercepciones = true;
                //005 UNIDAD DE PLANEACION FINACIERA PAGOS
                //006 RECAUDACION
                if (
                    $this->gpo_id . $this->ur_id == 116
                    && ($AFECTACION['ue_id'] == 005 || $AFECTACION['ue_id'] == 006)
                ) {
                    $ponretenciones = true;
                }

                break;
            case 'A':
            case 'L':
                $ponretenciones = true;
                $ponpercepciones = true;
                break;
            case 'F':
            case 'B':
                $ponretenciones = true;
                break;
            case 'D':
            case 'E':
                $ponpercepciones = true;
                break;
            case 'C':
                if ($this->gpo_id . $this->ur_id == 901) {
                    $ponpercepciones = true;
                } else {
                    $ponretenciones = true;
                    $ponpercepciones = true;
                }
                break;
            case 'G':
                $ponretenciones = true;
                $ponpercepciones = true;
                $esp = 'display:none;';
                break;
            case 'I':
                switch ($AFECTACION['stc_id']) {
                    case '01':
                    case '04':
                        $ponretenciones = true;
                        $ponpercepciones = true;
                        break;
                    default:
                        $ponpercepciones = true;
                        break;
                }
                break;
            case 'J':
                if ($AFECTACION['stc_id'] == '01') {
                    //deuda publica estatal y obligaciones de pago
                    $ponretenciones = true;
                    $ponpercepciones = true;
                } else { //deuda municipal
                    $ponpercepciones = true;
                }
                break;
            case 'K':
                if ($AFECTACION['stc_id'] = '01') {
                    // vehicular y nomina
                    $ponretenciones = true;
                    $ponpercepciones = true;
                } else { //tenencias
                    $ponpercepciones = true;
                }
                break;
            case 'H':
                $ponretenciones = true;
                $ponpercepciones = true;
                break;
        }

        if ($ponpercepciones) {
            $html .= '
                <div id="divpercepciones" style="' . $esp . '">
                    <input type="radio" id="percepciones" name="control" ' . ($movimiento != '' ? ($movimiento == '+' ? 'checked="checked"' : '') : '') . ' />
                <label for="percepciones">Percepciones</label></div>';
        }
        if ($ponretenciones) {
            $html .= '
                <div id="divretenciones" style="' . $esr . ' padding-left:10px;">
                    <input type="radio" id="retenciones" name="control" ' . ($movimiento != '' ? ($movimiento == '+' ? '' : 'checked="checked"') : '') . ' />
                <label for="retenciones">Retenciones</label></div><br/>';
        }
        /**** ESTO DIBUJA UN INPUT QUE INDICA CUANTO SE PUEDE AMORTIZAR DE UNA CLC DE  SERVICIOS PERSONALES, CONTRATO, ETC
        Y TAMBIEN SERA PARA UNA DE AJUSTE CUANTO PUEDE AUN AJUSTAR  */
        $vartmp = '';

        $html .= '
            <div id="poramortizar" style="display:none">
                Por amortizar
                <input id="importeporamortizar" disabled="disabled" style="border:1px solid #EAEAEA;" value="' . $vartmp . '" >
                </input>
            </div><br/></fieldset>';

        $tmpmensaje = '';
        if (in_array($AFECTACION['tcl_id'], $this->tclObra) || ($AFECTACION['tcl_id'] == 'E' && $AFECTACION['stc_id'] == 1)) {
            switch ($AFECTACION['tcl_id']) {
                case 'H':
                case 'E':
                    $tmpmensaje = ' MONTO TOTAL PARA PEDIR ANTICIPO';
                    break;
                case 'F':
                case 'G':
                    $tmpmensaje = ' IMPORTE MAXIMO PARA AMORTIZAR OBRA';
                    break;
            }

            $html .= '
                <fieldset class="col-md-6 col-xs-6">
                    <div id="porcomprometer" style="display:block">
                        <div id="compromensaje">
                            <strong>' . $tmpmensaje . ($abc == 'c' ? '(incluye el importe actual)' : '') . '</strong>
                        </div>
                        <div style="width:400px;">
                            <input tabindex="10000" id="porcom" readonly="" style="background:transparent;border:1px solid'
                . ' #EAEAEA;width:400px;height:20px;color:#000;float:left; text-align:center;"></input>
                            <div id="compromet" style="transition:.7s all ease-in-out;width:0px; border:1px solid #BBB; '
                . 'border-radius:3px; height:20px;background-color: rgb(0,255,0);"></div>
                        </div>
                    </div>
                </fieldset>
            ';
        }
        $html .= '<br/>
            <fieldset class="col-md-12 col-xs-12">'
            . $this->ponTextarea($concepto) . '
            </fieldset>
            </div>

            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 )
                        $(".documento_fs").animate({opacity:0,height:"0px",width:"0px"},400,function(){ $(".documento_fs").remove(); });
                });
            </script>';

        /******** SI EL TIPO EL ELEGIBLE PARA AMORTIZAR UNA CLC *******************************************/
        $tipos = ['A', 'B', 'C', 'F', 'G'];
        $amortizadoforzado = false;

        $script = '';
        /* VALORAMOS SI ES VIABLE AMORTIZAR CON ESTE REGISTRO */
        if (($CONTABLE->getAmortizado() == 2 || $CONTABLE->getAmortizado() == '') && in_array($AFECTACION['tcl_id'], $tipos)) {
            $mensajeamortizador = '';
            $claves = [];
            $cvecontrato = false;

            switch ($this->gpo_id . $this->ur_id) {
                case 106:
                    $claves = [2786];
                    break;
                case 411:
                    $claves = [218, 357, 705, 1982, 1990, 2031, 2073];
                    break;
                case 429:
                    $claves = [1268];
                    break;
                case 528:
                    $claves = [3019];
                    break;
            }
            $conamort = new AccesoDatos();
            $conamort->conectar();
            $sqlam = '
                SELECT ecf_id FROM poa_OrigenRecurso
                INNER JOIN enc_Programatico USING( gpo_id,ur_id,epg_id )
                INNER JOIN cat_ObraActividad USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                WHERE toa_id = 2 AND ecf_id= :ecf_id AND gpo_id= :gpo_id AND ur_id = :ur_id;
                ';
            $params = [
                ':ecf_id' => $AFECTACION['ecf_id'],
                ':gpo_id' => $this->gpo_id,
                ':ur_id'  => $this->ur_id,
            ];
            $conamort->setSql($sqlam);
            $conamort->consultar($params);

            if ($conamort->getNumeroRegistros() > 0 && ($AFECTACION['stc_id'] == '03' || $AFECTACION['stc_id'] == '04')) {
                $cvecontrato = true;
            }
            if (in_array($AFECTACION['ecf_id'], $claves) && $this->prm->tipo == 'C' && ($AFECTACION['stc_id'] == '03' || $AFECTACION['stc_id'] == '04')) {
                $cvecontrato = true;
            }
            $conamort->desconectar();
            unset($conamort);

            if ($this->prm->tipo == 'C' && ($AFECTACION['stc_id'] == '01' || $AFECTACION['stc_id'] == '02' || $cvecontrato == true)) {
                $html .= '<input type="checkbox" id="cta2014"/> Pagar cuenta Contable de ejercicios anteriores';
                $this->mixScript('
                    $("#cta2014").click(function(){
                        if($(this).is(":checked")){
                            $("#clc_pg,#squaredTwo").prop("checked", false);
                            $("#contenedorpagadora").hide();
                            $("#divpagadoras").hide();
                            $("#importe").removeAttr("disabled");
                            $("#clc_pag_ur").removeAttr("required");
                        }else{
                            $("#clc_pg,#squaredTwo").prop("checked","checked");
                            $("#contenedorpagadora").show();
                            $("#divpagadoras").show();
                            $("#clc_pag_ur").prop("required");
                        }
                    });
                ');
            }

            if ($this->prm->tipo == 'C' && $AFECTACION['stc_id'] == '03' && ($this->gpo_id . $this->ur_id == '539')) {
                $isEncFinan = new AccesoDatos();
                $isEncFinan->conectar();
                $isEncFinan->setSql('
                    SELECT
                    ecf.ecf_id
                    FROM scp_Afectacion afe
                    INNER JOIN enc_ClaveFinanciamiento ecf USING (ecf_id)
                    WHERE gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND afe_id = :afe_id;
                    ');
                $params = [
                    ':gpo_id' => $this->gpo_id,
                    ':ur_id'  => $this->ur_id,
                    ':afe_id' => $this->prm->afe_id,
                ];
                $isEncFinan->consultar($params);
                $ecf = $isEncFinan->extraer();
                $isEncFinan->desconectar();
                unset($isEncFinan);

                if ($ecf['ecf_id'] == '007597') {
                    ;
                    $html .= '<input type="checkbox" id="cta2014"/> Pagar cuenta Contable de ejercicios anteriores';
                    $this->mixScript('
                        $("#cta2014").click(function(){
                            if($(this).is(":checked")){
                                $("#clc_pg,#squaredTwo").removeAttr("checked");
                                $("#contenedorpagadora").hide();
                                $("#divpagadoras").hide();
                                $("#importe").removeAttr("disabled");
                                $("#clc_pag_ur").removeAttr("required");
                            }else{
                                $("#clc_pg,#squaredTwo").prop("checked","checked");
                                $("#contenedorpagadora").show();
                                $("#divpagadoras").show();
                                $("#clc_pag_ur").prop("required");
                            }
                        });
                    ');
                }
            }

            switch ($AFECTACION['tcl_id']) {
                case 'C':
                    //El stc_id es 05 por eso no cae en ningun caso
                    switch ($AFECTACION['stc_id']) {
                        case '01':
                            $amortizadoforzado = true;
                            $mensajeamortizador = 'Especifique la CLC  de Servicios Personales que desea pagar con este Número de Control';
                            break;
                        case '03':
                        case '04':
                            $amortizadoforzado = false;
                            $mensajeamortizador = 'Si usa cuenta contable de Amortización de obra se amortizara la obra que haya elegido en la Afectacion';
                            break;
                        case '02':
                            $amortizadoforzado = false;
                            $mensajeamortizador = 'Si desea pagar una CLC con este número de Control marque la casilla';
                            break;
                    }
                    break;
                case 'F':
                    $amortizadoforzado = false;
                    $AFECTACION['cta_cuenta'] = isset($AFECTACION['cta_cuenta']) ? $AFECTACION['cta_cuenta'] : '';
                    $n = explode('.', $AFECTACION['cta_cuenta']);
                    if ($abc == 'c' && $n[0] . $n[3] == 21194005) {
                        $amortizadoforzado = true;
                        $mensajeamortizador = 'Si usa cuenta contable de Amortización de obra (21194.XXX.XXX.005.XXXX) se amortizara la obra que haya elegido en la Afectacion';
                    }
                    $mensajeamortizador = 'Si usa cuenta contable de Amortización de obra (21194.XXX.XXX.005.XXXX) se amortizara la obra que haya elegido en la Afectacion';

                    break;
                case 'G':
                    $amortizadoforzado = true;
                    break;
                case 'B':
                case 'A':
                    $amortizadoforzado = false;
                    $mensajeamortizador = 'Si desea pagar una CLC con este número de Control marque la casilla';
                    break;
            }

            $html .= '</div>
                <br/><div style="padding:10px; color:#FFF; background-color:#666; min-height:30px;" id="divpagadoras">';

            $html .= '
                <div class="squaredTwo" style="' . ($amortizadoforzado == true ? 'display:none' : 'display:inline-block') . ';text-align: center;vertical-align: middle;margin-left: 8px;">
                    <input tcl_id="' . $AFECTACION['tcl_id'] . '" stc_id="' . $AFECTACION['stc_id'] . '"  type="checkbox" id="squaredTwo" name="check" style="display:none" ' . ($amortiza != 0 || $amortizadoforzado == true ? 'checked="checked"' : '') . '  />

                    <label for="squaredTwo" style="' . ($amortizadoforzado == true ? 'display:none' : 'display:inline-block') . '"></label>
                </div> ';

            $html .= '
                <span id="mensajeamor" style="color:#FFF; font-weight:bold;">' . $mensajeamortizador . '
                </span></div><div id="contenedorpagadora"style="display:none;"></div><br/>';

            if ($amortizadoforzado) {
                $this->prm->tcl_id = $AFECTACION['tcl_id'];
                $this->prm->stc_id = $AFECTACION['stc_id'];
                $this->pagadoras();
                $this->setContenedor('contenedor');
            }
        }
        /********* FIN SI EL TIPO EL ELEGIBLE PARA AMORTIZAR UNA CLC  **************/
        /*********  BOTON GUARDAR  *****************/
        if ($AFECTACION['eaf_id'] != 'G') {
            $html .= '<div class="alert alert-warning" style="display:inline-block; padding:9px;">La afectación esta  autorizada y no se pueden hacer cambios</div>';
        }

        /********* BOTON ELIMINAR Y SCRIPT  ************/
        if ($abc == 'c' && $amortizado == 1 && $AFECTACION['eaf_id'] == 'G') {
            $html .= '<div class="alert alert-warning" style="display:inline-block; padding:9px;">Este numero de control esta amortizado, no se puede eliminar o editar</div>';
            $botones .= $this->ponBoton($imprimeDetalle);
        }

        /**** FUNCIONES DE VALIDACION JAVASCRIPT  *****/
        /******* JAVASCRIPT DE LOS DIFERENTES CONTROLES -------------- *****/
        $script = $this->vista->getScriptContable(['abc' => $abc, 'afe_id' => $this->prm->afe_id, 'ncc_id' => $this->prm->ncc_id, 'fr' => $AFECTACION['afe_fondoRotatorio']]);

        $script .= '
            $("#cuentacontapre").blur(function(){
                if($(this).val().length != 0){
                    if($(this).val().length < 14) {
                        alertify.alert("Aviso", "La cuenta debe tener 18 o 14 números para ser valida",
                            ).set("labels", {ok:"Aceptar"});
                    } else {
                        comunica("' . $this->pantalla . '",{
                            metodo:"ChecaCuenta",
                            ecf_id:"' . $AFECTACION['ecf_id'] . '",
                            aniocve:"' . $aniocve . '",
                            cuenta:$(this).val(),
                            tipo:"' . $AFECTACION['tcl_id'] . '",
                            subtipo:"' . $AFECTACION['stc_id'] . '",
                            afe_id:"' . $this->prm->afe_id . '",
                            abc:"' . $abc . '"
                        });
                    }
                }
            });

            ' . $this->ponF1Proveedores('rfc_id', [], 'rfc_nombre') . '

            /********  DE AMORTIZACION DE CLC DE ESTE Y DE OTROS AÑOS  ************/
            $("#clc_pg ,#squaredTwo").click(function(){
                if($(this).is(":checked")){
                    comunica("' . $this->pantalla . '",{
                        metodo:"pagadoras",afe_id:"' . $this->prm->afe_id . '",
                        stc_id:"' . $AFECTACION['stc_id'] . '",
                        tcl_id:"' . $AFECTACION['tcl_id'] . '",
                        abc:"' . $abc . '"
                        ' . ($abc == 'c' ? ',ncc_id:' . $this->prm->ncc_id : '') . '
                    });
                }else{
                    comunica("' . $this->pantalla . '",{
                        metodo:"pagadoras",afe_id:"' . $this->prm->afe_id . '",
                        stc_id:"' . $AFECTACION['stc_id'] . '",
                        tcl_id:"' . $AFECTACION['tcl_id'] . '",
                        x_n:"r",abc:"' . $abc . '"
                        ' . ($abc == 'c' ? ',ncc_id:' . $this->prm->ncc_id : '') . '
                    });
                }
            });
        ';

        $html .= '</div>'; //cierra el div class row ver linea 3382
        $this->mixScript($script);
        $this->mixHtml($html);
        $this->agregaTools($botones);
    }

    /**FUNCION ABC PARA NUMEROS DE CONTROL*/
    /**MODIFICACION PARA EL TIPO A Y TIPO C*/
    public function abcPresupuestal()
    {
        /************ INFORMACION DE LA AFECTACION   ************/
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $ncp_id = null;
        $this->prm->gpo_id = $gpo_id;
        $this->prm->ur_id = $ur_id;

        $sql = '
            SELECT afe.gpo_id, afe.ur_id, afe.clc_id, afe.afe_id, afe.epg_id, afe.ecf_id, afe.tcp_id,
            afe.ue_id, afe.tcl_id, afe.stc_id, MONTH(afe.afe_fechaElaboracion) AS Mes,
            afe.tno_id, afe.mno_id, afe.smn_id,
            SUBSTRING(afe_fechaInicio,1,10) AS afe_fechaInicio, SUBSTRING(afe_fechaFin,1,10) AS afe_fechaFin,
            afe.eaf_id, afe.afe_fondoRotatorio,
            CONCAT(enc.gpo_id,enc.ur_id,"-",enc.prg_id,"-",enc.spr_id,"-",enc.py_id,"-",enc.oa_id) AS programatico
            FROM scp_Afectacion afe
            INNER JOIN enc_Programatico enc USING (epg_id)
            WHERE  afe.gpo_id = :gpo_id
            AND afe.ur_id = :ur_id
            AND afe.afe_id = :afe_id;
            ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':afe_id' => $this->prm->afe_id,
        ];
        $afectacion = $this->model->getDatos($sql, false, $params)['datos'][0];

        /**SE ESTA VERIFICANDO EL BLOQUEO DE LA AFECTACIÓN (MODULO DE BLOQUEO DE CLC)*/
        $valida = $this->model->gtBloqueos($afectacion['tcl_id'], $afectacion['stc_id'], $afectacion['ecf_id'], $this->prm->opcion);
        if (!$valida->valido) {
            $this->mixScript('notify("La opcion esta bloqueada! consulte a su analista,no es necesario levantar un ticket. ","error");');
            return false;
        }

        /**SE ESTA VERIFICADO QUE NINGUN FOLIO ADECUACIONES PRESUPUESTALES ESTE EN TRANSITO 08-07-2019 TICKET 550148*/
        if ($this->prm->abc == 'a' || $this->prm->abc == 'c') {
            switch ($afectacion['tcl_id']) {
                case 'A':
                case 'B':
                    //SE MANDAN LOS ID'S CORRESPONDIENTES DE LA CLAVE PRESUPUESTAL, PARTE DE LA CLAVE PRESUPUESTAL EN CADENA DE CARACTERES,
                    //EL TIPO DE ACCIÓN QUE SE ESTA REALIZANDO (AFECTACIÓN,GENERACIÓN)
                    $tieneFolio = $this->model->getFolioDevengadoComprometido(
                        $gpo_id,
                        $ur_id,
                        $afectacion['ue_id'],
                        $afectacion['epg_id'],
                        $this->prm->pg_id,
                        $this->prm->pe_id,
                        $afectacion['ecf_id'],
                        $afectacion['tcp_id'],
                        $afectacion['programatico'] . ' - ' . $this->prm->pg_id . ' - ' . $this->prm->pe_id,
                        'A',
                        'G'
                    );
                    //EN CASO QUE SE ENCUENTRE ALGÚN FOLIO SE MANDARÁ EL MENSAJE DE ERROR
                    if ($tieneFolio->estado == true) {
                        $this->mixScript('
                            notify("' . $tieneFolio->mensaje . '","error");
                            ');
                        return false;
                    }
                    break;
                default:
                    break;
            }
        }

        if ($afectacion['eaf_id'] == 'A') {
            /******************* LOG DE LAS AFECTACIONES**************************/
            $this->guardaLog($this->prm, 'pres');
            return false;
        }
        $this->prm->sts_id = 1; // 2287
        $this->prm->epg_id = $afectacion['epg_id'];
        $this->prm->ecf_id = $afectacion['ecf_id'];
        $this->prm->clc_id = $afectacion['clc_id'];
        $this->prm->ue_id = $afectacion['ue_id'];
        $presupuestal = null;
        if ($afectacion['tcp_id'] != '') {
            $this->prm->tcp_id = $afectacion['tcp_id'];
        }
        if ($this->prm->abc != 'b') {
            if ($this->prm->abc == 'c') {
                $presupuestal = new Presupuestal();
                $presupuestal->setNcp_id($this->prm->ncp_id);
                $presupuestal->setGpo_id($this->gpo_id);
                $presupuestal->setUr_id($this->ur_id);
                $presupuestal->setClc_id($afectacion['clc_id']);
                $presupuestal->setAfe_id($this->prm->afe_id);
                $presupuestal->select();
                $percepcion = $presupuestal->getPercepcionNeto();
            }

            $this->prm->concepto = preg_replace("/\r\n+|\r+|\n+|\t+/i", '', $this->prm->concepto);
            if ($this->prm->percepcionNeto == 0) {
                $this->mixScript(' notify("El importe no puede ser 0","warning"); $("#btn_guarda").show();');
                return false;
            }

            /******* VALIDACION DEL IMPORTE TOTAL  Y DEL DOCUMENTO A DEVENGAR    *********/
            $bod = isset($this->prm->bdo_id) ? $this->prm->bdo_id : '';
            $tdf = isset($this->prm->tdf_id) ? $this->prm->tdf_id : '';
            $documento = ValidaGarantias::gtDocumento($bod, $this->prm->rfc_id, $this->prm->referencia, $tdf);
            if ($documento->valido == false) {
                $this->mixScript('notify("' . $documento->mensaje . '","warning"); $("#btn_guarda").show();');
                return false;
            } else {
                $this->prm->bdo_id = $documento->bdo_id;
                $this->prm->fechaReferencia = $documento->fechaDocumento;
                $this->prm->fechaOrden = $documento->fechaDocumento;
                $this->prm->numeroOrden = $this->prm->referencia;
            }
            if ($documento->sts_id != '1') {
                $this->mixScript('notify("El documento esta in-habilitado, no se puede usar","error");
                    $("#btn_guarda").show();');
                return false;
            }
            /******* VALIDAR QUE NO MEZCLEN DOCUMENTOS DE RECTIFICACION    *********/
            if (!$this->revisaDocumentos($this->prm->afe_id, $documento->tdf_id)) {
                $this->mixScript('notify("No puede mezclar documentos de rectificacion con otro tipo de documento en una misma afectación!!","warning"); $("#btn_guarda").show();');
                return false;
            }
            /******* VALIDACION DEL TIPO DE DOCUMENTO FONDO ROTATORIO    *********/
            if ($afectacion['afe_fondoRotatorio'] == 'S' && $documento->fondoRotatorio == 'N') {
                $this->mixScript('notify("El documento no es para Recuperación de Fondo Rotatorio, sin embargo la Afectación esta marcada para tal fin","error");
                    $("#btn_guarda").show();');
                return false;
            } elseif ($afectacion['afe_fondoRotatorio'] == 'N' && $documento->fondoRotatorio == 'S') {
                $this->mixScript('notify("El documento es para Recuperación de Fondo Rotatorio, sin embargo la Afectación no esta marcada para tal fin","error");
                        $("#btn_guarda").show();');
                return false;
            }

            $disponible = null;
            if ($this->prm->abc == 'c') {
                $documento->referenciado -= $percepcion;
            }
            if ($documento->referenciado == ($documento->importe + $documento->importeRectificado)) {
                $this->mixScript('notify("El documento ha sido referenciado en su totalidad","warning"); $("#btn_guarda").show();');
                return false;
            } elseif (bccomp(
                strval(round((float) (float) $this->prm->percepcionNeto, 2)),
                strval(round((float) (float) ($documento->importe + $documento->importeRectificado) - $documento->referenciado, 2)),
                2
            ) == 1) {
                $this->mixScript('notify("El importe sobrepasa el disponible a Devengar del documento","warning"); $("#btn_guarda").show();');
                return false;
            } else {
                $this->prm->bdo_id = $documento->bdo_id;
                $this->prm->fechaReferencia = $documento->fechaDocumento;
                $this->prm->fechaOrden = $documento->fechaDocumento;
                $this->prm->numeroOrden = $this->prm->referencia;
            }

            if (isset($this->prm->tpd_id) && $this->prm->tpd_id != '') {
                $sql = 'SELECT tpd_nombre  FROM cat_TipoDocumento WHERE tpd_id = ' . $this->prm->tpd_id;
                $this->conectar();
                $this->setSql($sql);
                $this->consultar();
                $tp = $this->extraer();

                $t2 = trim($this->prm->otrdoc, '');

                if ($tp['tpd_nombre'] == 'OTRO' && (isset($t2) && $t2 == '')) {
                    $this->mixScript('notify("Error, no se especifico el nombre del OTRO documento","error");$("#btn_guarda").show();');
                    return false;
                } else {
                    $this->prm->otroDocumento = $this->prm->otrdoc;
                }
            } else {
                $this->mixScript('notify("Error, no es posible determina el Tipo de documento comprobatorio","error"); $("#btn_guarda").show();');
                return false;
            }
            /*  -------  DATOS DEL PRESUPUESTO ------------- */
            $pre = new stdClass();
            $pre->epg_id = $afectacion['epg_id'];
            $pre->ecf_id = $this->prm->ecf_id;
            $pre->tipo = 'presupuestal';
            $pre->pg_id = $this->prm->pg_id;
            $pre->pe_id = $this->prm->pe_id;
            $pre->mes = $afectacion['Mes'];
            $pre->ue_id = $afectacion['ue_id'];
            if (isset($this->prm->tcp_id)) {
                $pre->tcp_id = $this->prm->tcp_id;
            }
            $disponible = $this->presupuesto($pre);

            /*---- SI SELECCIONO GENERAR NUEVO COMPROMISO LO GENERAMOS A PARTIR DE LOS DATOS ----- */
            if ($this->prm->cmp_id == 0) {
                $porcom = round((float) ($disponible->ModificadoAnual - $disponible->Comprometido), 2);
                if ($porcom < round((float) (float) $this->prm->percepcionNeto, 2)) {
                    $this->mixScript('notify("Imposible crear el compromiso, el importe sobre pasa el disponible para '
                        . 'comprometer de $ ' . number_format((float) $porcom) . '","warning");
                            $("#btn_guarda").show();');
                    return false;
                }
                $cmpNvo = $this->model->fnGeneraCompromisoAfectacion($this->prm, $afectacion);
                if ($cmpNvo->bandera == 0) {
                    $this->mixScript('notify("hubo un error al autorizar el compromiso no se pudo generar: ' . $cmpNvo->mensaje . '","error");');
                    return false;
                } else {
                    if ($cmpNvo->banderaC != 1) {
                        $this->mixScript('notify("El compromiso se genero de manera exitosa, su poliza contable tuvo un error,'
                            . ' pero puede continuar de manera normal: ERROR: ' . $cmpNvo->mensajeC . '","warning");');
                    }
                    $this->prm->cmp_id = $cmpNvo->cmp_id;
                }
            } else {
                $compromiso = ValidaGarantias::gtCompromiso($this->prm->cmp_id);
                $tmpcmp = round((float) (($compromiso->cmp_importe + $compromiso->ajustado) - $compromiso->devengado), 2);

                if ($tmpcmp < $this->prm->percepcionNeto) {
                    $this->mixScript('
                            notify("No puede devengar mas del disponible a devengar del compromiso","warnign");
                            $("#btn_guarda").show();
                        ');
                    return false;
                }

                $this->prm->epg_id = $compromiso->epg_id;
                $this->prm->ecf_id = $compromiso->ecf_id;
                $this->prm->pe_id = $compromiso->pe_id;
                $this->prm->pg_id = $compromiso->pg_id;
                $this->prm->rfc_id = $compromiso->rfc_id;

                $pre = new stdClass();
                $pre->epg_id = $this->prm->epg_id;
                $pre->ecf_id = $this->prm->ecf_id;
                $pre->tipo = 'presupuestal';
                $pre->pg_id = $this->prm->pg_id;
                $pre->pe_id = $this->prm->pe_id;
                $pre->mes = $afectacion['Mes'];
                $pre->ue_id = $compromiso->ue_id;
                if (isset($this->prm->tcp_id)) {
                    $pre->tcp_id = $this->prm->tcp_id;
                }
                $disponible = $this->presupuesto($pre);
            }

            if ($this->prm->abc == 'a') {
                if (round((float) $disponible->Diferencia, 2) < round((float) $this->prm->percepcionNeto, 2)) {
                    $this->mixScript('notify("El importe sobrepasa el disponible a Devengar en el trimestre","warning"); $("#btn_guarda").show();');
                    return false;
                }
            } else {
                if (round((float) $disponible->Diferencia + $percepcion, 2) < round((float) $this->prm->percepcionNeto, 2)) {
                    $this->mixScript('notify("El importe sobrepasa el disponible a Devengar en el trimestre","warning");');
                    return false;
                }
            }
        }
        $mensaje = '';

        $this->prm->concepto = isset($this->prm->concepto) ? str_replace("'", "\'", $this->prm->concepto) : '';
        switch ($this->prm->abc) {
            //********** CASE A GUARDAR NUMERO DE CONTROL ************/
            case 'a':
                $ncp_id = Presupuestal::abc($this->prm);
                $mensaje = '"Se ha agregado correctamente el número de control ' . $ncp_id . '","success"';
                break;
                //********** CASE B BORRAR NUMERO DE CONTROL ************/
            case 'b':
                Presupuestal::abc($this->prm);
                $mensaje = '"Se ha elimino el número de control ' . $this->prm->ncp_id . '","success"';
                break;
                //********* CASE C ACTUALIZAR NUMERO DE CONTROL **********/
            case 'c':
                Presupuestal::abc($this->prm);
                $mensaje = '"Se ha actualizado el número de control ' . $this->prm->ncp_id . ' correctamente","success"';
                break;
        } //FIN SWITCH ABC
        /**
         * Autor: Eduardo Venegas
         * Fecha: 11:53 am 22/11/18
         * Comentario: Se agrego el Group by para la actualizacion del modo estricto
         */
        /* ACTUALIZAR LOS MONTOS DE LA AFECTACION  */
        $sql = '
            UPDATE scp_Afectacion
            JOIN
            ( SELECT gpo_id,ur_id,afe_id,SUM(Percepcion) percepciones,SUM(Retencion) retenciones
            FROM (
            SELECT gpo_id,ur_id,afe_id,ncc_percepcion AS "Percepcion", ncc_retencion AS "Retencion"
            FROM scp_Contable
            WHERE gpo_id= :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id
            UNION ALL
            SELECT gpo_id,ur_id,afe_id, ncp_percepcionNeto as "Percepcion ", "0" as "Retencion"
            FROM scp_Presupuestal
            WHERE gpo_id= :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id
            ) NumControl
            GROUP BY gpo_id,ur_id,afe_id
            ) sumas USING(gpo_id,ur_id,afe_id )
            SET afe_percepcion = percepciones,afe_retencion = retenciones
            WHERE gpo_id= :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id';
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':afe_id' => $this->prm->afe_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        /******************* LOG DE LAS AFECTACIONES************************************/
        $this->guardaLog($this->prm, 'pres');
        $this->mixScript('
            $("#loading").hide();
            notify(' . $mensaje . ');
            comunica("' . $this->pantalla . '",{    metodo:"afectacion",    afe_id:"' . $this->prm->afe_id . '",    tipo:"' . $afectacion['tcl_id'] . '"});
        ');
    }

    /**FUNCION ABC PARA NUMEROS DE CONTROL*/
    public function abcContable()
    { //var_dump($this->prm);die;
        /************ INFORMACION DE LA AFECTACION   ************/
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $num_id = null;
        $sql = '
            SELECT gpo_id,ur_id, clc_id,afe_id,tcl_id,stc_id,epg_id,ecf_id,tcp_id,ue_id,tcl_id,stc_id, MONTH(afe_fechaElaboracion)AS Mes,tno_id,mno_id,smn_id,
            SUBSTRING(afe_fechaInicio,1,10) AS afe_fechaInicio, SUBSTRING(afe_fechaFin,1,10) AS afe_fechaFin,eaf_id,afe_fondoRotatorio
            FROM scp_Afectacion WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':afe_id' => $this->prm->afe_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $afectacion = $this->extraer();
        $this->desconectar();

        $valida = $this->model->gtBloqueos($afectacion['tcl_id'], $afectacion['stc_id'], $afectacion['ecf_id'], $this->prm->opcion);
        if (!$valida->valido) {
            $this->mixScript('notify("La opcion esta bloqueada! consulte a su analista,no es necesario levantar un ticket. ","error");');
            return false;
        }

        if ($afectacion['eaf_id'] == 'A') {
            $this->mixScript('notify("Intento ilegal de modificar una Afectaci&oacute;n, la Afectaci&oacute;n ya esta Autorizada","Error")');
            return false;
        }
        $this->prm->sts_id = 1; // 2287
        $this->prm->epg_id = $afectacion['epg_id'];
        $this->prm->ecf_id = $afectacion['ecf_id'];
        $this->prm->clc_id = $afectacion['clc_id'];
        $this->prm->ue_id = $afectacion['ue_id'];
        if ($afectacion['tcp_id'] != '') {
            $this->prm->tcp_id = $afectacion['tcp_id'];
        }

        $cuenta2013 = '';

        if ($this->prm->abc != 'b') {
            /* ---- ELIMINAR SALTOS DE LINEA DEL CONCEPTO   -------------  */
            $this->prm->concepto = preg_replace("/\r\n+|\r+|\n+|\t+/i", '', $this->prm->concepto);
            if ($this->prm->importe == 0) {
                $this->mixScript(' notify("El importe no puede ser 0","warning"); $("#btn_guarda").show();');
                return false;
            }
        }

        $this->prm->gpo_id = $gpo_id;
        $this->prm->ur_id = $ur_id;

        if (isset($this->prm->tipoamortiza) && $this->prm->tipoamortiza == 'acta') {
            $cuenta2013 = $this->prm->nccamortiza;
            $this->prm->nccamortiza = null;
        }
        if (isset($this->prm->aclc) && $this->prm->aclc != '') {
            $this->prm->clc = $this->prm->aclc;
        }
        if (isset($this->prm->agpo) && $this->prm->agpo != '') {
            $this->prm->gpo = $this->prm->agpo;
        }
        if (isset($this->prm->aur) && $this->prm->aur != '') {
            $this->prm->ur = $this->prm->aur;
        }

        if ($this->prm->abc == 'b') {
            $cuentaTem = '...';
        } else {
            $cuentaTem = $this->prm->cuenta;
        }

        $cuentas = explode('.', $cuentaTem); // obtener cada campo de la cuenta  y separarlo en variables
        $this->prm->ger_id = substr($cuentas[0], 0, 1);
        $this->prm->cgp_id = substr($cuentas[0], 1, 1);
        $this->prm->rub_id = substr($cuentas[0], 2, 1);
        $this->prm->cgcu_id = substr($cuentas[0], 3, 1);
        $this->prm->cgsc_id = substr($cuentas[0], 4, 3);

        $this->prm->cta_gpo_id = substr($cuentas[1], 0, 1);
        $this->prm->cta_ur_id = substr($cuentas[1], 1, 2);

        $this->prm->cta_ue_id = $cuentas[2];
        $this->prm->cgc_id = $cuentas[3];
        if (isset($this->prm->cuenta)) {
            if (strlen($cuentaTem) == 25) {
                $this->prm->cta_id = $cuentas[4] . $cuentas[5];
            } else {
                $this->prm->cta_id = $cuentas[4];
            }
        }
        //print_r($this->prm);die;
        if (isset($this->prm->tipoimporte)) {
            $this->prm->tipoMovimiento = (($this->prm->tipoimporte == 'percepcion') ? '+' : '-'); //definir el tipo de movimiento
            if ($this->prm->tipoMovimiento == '+') {
                $this->prm->percepcion = $this->prm->importe;
                $this->prm->retencion = 0;
            } else {
                $this->prm->retencion = $this->prm->importe;
                $this->prm->percepcion = 0;
            }
        }

        /* VALIDACIONES DEL DOCUMENTO   */
        if ($this->prm->abc != 'b') {
            $tdf = isset($this->prm->tdf_id) ? $this->prm->tdf_id : '';
            $documento = ValidaGarantias::gtDocumento($this->prm->bdo_id, $this->prm->rfc_id, $this->prm->referencia, $tdf);
            if ($documento->valido == false) {
                $this->mixScript('notify("' . $documento->mensaje . '","warning"); $("#btn_guarda").show();');
                return false;
            } else {
                $this->prm->bdo_id = $documento->bdo_id;
                $this->prm->fechaReferencia = $documento->fechaDocumento;
                if ($this->prm->tipoimporte == 'percepcion') {
                    if ($this->prm->tipoMovimiento == '+' && $afectacion['tcl_id'] == 'G' and $cuentas[0] . $cuentas[3] != '11239008') {
                        $this->mixScript('
                            notify("No puede usar percepciones en una Afectacion de Administraci&oacute;n Directa  con cuenta diferente a 11239 XXX XXX 008 XXXX","error");
                            $("#btn_guarda").show();
                        ');
                        return false;
                    }

                    if ($this->prm->importe > round((float) $documento->percepcionContable - ($documento->perceD - $documento->importePerR), 2)) {
                        $this->mixScript('notify("El importe supera las percepciones del documento","warning");
                        $("#btn_guarda").show();');
                        return false;
                    }
                } else {
                    if ($this->prm->importe > round((float) $documento->retencionContable - ($documento->retenD - $documento->importeRetR), 2)) {
                        $this->mixScript('notify("El importe supera las retenciones del documento, importe supera las percepciones del documento","warning");
                        $("#btn_guarda").show();');
                        return false;
                    }
                }
                /******* VALIDAR QUE NO MEZCLEN DOCUMENTOS DE RECTIFICACION    *********/
                if (!$this->revisaDocumentos($this->prm->afe_id, $documento->tdf_id)) {
                    $this->mixScript('notify("No puede mezclar documentos de rectificacion con otro tipo de documento en una misma afectacion!!","warning"); $("#btn_guarda").show();');
                    return false;
                }

                if ($documento->tdf_id == 'PGT') {
                    if ($afectacion['tcl_id'] != 'C') {
                        $this->mixScript('notify("NO puede usar un documento de pagos a terceros en una Afectacion que no es de pagos a terceros","warning");
                        $("#btn_guarda").show();
                        ');
                        return false;
                    } else {
                        if ($afectacion['stc_id'] != '01') {
                            $this->mixScript('notify("No puede usar un documento de pagos a terceros en una Afectacion que no es de pagos a terceros de servicios personales","warning");
                                $("#btn_guarda").show();
                            ');
                            return false;
                        }
                    }
                }
            }
            if ($documento->sts_id != '1') {
                $this->mixScript('notify("El documento esta in-habilitado, no se puede usar","error");
                $("#btn_guarda").show();');
                return false;
            }
        }
        $this->prm->concepto = isset($this->prm->concepto) ? str_replace("'", "\'", $this->prm->concepto) : '';

        switch ($this->prm->abc) {
            //********* CASE A GUARDAR NUMERO DE CONTROL **********/
            case 'a':
                $ajuste = false;
                if (isset($this->prm->ajuste) && $this->prm->ajuste > 0) {
                    $ajuste = true;
                }

                /******* VALIDACION DEL TIPO DE DOCUMENTO FONDO ROTATORIO    *********/
                if ($afectacion['afe_fondoRotatorio'] == 'S' && $documento->fondoRotatorio == 'N') {
                    $this->mixScript('notify("El documento no es para Recuperacion de Fondo Rotatorio, sin embargo la Afectacion esta marcada para tal fin","error");$("#btn_guarda").show();');
                    return false;
                } elseif ($afectacion['afe_fondoRotatorio'] == 'N' && $documento->fondoRotatorio == 'S') {
                    $this->mixScript('notify("El documento es para Recuperacion de Fondo Rotatorio, sin embargo la Afectacion no esta marcada para tal fin","error");$("#btn_guarda").show();');
                    return false;
                }

                if (isset($this->prm->ciclos) && $this->prm->ciclos > 0) {
                    /************ ESTO ES PARA AMORTIZACION EN GRUPO DE NUMEROS DE CONTROL  */
                    $this->conectar();
                    $this->prm->gpo = $this->gpo_id;
                    $this->prm->ur = $this->ur_id;
                    $this->prm->tipoMovimiento = ($this->prm->tipoimporte == 'percepcion' ? '+' : '-'); //definir el tipo de movimiento
                    foreach ($this->prm->datos as $valor) {
                        $this->prm->nccamortiza = $valor[0]->ncc_id;
                        $this->prm->clc = $valor[1]->clc_id;

                        $percepcion = $valor[2]->percepcion;
                        $retencion = $valor[3]->retencion;
                        $valors = $percepcion + $retencion;

                        if ($this->prm->tipoimporte == 'percepcion') {
                            $this->prm->percepcion = $valors;
                        } else {
                            $this->prm->retencion = $valors;
                        }
                        $this->prm->clc_id = '0';
                        Contable::abc($this->prm);

                        $sql2 = 'UPDATE scp_Contable SET ncc_amortizado = 1 WHERE ncc_id = ?';
                        $params = [$valor[0]->ncc_id];
                        $this->setSql('SET @scp = 1;');
                        $this->consultar();
                        $this->setSql($sql2);
                        $this->consultar($params);
                        $this->setSql('SET @scp = NULL;');
                        $this->consultar();
                    }
                    $this->desconectar();
                } else {
                    $this->prm->clc_id = '0';
                    $num_id = Contable::abc($this->prm);
                }
                /*********DESPUES DE GUARDAR VALIDACIONES PARA NUMEROS DE CONTROL CONTABLE***********/
                if (isset($this->prm->nccamortiza) && $this->prm->nccamortiza != 0 && $this->prm->tipoamortiza == 'aclc') {
                    // SI TRAE VALOR DE NUM DE CONTROL PARA AMORTIZAR DESPUES DE GUARDAR ACTUALIZAMOS EL NUMERO AMORTIZADO AL ESTADO DE "AMORTIZADO"
                    $sql = '
                        UPDATE scp_Contable
                        SET ncc_amortizado=1
                        WHERE gpo_id =' . $this->prm->gpo . '
                        AND ur_id =' . $this->prm->ur . '
                        AND clc_id=' . $this->prm->clc . '
                        AND ncc_id =' . $this->prm->nccamortiza;
                    /* DESPUES DE AMORTIZAR ENTONCES ACTUALIZAMOS EL PERIODO DE LA AFECTACION CON EL DE LA CLC  */
                    $this->conectar();
                    $this->setSql('SET @scp = 1;');
                    $this->consultar();

                    $this->setSql($sql);
                    $this->consultar();

                    $this->setSql('SET @scp = NULL;');
                    $this->consultar();

                    $consulta = '
                        SELECT date_format(afe_fechaInicio,\'%Y/%d/%m\') as afe_fechaInicio,
                        date_format(afe_fechaFin,\'%Y/%d/%m\') as afe_fechaFin
                        FROM scp_Afectacion
                        INNER JOIN scp_Contable USING(gpo_id,ur_id, clc_id,afe_id)
                        WHERE afe_fechaInicio is not null AND gpo_id = :gpo_id AND ur_id = :ur_id AND ncc_id = :ncc_id';
                    $params = [
                        ':gpo_id' => $this->prm->gpo,
                        ':ur_id'  => $this->prm->ur,
                        ':ncc_id' => $this->prm->nccamortiza,
                    ];
                    $this->setSql($consulta);
                    $this->consultar($params);
                    $datos = $this->extraer();

                    if ($this->getNumeroRegistros() > 0) {
                        $sql2 = '
                            UPDATE scp_Afectacion SET
                            afe_fechaInicio=STR_TO_DATE(\'' . $datos['afe_fechaInicio'] . '\',\'%Y/%d/%m\'),
                            afe_fechaFin=STR_TO_DATE(\'' . $datos['afe_fechaFin'] . '\',\'%Y/%d/%m\')
                            WHERE afe_id = :afe_id AND gpo_id = :gpo_id AND ur_id = :ur_id';
                        $params = [
                            ':afe_id' => $this->prm->afe_id,
                            ':gpo_id' => $this->gpo_id,
                            ':ur_id'  => $this->ur_id,
                        ];
                        $this->setSql($sql2);
                        $this->consultar($params);
                        $this->desconectar();
                    }
                } elseif ($cuenta2013 != '' && $this->prm->tipoamortiza == 'acta') {
                    $cuentas = explode('.', $cuenta2013);
                    $ger_id = substr($cuentas[0], 0, 1);
                    $cgp_id = substr($cuentas[0], 1, 1);
                    $rub_id = substr($cuentas[0], 2, 1);
                    $cgcu_id = substr($cuentas[0], 3, 1);
                    $cgsc_id = substr($cuentas[0], 4, 1);
                    $cta_gpo_id = substr($cuentas[1], 0, 1);
                    $cta_ur_id = substr($cuentas[1], 1, 2);
                    $cta_ue_id = $cuentas[2];
                    $cgc_id = $cuentas[3];
                    $cta_id = $cuentas[4];

                    $sql = '
                        INSERT INTO scp_ContableSaldo2013 (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,cos_percepcion,ncc_id)
                        SELECT ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,:importe,:num_id
                        FROM scp_ContableSaldo2013
                        WHERE ger_id = :ger_id
                        AND cgp_id = :cgp_id AND rub_id = :rub_id
                        AND cgcu_id = :cgcu_id AND cgsc_id = :cgsc_id
                        AND cta_gpo_id = :gpo_id
                        AND cta_ur_id = :ur_id AND cta_ue_id = :ue_id
                        AND cgc_id = :cgc_id AND cta_id = :cta_id';
                    $params = [
                        ':importe' => $this->prm->importe,
                        ':num_id'  => $this->prm->num_id,
                        ':ger_id'  => $ger_id,
                        ':cgp_id'  => $cgp_id,
                        ':rub_id'  => $rub_id,
                        ':cgcu_id' => $cgcu_id,
                        ':cgsc_id' => $cgsc_id,
                        ':gpo_id'  => $cta_gpo_id,
                        ':ur_id'   => $cta_ur_id,
                        ':ue_id'   => $cta_ue_id,
                        ':cgc_id'  => $cgc_id,
                        ':cta_id'  => $cta_id,
                    ];
                    $this->setParams($params);
                    $this->conectar();
                    $this->setSql($sql);
                    $this->desconectar();
                }
                break;
                //********** CASE B BORRAR NUMERO DE CONTROL **********/
            case 'b':
                $this->prm->clc_id = 0;
                $sql = '
                    SELECT ncc_nccamortiza
                    FROM scp_Contable
                    WHERE gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND ncc_id = :ncc_id';
                $con = new AccesoDatos();
                $params = [
                    ':gpo_id' => $this->prm->gpo_id,
                    ':ur_id'  => $ur_id,
                    ':ncc_id' => $this->prm->ncc_id,
                ];
                $con->setSql($sql);
                $con->conectar();
                $con->consultar($params);
                $data = $con->extraer();
                $con->desconectar();

                Contable::abc($this->prm);
                $con->conectar();
                $this->prm->nccAmortiza = $this->prm->ncc_id;
                if ($data['ncc_nccamortiza'] > 0) { //verificar si quedan numeros de control amortizando al numero de control que amortizaba este(si lo hacia)
                    $ncc = $data['ncc_nccamortiza'];
                    $sql = '
                        SELECT COUNT(ncc_id) AS Total
                        FROM scp_Contable
                        WHERE ncc_nccamortiza = :ncc_id
                        AND gpo_id = :gpo_id
                        AND ur_id = :ur_id;
                    ';
                    $params = [
                        ':ncc_id' => $ncc,
                        ':gpo_id' => $this->prm->gpo_id,
                        ':ur_id'  => $this->prm->ur_id,
                    ];
                    $con->setSql($sql);
                    $con->consultar($params);
                    $row = $con->extraer();
                    if ($row['Total'] == 0) {
                        $con->setSql('SET @scp = 1;');
                        $con->consultar();
                        $sql = '
                            UPDATE scp_Contable
                            SET ncc_amortizado = 2
                            WHERE gpo_id = :gpo_id and ur_id = :ur_id AND ncc_id = :ncc_id
                        ';
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':ncc_id' => $ncc,
                        ];
                        $con->setSql($sql);
                        $con->consultar($params);
                        $con->setSql('SET @scp = NULL;');
                        $con->consultar();
                        $this->mixScript('notify("El numero de Control ' . $this->prm->nccAmortiza . ' ha quedado sin amortizaciones","warning");');
                    }
                }
                $con->desconectar();
                unset($con);
                break;
                //******** CASE C ACTUALIZAR NUMERO DE CONTROL **********/
            case 'c':
                $percepcion = '';
                $retencion = '';
                $amortizado = '';
                $afectacion2 = null;
                $this->prm->gpo_id = $this->gpo_id;
                $this->prm->ur_id = $this->ur_id;
                $this->prm->clc_id = 0;

                $afectacion2 = new Contable();
                $afectacion2->setNcc_id($this->prm->ncc_id);
                $afectacion2->setGpo_id($this->gpo_id);
                $afectacion2->setUr_id($this->ur_id);
                $afectacion2->setClc_id($this->prm->clc_id);
                $afectacion2->setAfe_id($this->prm->afe_id);
                $afectacion2->select();

                if (isset($this->prm->aclc) && $this->prm->aclc != '') {
                    $this->prm->clc = $this->prm->aclc;
                }
                if (isset($this->prm->agpo) && $this->prm->agpo != '') {
                    $this->prm->gpo = $this->prm->agpo;
                }
                if (isset($this->prm->aur) && $this->prm->aur != '') {
                    $this->prm->ur = $this->prm->aur;
                }

                $percepcion = $afectacion2->getPercepcion();
                $retencion = $afectacion2->getRetencion();
                $amortizado = $afectacion2->getNccamortiza();

                $documento = ValidaGarantias::gtDocumento('', $this->prm->rfc_id, $this->prm->referencia);
                if ($afectacion['afe_fondoRotatorio'] == 'S' && $documento->fondoRotatorio == 'N') {
                    $this->mixScript('notify("El documento no es para Recuperaci&oacute;n de Fondo Rotatorio, sin embargo la Afectaci&oacute;n esta marcada para tal fin","error");
                                    $("#btn_guarda").show();
                    ');
                    return false;
                } elseif ($afectacion['afe_fondoRotatorio'] == 'N' && $documento->fondoRotatorio == 'S') {
                    $this->mixScript('notify("El documento es para Recuperaci&oacute;n de Fondo Rotatorio, sin embargo la Afectaci&oacute;n no esta marcada para tal fin","error");$("#btn_guarda").show();');
                    return false;
                }

                $this->prm->tipoMovimiento = ($this->prm->tipoimporte == 'percepcion' ? '+' : '-');
                if ($this->prm->tipoMovimiento == '+') {
                    $this->prm->percepcion = $this->prm->importe;
                    $this->prm->retencion = '0';
                } else {
                    $this->prm->retencion = $this->prm->importe;
                    $this->prm->percepcion = '0';
                }
                isset($this->prm->nccamortiza) ? ($this->prm->nccamortiza == '' ? $this->prm->nccamortiza = 0 : '') : '';

                Contable::abc($this->prm);
                //******* SI NO AMORTIZABA NADA Y AHORA SI ACTUALIZAMOS ASI MISMO SI AMORTIZABA Y AHORA NO ACTUALIZAMOS AMBOS CAMPOS DE LOS RESPECTIVOS NUMEROS DE CONTROL

                if (isset($this->prm->nccamortiza) && $this->prm->nccamortiza != null) {
                    $this->conectar();
                    $this->setSql('SET @amortizar = 1;');
                    $this->consultar();
                    $sql = '
                        UPDATE scp_Contable
                        SET ncc_amortizado=1
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id and ncc_id = :ncc_id
                    ';
                    $params = [
                        ':gpo_id' => $this->gpo_id,
                        ':ur_id'  => $this->ur_id,
                        ':ncc_id' => $this->prm->nccamortiza,
                    ];
                    $this->setSql($sql);
                    $this->consultar($params);
                    $this->setSql('SET @amortizar = NULL;');
                    $this->consultar();

                    $consulta = '
                        SELECT date_format(afe_fechaInicio,\'%Y/%d/%m\') as afe_fechaInicio,
                        date_format(afe_fechaFin,\'%Y/%d/%m\') as afe_fechaFin
                        FROM scp_Afectacion
                        JOIN scp_Contable USING(gpo_id,ur_id, clc_id,afe_id)
                        WHERE gpo_id = :gpo_id and ur_id = :ur_id and ncc_id = :ncc_id';
                    $params = [
                        ':gpo_id' => $this->gpo_id,
                        ':ur_id'  => $this->ur_id,
                        ':ncc_id' => $this->prm->nccamortiza,
                    ];
                    $this->setSql($consulta);
                    $this->consultar($params);
                    $datos = $this->extraer();

                    $sql2 = '
                        UPDATE scp_Afectacion SET
                        afe_fechaInicio=STR_TO_DATE(\'' . $datos['afe_fechaInicio'] . '\',\'%Y/%d/%m\'),
                        afe_fechaFin=STR_TO_DATE(\'' . $datos['afe_fechaFin'] . '\',\'%Y/%d/%m\')
                        WHERE afe_id = :afe_id
                        AND gpo_id = :gpo_id
                        AND ur_id = :ur_id';
                    $params = [
                        ':afe_id' => $this->prm->afe_id,
                        ':gpo_id' => $this->gpo_id,
                        ':ur_id'  => $this->ur_id,
                    ];
                    $this->setSql($sql2);
                    $this->consultar($params);
                    $this->desconectar();
                } else {
                    $tmpgpo = $tmpur = '';
                    if (isset($this->prm->agpo) && $this->prm->agpo != '') {
                        $tmpgpo = $this->prm->agpo;
                    } else {
                        $tmpgpo = $this->gpo_id;
                    }
                    if (isset($this->prm->aur) && $this->prm->aur != '') {
                        $tmpur = $this->prm->aur;
                    } else {
                        $tmpur = $this->ur_id;
                    }

                    $sql = '
                        UPDATE scp_Contable
                        SET ncc_nccamortiza=0,ncc_gpo=null,ncc_ur=null,ncc_clc=null
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND ncc_id = :ncc_id';
                    $params = [
                        ':gpo_id' => $tmpgpo,
                        ':ur_id'  => $tmpur,
                        ':ncc_id' => $this->prm->ncc_id,
                    ];
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar($params);
                    if ($amortizado != '') {
                        $sql = '
                            SELECT COUNT(ncc_id) AS Total
                            FROM scp_Contable
                            WHERE ncc_nccamortiza = :ncc_id
                            AND gpo_id = :gpo_id
                            AND ur_id = :ur_id;';
                        $params = [
                            ':ncc_id' => $amortizado,
                            ':gpo_id' => $this->gpo_id,
                            ':ur_id'  => $this->ur_id,
                        ];
                        $this->setSql($sql);
                        $this->consultar($params);
                        $row = $this->extraer();
                        if ($row['Total'] == 0) {

                            $this->setSql('SET @amortizar = 1;');
                            $this->consultar();

                            $sql = '
                                UPDATE scp_Contable
                                SET ncc_amortizado=2
                                WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND ncc_id = :ncc_id';
                            $params = [
                                ':gpo_id' => $this->gpo_id,
                                ':ur_id'  => $this->ur_id,
                                ':ncc_id' => $amortizado,
                            ];
                            $this->setSql($sql);
                            $this->consultar($params);

                            $this->setSql('SET @amortizar = NULL;');
                            $this->consultar();

                            $this->mixScript('notify("El numero de Control ' . $amortizado . ' ha quedado sin amortizaciones","warning");');
                        }
                    }
                    $this->desconectar();
                }
                break;
        } //FIN SWITCH ABC

        /******************* LOG DE LAS AFECTACIONES************************************/
        $rutalog = 'log/NumControl' . $this->gpo_id . $this->ur_id . '.txt';
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');

        foreach ($this->prm as $field => $value) {
            if (is_array($value)) {
                $tempdata .= '|' . $field . '=' . json_encode($value);
            } elseif (!is_object($value)) {
                $tempdata .= '|' . $field . '=' . $value;
            }
        }
        $tempdata .= '|IPADDRESS=' . $this->ip;
        $tempdata .= '|USRID=' . $this->usr_id;

        fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);

        $peso = filesize($rutalog);
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $rutamodificada = 'log/NumControl' . $this->gpo_id . $this->ur_id . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
        /******************* FIN DEL LOG************************************/
        switch ($this->prm->abc) {
            case 'a':
                if (isset($this->prm->ciclos) && $this->prm->ciclos > 0) {
                    $this->mixScript('notify("Se han agregado correctamente los numeros de control","success");');
                } else {
                    $this->mixScript('notify("Se ha agregado correctamente el numero de control ' . $num_id . '","success");');
                }
                break;
            case 'b':
                $this->mixScript('notify("Se ha elimino el numero de control ' . $this->prm->ncc_id . '","success");');
                break;
            case 'c':
                $this->mixScript('notify("Se ha actualizado el numero de control ' . $this->prm->ncc_id . ' correctamente","success");');
                break;
        }
        $this->mixScript('
            $("#loading").hide();
            comunica("' . $this->pantalla . '",{    metodo:"afectacion",    afe_id:"' . $this->prm->afe_id . '",    tipo:"' . $afectacion['tcl_id'] . '"});
        ');
    }

    /**FUNCION QUE VALIDA EL DEVENGADO DEL COMPROMISO ANTES DE AUTORIZAR*/
    private function ValidaCompromiso($cmp_id, $afe_id)
    {
        $return = new stdClass();
        $datos = $this->model->importesValidaCompromiso($cmp_id, $afe_id);
        $cmp_importe = $datos['cmp_importe'] + floatval($datos['Ajuste']);
        $devengado = floatval($datos['Devengado']);
        $Adevengar = floatval($datos['aDevengar']);

        /*---------------------- VALIDACIONES DE IMPORTES ------------------------------------*/
        if ($Adevengar > round((float) ($cmp_importe - $devengado), 2)) {
            $return->error = true;
            $return->cmp_id = $cmp_id;
            $return->mensaje = 'EL IMPORTE A DEVENGAR ES MAYOR AL DISPONIBLE A DEVENGAR DEL COMPROMISO: COMPROMISO N°:' . $cmp_id;
            $return->excedente = round((float) ($Adevengar - ($cmp_importe - $devengado)), 2);
            $return->pordevengar = round((float) ($cmp_importe - $devengado), 2);
        } elseif ($datos['ecm_id'] == 'C') {
            $return->error = true;
            $return->mensaje = 'EL COMPROMISO A DEVENGAR ESTA CANCELADO!! COMPROMISO N°:' . $cmp_id;
        } else {
            $return->error = false;
        }
        return $return;
    }

    /**FUNCION QUE AUTORIZA LA AFECTACION */
    /**
     * EN ESTE METODO SE TIENE QUE HACER LA MODIFICACIÓN
     */
    private function AutorizaAfectacion()
    {
        /**ESTE METODO ES TAMBIÉN UTILIZADO POR LA CARGA MASIVA */
        $mensaje = '';
        $afes = '';
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;

        $mes = 0;
        if (date('Y') > APP_EJERCICIO) {
            $mes = 12;
        } else {
            $mes = date('m');
        }

        $tri = $this->model->getTrimestre($mes);
        $html = '<table style="width:100%" class="table table-striped table-bordered">
            <tr class="warning" role="row">
                <th style="width:5%;text-align:center" ># de Afectación</th>
                <th>Unidad Ejecutora</th>
                <th>AAC</th>
                <th>Clave Financiamiento</th>
                <th>Fecha Elaboración</th>
                <th align="right">Percepciones</th>
                <th align="right">Retenciones</th>
                <th align="center">Resultado</th>
            </tr>';

        $autorizar = true;

        foreach ($this->prm->data as $afectacion) {
            if ($afes == '') {
                $afes .= $afectacion->afe_id;
            } else {
                $afes .= ',' . $afectacion->afe_id;
            }
        }

        //OBTIENE LA INFORMACIÓN DE LA AFECTACIÓN O AFECTACIONES
        $af = $this->model->getAutorizaAfectaciones($afes);

        foreach ($af['datos'] as $afe) {
            /*----- VALIDAR EL ESTADO DE LOS BLOQUEOS DE LA DIRECCION DE PRESUPUESTO -----*/
            $valida = $this->model->gtBloqueos($afe['tcl_id'], $afe['stc_id'], $afe['ecf_id'], $this->prm->opcion);
            if (!$valida->valido) {
                $autorizar = false;
                $mensaje = 'La opcion [' . $this->prm->opcion . '] esta bloqueada, consulte a su analista, NO es necesario levantar un ticket!!';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }

            /**----- VALIDAR SI EXISTE UN FOLIO DE ADECUACIÓN PERSUPUESTAL DE FORMA MANUAL ------*/
            /**ESTA VALIDACIÓN SOLO SERÁ PARA LAS AFECTACIONES DE MANERA MANUAL */
            /**SE ESTA VERIFICADO QUE NINGUN FOLIO DE ADECUACIONES PRESUPUESTALES ESTE EN TRANSITO 08-07-2019 TICKET 550148*/
            if ($afe['tem_carga'] == '') {
                switch ($afe['tcl_id']) {
                    case 'A':
                    case 'B':
                        //SE MANDAN LOS ID'S DE LA UR
                        //EL TIPO DE ACCIÓN QUE SE ESTA REALIZANDO (COMPROMISO,AUTORIZACIÓN)
                        //LA ACCIÓN REALIZADA (AUTORIZACIÓN, O GENERACIÓN)
                        //LA AFECTACIÓN A COMPROBAR
                        $tieneFolio = $this->model->getFolioDevengadoComprometido(
                            $afe['gpo_id'],
                            $afe['ur_id'],
                            'X',
                            'X',
                            'X',
                            'X',
                            'X',
                            'X',
                            'X',
                            'A',
                            'A',
                            $afe['afe_id']
                        );
                        //EN CASO QUE SE ENCUENTRE ALGÚN FOLIO SE MANDARÁ EL MENSAJE DE ERROR
                        if ($tieneFolio->estado == true) {
                            $this->mixScript('
                                notify("' . $tieneFolio->mensaje . '","error");
                                ');
                            return false;
                        }
                        break;
                    default:
                        break;
                }
            }
            /*------ SI ES DE CARGA MASIVA VERIFICAR EL ESTADO DE LA CARGA --------*/
            if ($afe['ecr_id'] != '' && !in_array($afe['ecr_id'], ['99', '100'])) {
                $autorizar = false;
                $mensaje = 'LA AFECTACION ES DE CARGA MASIVA Y ESTA AUN NO TERMINA O QUEDO TRUNCA';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }

            if ($afe['eaf_id'] != 'G') {
                $autorizar = false;
                $mensaje = 'LA AFECTACION TIENE UN ESTADO INCORRECTO, NO SE PUEDE AUTORIZAR';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }
            /*-------- VALIDAR EL MES DE LA AFECTACIóN ---------*/
            if (!in_array($afe['MES'], $tri->meses)) {
                $autorizar = false;
                $mensaje = 'LA AFECTACIÓN NO PERTENECE AL TRIMESTRE ACTUAL';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }
            /*--------- VERIFICAR QUE LA AFECTACION TENGA NUMEROS DE CONTROL ----------*/
            if ($afe['Detalle'] < 1 || $afe['Detalle'] == '') {
                $autorizar = false;
                $mensaje = 'LA AFECTACIÓN NO TIENE NUMEROS DE CONTROL';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }
            /*--------- VALIDAR PERCEPCIONES CONTRA RETENCIONES ---------------*/
            if ($afe['Percepcion'] < $afe['Retencion']) {
                $mensaje = 'LAS RETENCIONES DE LA AFECTACION SON MAYORES A LAS PERCEPCIONES, AFECTACION EN NEGATIVO!';
                $this->mixScript('notify("' . $mensaje . '","warning");');
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                unset($conexion);
            }
            /*--------VALIDAR ESTADO DE LA AFECTACION -------------*/
            if ($afe['eaf_id'] == 'A' || $afe['eaf_id'] == 'C') {
                $autorizar = false;
                $mensaje = 'LA AFECTACIóN TIENE UN ESTADO INCORRECTO Y NO SE PUEDE AUTORIZAR';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }

            /********VERFICAR  EL DEVENGADO DE LOS DOCUMENTOS*************/
            $sql = '
            SELECT
            gpo_id,ur_id,PP,bdo_percepcionPresupuestal,PC,bdo_percepcionContable,RC,bdo_retencionContable,bdo_referencia,rfc_id
            FROM cat_BancoDocumentos
            INNER JOIN(
            SELECT
            gpo_id,ur_id,bdo_id,SUM(PP) AS PP,SUM(PC) AS PC, SUM(RC) AS RC
            FROM (
            SELECT
            gpo_id,ur_id,bdo_id,ncp_percepcionNeto PP,0 AS PC, 0 AS RC
            FROM scp_Presupuestal
            WHERE gpo_id = ? AND ur_id = ? AND afe_id = ?
            UNION ALL
            SELECT
            gpo_id,ur_id,bdo_id,0 AS PP,ncc_percepcion AS PC, ncc_retencion AS RC
            FROM scp_Contable
            WHERE gpo_id = ? AND ur_id = ? AND afe_id = ?
            ) m
            GROUP BY bdo_id, m.gpo_id,m.ur_id
            ) c USING ( gpo_id,ur_id,bdo_id )
            LEFT JOIN(
            SELECT
            gpo_id,ur_id,bdo_id,SUM(PP) AS DPP,SUM(PC) AS DPC, SUM(RC) AS DRC
            FROM (
            SELECT
            gpo_id,ur_id,bdo_id,ncp_percepcionNeto PP,0 AS PC, 0 AS RC
            FROM scp_Presupuestal
            INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,afe_id )
            WHERE gpo_id = ? AND ur_id = ? AND eaf_id ="A"
            UNION ALL
            SELECT
            gpo_id,ur_id,bdo_id,0 AS PP,ncc_percepcion AS PC, ncc_retencion AS RC
            FROM scp_Contable
            INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,afe_id )
            WHERE gpo_id = ? AND ur_id = ? AND eaf_id ="A"
            ) n
            GROUP BY bdo_id,n.gpo_id,n.ur_id
            ) d USING ( gpo_id,ur_id,bdo_id )
            WHERE gpo_id= ? AND ur_id = ?
            AND
            ( ( DPP + PP > bdo_percepcionPresupuestal) OR
            ( DPC + PC >bdo_percepcionContable ) OR
            ( DRC + RC >bdo_retencionContable ) )
            ';
            $params = [$gpo_id, $ur_id, $afe['afe_id'], $gpo_id, $ur_id, $afe['afe_id'], $gpo_id, $ur_id, $gpo_id, $ur_id, $gpo_id, $ur_id];
            $datos = $this->model->getDatos($sql, false, $params);

            if ($datos['registros'] > 0) {
                $tm = $datos['datos'];
                $autorizar = false;
                $mensaje = 'ERROR AL DEVENGAR UNO O MAS DOCUMENTOS';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $html .= '<tr><td colspan="8">';
                foreach ($tm as $tmp) {
                    $html .= '
                        <div class="row danger">
                        Error al Devengar el Importe del Documento '
                        . $tmp['bdo_referencia'] . ' - ' . $tmp['rfc_id'] . '  $' . number_format(floatval($tmp['bdo_importe']), 2)
                        . '</div>';
                }
                $html .= '</td></tr>';
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);

                continue;
            }
            /****VERIFICAR EL DISPONIBLE PARA DEVENGAR DE LAS CLAVES PRESUPUESTALES DE LA AFECTACIóN  *****/
            $datos = $this->model->getPreDevDisponible($afe, $tri);

            if ($datos['registros'] > 0) {
                $autorizar = false;
                $mensaje = 'ERROR AL DEVENGAR EL IMPORTE DE LA CLAVE PRESUPUESTAL';
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $html .= ' <tr><td colspan="8" style="padding-left:20px; font-size:10px;background-color:rgba(220,220,220,.6) !important;color:#F00; font-family:Arial;">';

                foreach ($datos['datos'] as $tmp) {
                    $html .= '<div class="row danger">
                            <span class="fa-solid fa-chevrons-right"></span>ERROR:
                            CLAVE =[' . $tmp['pre_ClaveCompleta'] . ']
                            Importe a Devengar de la Clave : $' . number_format(floatval($tmp['Percepcion']), 2) . ' : Importe Disponible $' . number_format(($tmp['Modificado'] - $tmp['Devengado']), 2);

                    $mensaje .= 'Clave con Error:' . $tmp['pre_ClaveCompleta'];
                    $mensaje .= 'Importe a Devengar de la Clave : $' . number_format(floatval($tmp['Percepcion']), 2) . ' : Importe Disponible $' . number_format(($tmp['Modificado'] - $tmp['Devengado']), 2);

                    if (($tmp['Devengado'] + $tmp['Percepcion']) > $tmp['Comprometido']) {
                        $html .= ' ::: EL IMPORTE A DEVENGAR SUPERA EL PRESUPUESTO COMPROMETIDO POR $' . round((float) ($tmp['Devengado'] + $tmp['Percepcion']) - $tmp['Comprometido'], 2);
                        $mensaje .= '[EL IMPORTE A DEVENGAR SUPERA EL PRESUPUESTO COMPROMETIDO POR $' . round((float) ($tmp['Devengado'] + $tmp['Percepcion']) - $tmp['Comprometido'], 2) . ']';
                    }
                    if ($tmp['pre_mov'] == 'M') {
                        $html .= ' ::LA CLAVE ESTA SIENDO USADA POR UNA CARGA MASIVA, POR FAVOR ESPERE A QUE ESTA CARGA TERMINE ';
                        $mensaje .= ' ::LA CLAVE ESTA SIENDO USADA POR UNA CARGA MASIVA, POR FAVOR ESPERE A QUE ESTA CARGA TERMINE ';
                    }
                    $html .= '</td></tr>';
                }
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $mensaje);
                continue;
            }
            /*---------- VERIFICAR EL DEVENGADO DE LOS COMPROMISOS  --------------*/
            $sql = '
                SELECT DISTINCT cmp_id
                FROM scp_Presupuestal
                WHERE gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND afe_id= :afe_id ';
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id,
                ':afe_id' => $afe['afe_id'],
            ];
            $datos = $this->model->getDatos($sql, false, $params);
            $error = false;
            if ($datos['registros'] > 0) {
                foreach ($datos['datos'] as $cmp) {
                    $result = $this->ValidaCompromiso($cmp['cmp_id'], $afe['afe_id']);
                    if ($result->error === true) {
                        $error = true;
                        $html .= $this->vista->vwRechazoAutoAfe($afe, $result->mensaje);
                        $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] . ' RECHAZADA POR::' . $result->mensaje);
                        $mensaje .= $result->mensaje;
                    }
                }
                if ($error == true) {
                    $autorizar = false;
                    continue;
                }
            }
            /*----------- AUTORIZAR LA AFECTACIóN Y ACTUALIZAR COMPROMISOS ------------*/
            $user = $this->usr_id;
            $ip = ($this->ip);
            $ip = (isset($ip) && $ip != '::1') ? $ip : '127.0.0.1';

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SET
                @usr_ip = INET_ATON(:ip),
                @usr_id = :usr_id,
                @bit_observacion = "AUTORIZACION DE LA AFECTACION - CAPTURA MANUAL - S 2 1 2";
            ');
            $params = [
                ':ip'     => $ip,
                ':usr_id' => $user,
            ];
            $conexion->consultar($params);

            $sql = 'CALL sp_AutorizarAfectacion' . APP_EJERCICIO . '(:gpo_id,:ur_id,:afe_id, INET_ATON(:ip),:usr_id)';
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id,
                ':afe_id' => $afe['afe_id'],
                ':ip'     => $ip,
                ':usr_id' => $user,
            ];
            $conexion->setSql($sql);
            $conexion->consultar($params);

            $conexion->setSql('SELECT @banderaPresupuesto AS band ,@mensajePresupuesto AS mensaje');
            $conexion->consultar();
            $data = $conexion->extraer();
            if ($data['band'] != 1) {
                $html .= $data['mensaje'] . '</td></tr>';
                $mensaje = $data['mensaje'];
                $html .= $this->vista->vwRechazoAutoAfe($afe, $mensaje);
                $this->guardaLogAutorizacion('AFECTACION ' . $afe['afe_id'] .
                    ' RECHAZADA POR EXCEPCION EN EL PROCEDIMIENTO ALMACENADO CON MENSAJE::' . $data['mensaje']);
                $conexion->desconectar();
                $autorizar = false;
                unset($conexion);
                continue;
            }
            $html .= '
                <tr style="background-color:rgba(48,232,38,.2)!important;">
                    <td style="width:5%;text-align:center">' . $afe['afe_id'] . '</td>
                    <td>' . $afe['ue_id'] . '</td>
                    <td>' . $afe['AAC'] . '</td>
                    <td>' . $afe['clavefin'] . '</td>
                    <td>' . $afe['afe_fechaElaboracion'] . '</td>
                    <td align="right"><span style="float:left">$</span>' .
                number_format(floatval($afe['afe_percepcion']), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>'
                . number_format(floatval($afe['afe_retencion']), 2) . '</td>
                    <td align="center">Autorizada</td>
                </tr>';
            $conexion->desconectar();
            unset($conexion);
        }
        $html .= '</table>';

        if ($af['registros'] == 1) {
            $this->mixScript(' notify("La afectación ha sido ' . ($autorizar ? 'Autorizada' : ' Rechazada ' . $mensaje) . '","' . ($autorizar ? 'success' : 'error') . '",false);');
            if ($autorizar) {
                $this->mixScript($this->wrLlamaLlenaTabla('tbl_Conceptos'));
            }
        } elseif ($af['registros'] > 1) {
            $btnregresa = new stdClass();
            $btnregresa->id = 'btnregresa';
            $btnregresa->icon = 'circle-left me-2';
            $btnregresa->name = 'nuevo';
            $btnregresa->color = 'gris';
            $btnregresa->text = 'Regresar';
            $btnregresa->value = 'Regresar';
            $btnregresa = $this->ponBoton($btnregresa);
            $this->agregaTools($btnregresa);
            $this->mixHtml($html);
            $this->mixScript('
                $("#btnregresa").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"main",
                        tipo:"' . $this->prm->tipo . '",
                        nombre:"' . $this->prm->nombre . '"
                    })
                });
            ');
        }
    }

    /* CANCELA UNA AFECTACION */
    private function CancelaAfectacion()
    {
        $afe_id = $this->prm->afe_id;
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        /*------- VERIFICAMOS LA AFECTACION Y EL ESTADO DE LA AFECTACION  --------*/
        $sql = '
            SELECT * FROM scp_Afectacion
            LEFT JOIN (
            SELECT * FROM scp_Presupuestal
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id
            ) Presupuestales USING( gpo_id,ur_id,afe_id )
            LEFT JOIN (
            SELECT * FROM scp_Contable
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id
            )Contables USING( gpo_id,ur_id,afe_id )
            INNER JOIN cat_SubtipoClc USING( tcl_id,stc_id)
            INNER JOIN cat_TipoClc USING (tcl_id)
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id
            ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':afe_id' => $afe_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        /*--- SI REGRESA 0 ES QUE LA AFECTACIóN NO EXISTE ---*/
        if ($this->getNumeroRegistros() == 0) {
            $this->mixScript('notify("Error en la Afectación","error");');
            $this->desconectar();
            unset($data);
            return;
        }

        $data = $this->extraer();
        /*--- SI EL ESTADO DE LA AFECTACIóN ES DIFERENTE DE "A" ES QUE SE QUIERE CANCELAR UNA AFECTACION ERRONEA ---*/
        if ($data['eaf_id'] != 'A') {
            $this->mixScript(
                'notify("Error en el estado de la Afectación, debe estar Autorizada para poder Cancelarla","error");'
            );
            $this->desconectar();
            unset($data);
            return;
        }
        $user = $this->usr_id;
        $ip = ($this->ip);
        $ip = (isset($ip) && $ip != '::1') ? $ip : '127.0.0.1';

        $this->setSql('
            SET @usr_ip = INET_ATON(:ip),
            @usr_id = :usr,
            @bit_observacion = "SE CANCELA LA AFECTACION DESDE LA PANTALLA -CAPTURA MANUAL - S 2 1 2"
        ');
        $params = [
            ':ip'  => $ip,
            ':usr' => $this->usr_id,
        ];
        $this->consultar($params);
        $sql = 'CALL sp_CancelarAfectacion' . APP_EJERCICIO . '(:gpo_id,:ur_id,:afe_id, INET_ATON(:ip),:usr_id);';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':afe_id' => $afe_id,
            ':ip'     => $ip,
            ':usr_id' => $user,
        ];
        $this->setSql($sql);
        $this->consultar($params);
        $this->setSql('
            SELECT @banderaPresupuesto AS bandera,
            @mensajePresupuesto AS mensaje, @banderaContabilidad as bc, @mensajeContabilidad AS mc
        ');
        $this->consultar();
        $men = $this->extraer();
        $this->desconectar();
        $mensajee = '';
        if ($men['bandera'] == 0) {
            $this->mixScript('
                notify("' . $men['mensaje'] . '","error");
                comunica("' . $this->pantalla . '",{metodo:"main",tipo:"' . $data['tcl_id'] . '",nombre:"' . $data['tcl_nombre'] . '"});');
            $rutalog = 'log/CancelaAfe' . $gpo_id . $ur_id . '.txt';
            $file = fopen($rutalog, 'a');
            $tempdata = 'HUBO UN ERROR AL CANCELAR LA AFECTACION ' . $afe_id . ' MENSAJE DE ERROR:' . $men['mensaje'];

            $hoy = date('Y-m-d H-i-s');
            $tempdata .= '|IPADDRESS=' . $this->ip;
            $tempdata .= '|USRID=' . $this->usr_id;

            fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
            fclose($file);
            $peso = filesize($rutalog);
            $peso = ($peso / 1024) / 1024;
            if ($peso > 8) {
                $rutamodificada = 'log/CancelaAfe' . $this->gpo_id . $this->ur_id . $hoy . '.txt';
                rename($rutalog, $rutamodificada);
                $file = fopen($rutalog, 'a');
                fwrite($file, 'Creado:' . $hoy . PHP_EOL);
                fclose($file);
            }

            return false;
        } else {
            if ($men['bc'] != 1) {
                $mensajee = ' pero hubo un error en la poliza de cancelacion';
            }
        }

        /*--- SACAMOS  TODOS LOS COMPROMISOS DE LA AFECTACION PARA ACTUALIZARLOS ---*/
        $sql = '
            SELECT ncc_id,ncc_nccamortiza
            FROM scp_Contable
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND afe_id= :afe_id
            AND ncc_nccamortiza > 0';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':afe_id' => $afe_id,
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $amor = 'AMORTIZADOS  ACTUALIZADOS POR LA CANCELACION{';
        if ($conexion->getNumeroRegistros() > 0) {
            while ($nn = $conexion->extraer()) {
                if ($nn['ncc_nccamortiza'] > 0) {
                    $this->actualizaAmortizados($nn['ncc_nccamortiza']);
                    $amor .= '[ncc_id:' . $nn['ncc_nccamortiza'] . '],';
                }
            }
        }
        $amor .= '}';
        $conexion->desconectar();
        unset($conexion);

        /******************* LOG DE LAS AFECTACIONES************************************/
        $rutalog = 'log/CancelaAfe' . $gpo_id . $ur_id . '.txt';
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $tempdata = 'AFECTACION ' . $afe_id . ' CANCELADA::' . $amor;
        $hoy = date('Y-m-d H-i-s');
        $tempdata .= '|IPADDRESS=' . $this->ip;
        $tempdata .= '|USRID=' . $this->usr_id;

        fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog);
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $rutamodificada = 'log/CancelaAfe' . $this->gpo_id . $this->ur_id . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
        /******************* FIN DEL LOG************************************/
        $this->mixScript('
            notify("Se ha cancelado la Afectación de manera exitosa:' . $mensajee . '","success");
            comunica("' . $this->pantalla . '",{metodo:"main",tipo:"' . $data['tcl_id'] . '",nombre:"' . $data['tcl_nombre'] . '"})
        ');
    }

    /**BUSCA LA CUENTA CONTABLE ESCRITA */
    private function ChecaCuenta()
    {
        $cuentas = explode('.', $this->prm->cuenta);
        $ger_id = substr($cuentas[0], 0, 1);
        $cgp_id = substr($cuentas[0], 1, 1);
        $rub_id = substr($cuentas[0], 2, 1);
        $cgcu_id = substr($cuentas[0], 3, 1);
        $cgsc_id = substr($cuentas[0], 4, 3);
        $cta_gpo_id = substr($cuentas[1], 0, 1);
        $cta_ur_id = substr($cuentas[1], 1, 2);
        $cta_ue_id = $cuentas[2];
        $cgc_id = $cuentas[3];

        if ((strlen($this->prm->cuenta)) == 25) {
            $cta_id = $cuentas[4] . $cuentas[5];
        } else {
            $cta_id = $cuentas[4];
        }

        $sql = '
            SELECT cta_nombre
            FROM   cg_PlanCuentas
            INNER JOIN cat_CgSubcuenta USING ( ger_id,cgp_id,rub_id,cgcu_id,cgsc_id )
            WHERE  ger_id = "' . $ger_id . '"
            AND cgp_id = "' . $cgp_id . '"
            AND rub_id = "' . $rub_id . '"
            AND cgcu_id = "' . $cgcu_id . '"
            AND cgsc_id = "' . $cgsc_id . '"
            AND cta_gpo_id = "' . $cta_gpo_id . '"
            AND cta_ur_id = "' . $cta_ur_id . '"
            AND cta_ue_id = "' . $cta_ue_id . '"
            AND cgc_id = "' . $cgc_id . '"
            AND cta_id = "' . $cta_id . '"
            AND ecu_id != 2;
            ';

        $cuenta = false;
        $msg = '';
        $tmpgpo = $this->gpo_id . $this->ur_id;
        $tmp2gpo = $cta_gpo_id . $cta_ur_id;
        switch ($tmpgpo) {
            case 902:
                if ($tmpgpo == $tmp2gpo || $tmp2gpo == 116) {
                    $cuenta = true;
                }
                break;
            case 118:
                if ($tmpgpo == $tmp2gpo || in_array($tmp2gpo, $this->urs318)) {
                    $cuenta = true;
                }
                break;
            case 901:
                $valExc = new AccesoDatos();
                $valExc->conectar();
                $valExc->setSql('
                    SELECT exc_id
                    FROM scp_ExcepcionPlanCuentas exc
                    INNER JOIN scp_ExcepcionTipoClc vinexc USING (ext_id)
                    WHERE vinexc.gpo_id = ' . $this->gpo_id . '
                    AND vinexc.ur_id = ' . $this->ur_id . '
                    AND vinexc.tcl_id = "' . $this->tipo . '"
                    AND exc.ger_id = ' . $ger_id . '
                    AND exc.cgp_id = ' . $cgp_id . '
                    AND exc.rub_id = ' . $rub_id . '
                    AND exc.cgcu_id = ' . $cgcu_id . '
                    AND exc.cgsc_id = ' . $cgsc_id . '
                    AND exc.cta_gpo_id = ' . $cta_gpo_id . '
                    AND exc.cta_ur_id = ' . $cta_ur_id . '
                    AND exc.cta_ue_id = ' . $cta_ue_id . '
                    AND exc.cgc_id = ' . $cgc_id . '
                    AND exc.cta_id = "' . $cta_id . '"
                    ');
                $valExc->consultar();
                if ($valExc->getNumeroRegistros() == 1) {
                    $cuenta = true;
                } else {
                    $cuenta = false;
                }
                $valExc->desconectar();
                break;

            default:
                if ($tmpgpo == $tmp2gpo) {
                    $cuenta = true;
                } elseif (
                    isset($this->prm->tipo) && ($this->prm->tipo == 'D' || $this->prm->tipo == 'E')
                    && isset($this->prm->valido) && $this->prm->valido == 'true'
                ) {
                    $cuenta = true;
                } else {
                    $cuenta = false;
                }
                break;
        }

        if ($cuenta) {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar();
            $row = $conexion->extraer();
            $conexion->desconectar();
            if (($row)) {
                $this->mixScript('$("#cuenta_descripcion").val("' . str_replace('"', '', $row['cta_nombre']) . '");');
                $amortizar = false;
                if (!isset($this->prm->tipo)) {
                    return;
                }
                switch ($this->prm->tipo) {
                    case 'A':
                        break;
                    case 'F':
                    case 'G':
                        $this->mixScript('
                            comunica("' . $this->pantalla . '",{
                                metodo:"pagadoras",
                                tcl_id:"' . $this->prm->tipo . '",
                                stc_id:"' . $this->prm->subtipo . '",
                                cuenta:"' . $cuentas[0] . '",
                                cta:"' . $cuentas[3] . '",
                                afe_id:"' . $this->prm->afe_id . '",
                                abc:"' . $this->prm->abc . '",
                                cuentacompleta:"' . $this->prm->cuenta . '"
                            });
                        ');

                        if ($cuentas[0] . $cuentas[3] == '11239008') {
                            $this->mixScript(' $("#divpercepciones").show();');
                        } else {
                            $this->mixScript('
                                $("#divpercepciones").hide();
                                $("#percepciones").removeProp("checked");
                            ');
                        }
                        break;
                    case 'C':
                        if ($cuenta == 21171) {
                            $this->mixScript('
                                comunica("' . $this->pantalla . '",{
                                    metodo:"pagadoras",
                                    tcl_id:"' . $this->prm->tipo . '",
                                    stc_id:"' . $this->prm->subtipo . '",
                                    cuenta:"' . $cuentas[0] . '",
                                    cta:"' . $cuentas[3] . '"  ,
                                    afe_id:"' . $this->prm->afe_id . '",
                                    abc:"' . $this->prm->abc . '",
                                    cuentacompleta:"' . $this->prm->cuenta . '"
                                })
                            ');
                        }

                        if ($this->prm->subtipo == 01 && $this->prm->cuenta != '21194.118.001.002.0230' && $this->prm->cuenta != '21194.118.001.003.0235' && $this->prm->cuenta != '21194.118.001.004.0236') {
                            $this->mixScript('
                                $("#divpagadoras").show();
                                $("#clc_pg,#squaredTwo").attr("checked","1");
                                $("#muestraCLC").show();
                            ');
                        } elseif ($this->prm->subtipo == 01 && ($this->prm->cuenta === '21194.118.001.002.0230' || $this->prm->cuenta === '21194.118.001.003.0235' || $this->prm->cuenta === '21194.118.001.004.0236')) {
                            $this->mixScript('
                                $("#divpagadoras").hide();
                                $("#clc_pg,#squaredTwo").removeAttr("checked");
                                $("#muestraCLC").hide();
                            ');
                        }
                        break;
                    case 'M':
                        if ($this->prm->cuenta == '21999.116.006.003.0001' && $this->prm->subtipo == 15) {
                            $this->mixScript('
                                $("#rfc_id").val("MSM850101LZA");
                                $("#rfc_id").attr("disabled","true");
                                if ($("#rfc_id").lenght != 0) {
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"proveedor",
                                        rfc:$("#rfc_id").val(),
                                        abc:"' . $this->prm->abc . '"
                                    },
                                    false,false
                                    );
                                }
                            ');
                        }
                        break;
                }
            } else {
                $this->mixScript('$("#cuenta_descripcion").val("");');
                $this->mixScript('
                    alert("Atención \n La cuenta ' . $this->prm->cuenta . ' no existe en la base de datos!!");
                    $("#cuentacontapre").val("");
                ');
                return false;
            }
        } else {
            $this->mixScript('
                $("#cuenta_descripcion").val("La cuenta ' . $this->prm->cuenta . ' ' . ($msg == '' ? 'no es valida para su UR' : $msg) . '");
                $("#cuentacontapre").val("");
            ');
        }
    }

    /**VALIDAR EL FOLIO DE LINEA DE CAPTURA */
    private function checaFolio()
    {
        //0014AAEC824230111461    pe 388   TENENCIA VEHICULARES 328         PABIC
        $folio = $this->prm->folio;
        $numerico = false;
        if (is_numeric($folio) && ($this->prm->tcl_id == 'A' || $this->prm->tcl_id == 'B' || $this->prm->tcl_id == 'G')) {
            $numerico = true;
        }

        if ($numerico) {
            $sql = '
                SELECT gpo_id,ur_id,clc_id,afe_id,ncp_id,ncp_concepto,ncp_percepcionNeto as percepcion,pe_id,pe_nombre FROM scp_Presupuestal
                JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
                WHERE ncp_referencia = ?
                ';
            $params = [$this->prm->folio];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $data = $this->extraer();
            $this->desconectar();
            if ($data['ncp_id'] != '') {
                if ($data['gpo_id'] != $this->gpo_id || $data['ur_id'] != $this->ur_id) {
                    $this->mixScript('
                        $("#infolc").html("El Folio <strong>' . $this->prm->folio . '</strong> esta siendo utilizado por otra Unidad Responsable");
                        $("#infolc").css("display","block");
                        $("#NumReferencia").val("");
                    ');
                } else {
                    $total = 0;
                    $div = '<div id="detallelineacaptura"  style="position:absoulte; top:50%; display:block; left:0%; background-color:#FFF; border-radius:0px 0px 5px 5px; ';
                    $div .= 'padding:5px; width:100%; z-index:-1050; height:100px; overflow:auto; border-left:1px solid #AAA;border-rigth:1px solid #AAA;border-bottom:1px solid #AAA;';
                    $div .= 'padding:10px;">';
                    $div .= '<a id="cerrardetalle">X Cerrar</a><br>';
                    $div .= '<table style="width:100%;"><tr style="background-color:#EAEAEA;">
                                <th>Afectación</td>
                                <th>NÂ° Ctrl</td>
                                <th>Concepto</td>
                                <th>Importe</td>
                            </tr>';
                    $celda = 0;
                    $color = '';
                    $pe_id = $data['pe_id'];
                    $pe_nombre = $data['pe_nombre'];
                    do {
                        if ($celda == 1) {
                            $color = '#EAEAEA;';
                            $celda = 0;
                        } else {
                            $color = '#FFF;';
                            $celda = 1;
                        }
                        $div .= '
                            <tr>
                                <td style="background-color:' . $color . '">' . $data['afe_id'] . '</td>
                                <td style="background-color:' . $color . '">' . $data['ncp_id'] . '</td>
                                <td style="background-color:' . $color . '">' . $data['ncp_concepto'] . '</td>
                                <td style="background-color:' . $color . '" align="right">$' . number_format(floatval($data['percepcion']), 2) . '</td>
                            </tr>
                        ';
                        $total += $data['percepcion'];
                    } while ($data = $this->extraer());
                    $div .= '</div>';

                    $html = 'El Folio <strong>' . $this->prm->folio . '</strong> tiene registrados movimientos por la cantidad de:<strong> $' . number_format(floatval($total), 2) . '</strong>';
                    $html .= '<a id="detallelc">Detalle</a><br/> y con uso de la Partida Específica' . $pe_id . ' - ' . $pe_nombre;

                    $this->setContenedor('contenedordetalle');
                    $this->mixHtml($div);
                    $this->mixScript('
                        $("#infolc").html(\'' . $html . '\');
                        $("#detallelineacaptura").hide();
                        $("#infolc").css("display","block");
                        $("#detallelc").click(function(){
                            $("#infolc").css("border-radius","5px 5px 0px 0px");
                            $("#infolc").css("border-bottom","none");
                            $("#detallelineacaptura").slideDown("fast");
                        });
                        $("#cerrardetalle").click(function(){
                            $("#detallelineacaptura").slideUp("fast");
                            $("#infolc").css("border-radius","5px");
                            $("#infolc").css("border-bottom","1px solid #AAA");
                        });
                    ');
                }
            } else {
                if ($this->prm->tcl_id == 'A') {
                    $mensaje = ' Este parece ser un folio valido para autopago, recuerde que para adjuntarla a la CLC de pago de Impuestos y Derechos Estatales,';
                    $mensaje .= ' las Partidas Especificas que puede utilizar son las referentes al';
                    $mensaje .= ' <strong>Impuesto 2% sobre nomina (394,395,396,397,398,399,411)</strong>';
                } elseif ($this->prm->tcl_id == 'G') {
                    $mensaje = ' Este parece ser un folio valido para autopago, recuerde que para adjuntarla a la CLC de  pago de Impuestos y Derechos Estatales,';
                    $mensaje .= ' las Partidas Especificas que puede utilizar son ';
                    $mensaje .= ' <strong>Partida Genérica 632 y Específica 720</strong>';
                } else {
                    $mensaje = ' Este parece ser un folio valido para autopago, recuerde que para adjuntar la afectación   CLC de  pago de Impuestos y Derechos Estatales,';
                    $mensaje .= ' las partidas Especificas que puede utilizar son la <strong>388</strong> ';
                    $mensaje .= ' para Tenencia Vehicular y <strong>328</strong> para PABIC, de lo contrario sus afectaciones no se podrán vincular a su CLC';
                }
                $this->mixScript('
                    $("#infolc").html("' . $mensaje . '");
                    $("#infolc").css("display","block");
                ');
            }
        } else {
            $this->mixScript('
                $("#infolc").html("");
                $("#infolc").css("display","none");
            ');
        }
    }

    /**MUESTRA LA INFORMACION DEL NUMERO DE CONTROL A AMORTIZAR */
    private function infoNumCtrl()
    {
        $this->setContenedor('info_clc');
        $totalamortizado = $resta = $totalaamortizar = 0;
        $gpo = '';
        $ur = '';
        $fechareferencia = '';
        if (isset($this->prm->opur) && $this->prm->opur != '') {
            $gpo = substr($this->prm->opur, 0, 1);
            $ur = substr($this->prm->opur, 1, 2);
        } else {
            $gpo = $this->gpo_id;
            $ur = $this->ur_id;
        }
        $html1 = '';
        $html2 = '';
        if (isset($this->prm->ncc_id) && $this->prm->ncc_id != '...') {
            $sql = '
                SELECT clc_id,tcl_id,tcl_nombre,ncc_id,ncc_concepto as Concepto,ncc_tipoMovimiento,IF(ncc_percepcion=0,ncc_retencion,ncc_percepcion) as Importe,
                ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,ncc_fechaReferencia,cta_nombre
                FROM scp_Contable
                INNER JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
                INNER JOIN cg_PlanCuentas USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
                INNER JOIN cat_TipoClc USING(tcl_id)
                WHERE ncc_id = :ncc_id AND  gpo_id = :gpo_id AND ur_id = :ur_id
                ';
            $params = [
                ':ncc_id' => $this->prm->ncc_id,
                ':gpo_id' => $gpo,
                ':ur_id'  => $ur,
            ];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $data = $this->extraer();

            $tmp = explode('-', $data['ncc_fechaReferencia']);
            $fechareferencia = $tmp[2] . '/' . $tmp[1] . '/' . $tmp[0];

            $ctacontable = $data['ger_id'] . $data['cgp_id'] . $data['rub_id'] . $data['cgcu_id'] . $data['cgsc_id'] . '.' . $data['cta_gpo_id'] . $data['cta_ur_id'] . '.' . $data['cta_ue_id'] . '.' . $data['cgc_id'] . '.' . $data['cta_id'];

            $html1 = '<legend>Estado del número de control  a amortizar</legend>';
            $html1 .= '<table style ="border:1px solid #AAA; box-shadow:1px 1px 1px #999; width:100%; padding:4px; font-size:14px;" id="tabladeamortizaciones"><tr>';
            $html = ' NÚMERO DE CONTROL A AMORTIZAR: <strong>' . $data['ncc_id'] . '</strong> LIGADO A LA CLC  <strong>' . $data['clc_id'] . '</strong><br/>
                        TIPO <strong>' . $data['tcl_id'] . ' - ' . $data['tcl_nombre'] . '</strong><br/>
                        CONCEPTO: <strong>' . $data['Concepto'] . '</strong><br/>
                        TIPO: <strong>' . ($data['ncc_tipoMovimiento'] == '+' ? 'Percepcion' : 'Retencion') . '</strong>
                    </td>
                    <td  align="right"  style="background-color:#E7FFDF; padding:3px; border-bottom:1px dotted #AAA;" >
                        IMPORTE <br/><strong>$' . number_format(floatval($data['Importe']), 2) . '</strong>
                    </td>
                </tr>';
            $totalaamortizar = $data['Importe'];
            $html .= '<tr><td colspan="4" style="background-color:#FFF; padding:6px; font-weight:bold;">Amortizado por</td></tr>';
            $html .= '<tr><td style="background-color:#EEEEEE; padding:6px;font-weight:bold;">&nbsp;</td>
                    <td style="background-color:#EEEEEE; padding:6px;font-weight:bold;">Numero de Control / Concepto</td>
                    <td style="background-color:#EEEEEE; padding:6px; font-weight:bold;" align="center">Movimiento</td>
                    <td style="background-color:#EEEEEE; padding:6px; font-weight:bold;" align="center">Importe</td>
                    <td style="background-color:#EEEEEE; padding:6px; font-weight:bold;" align="center">Estado</td>
                    </tr>';
            // bloque del SQL   | query block
            $sql = '
                SELECT ncc_id, IF(ncc_percepcion=0,ncc_retencion,ncc_percepcion) as Importe, ncc_concepto,ncc_tipoMovimiento,eaf_id
                FROM scp_Contable
                INNER JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
                INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id)
                WHERE ecl_id !=15 AND ncc_nccamortiza = ? AND eaf_id != "C"';
            $params = [$this->prm->ncc_id];
            $this->setSql($sql);
            $this->consultar($params);
            $num_id = (isset($this->prm->num_id) ? $this->prm->num_id : 0);
            $mas = 0;
            while ($data2 = $this->extraer()) {
                $html .= '
                    <tr style="' . ($data2['eaf_id'] == 'G' ? 'background-color:#FFF' : 'border:1px solid #090;background-color:#E7FFDF') . '"">
                        <td style="border-bottom:1px dotted #AAA; width:50px;padding:3px; color:#000;">' . ($num_id == $data2['ncc_id'] ? 'ACTUAL' : '-') . '</td>
                        <td style="border-bottom:1px dotted #AAA; padding:3px;">' . $data2['ncc_id'] . ' / ' . $data2['ncc_concepto'] . '</td>
                        <td style="border-bottom:1px dotted #AAA; padding:3px;" align="center">' . ($data2['ncc_tipoMovimiento'] == '+' ? 'Percepcion' : 'Retencion') . '</td>
                        <td align="right" style="border-bottom:1px dotted #AAA; padding:3px;" id="' . $data2['ncc_id'] . '">$' . number_format(floatval($data2['Importe']), 2) . '</td>
                        <td align="right" style="border-bottom:1px dotted #AAA; padding:3px;" id="' . $data2['ncc_id'] . '">' . ($data2['eaf_id'] == 'G' ? 'GENERADO' : 'AUTORIZADO') . '</td>
                    </tr>';
                $totalamortizado += $data2['Importe'];
                if ($num_id == $data2['ncc_id']) {
                    $mas = $data2['Importe'];
                }
            }
            $html .= '
                <tr>
                    <td colspan="3" align="right" style="padding:6px;">Total amortizado</td>
                    <td align="right" style="padding:2px;"> $ ' . number_format(floatval($totalamortizado), 2) . ' </td>
                </tr>';
            $html .= '
                <tr>
                    <td colspan="3" align="right" style="padding:2px;">Resta por amortizar</td>
                    <td align="right" style="padding:6px;" id="restaPorAmortizar"> $ ' . number_format(($totalaamortizar - $totalamortizado), 2) . ' </td>
                </tr>
            </table>';
        } else {
            $html = '<div class="alert alert-danger"><strong>Ha ocurrido un problema!!</strong></div>';
            $this->mixHtml($html1 . $html2 . $html);
            return;
        }
        $html2 = '';
        $tot = round((float) $totalaamortizar - $totalamortizado, 2);
        if ($this->prm->abc == 'c') {
            $tot += $mas;
        }
        if ($tot == 0) {
            $html2 = '
                <td colspan="4" style="background-color:#FE2E2E; padding:6px; border-bottom:1px dotted #AAA;">
                <span style="color:#FFF"><strong>ESTE NÚMERO DE CONTROL NO SE PUEDE AMORTIZAR YA QUE YA HA SIDO AMORTIZADO EN SU TOTALIDAD</strong></span><br/>';
        } else {
            $html2 = '<td colspan="4" style="background-color:#E7FFDF; padding:6px; border-bottom:1px dotted #AAA;">';
        }
        $this->mixHtml($html1 . $html2 . $html);
        $this->mixScript('
            var sizetable =50+ parseInt($("#tabladeamortizaciones").css("height"))+ parseInt($("#muestraCLC").css("height"));

            $("#contenedorpagadora").css("height",sizetable+"px");
            $("#cuentacontapre").val("' . $ctacontable . '");
            $("#cuentacontapre").attr("disabled","disabled");
            $("#cuenta_descripcion").val("' . $data['cta_nombre'] . '");
            $("#importeporamortizar").val("' . $tot . '");
            $("#importeporamortizar").attr("tipo","' . ($data['ncc_tipoMovimiento'] == '+' ? 'percepcion' : 'retencion') . '");

            ' . ($data['ncc_tipoMovimiento'] == '+' ?
            '     $("#percepciones").attr("disabled","disabled").removeAttr("checked");
                    $("#retenciones").prop("checked","checked").removeAttr("disabled");
                ' : '
                    $("#retenciones").attr("disabled","disabled").removeAttr("checked");
                    $("#percepciones").prop("checked","checked").removeAttr("disabled");
                ') . '

            document.getElementById("poramortizar").style.display="block";
            $("#fecha_referencia").val("' . $fechareferencia . '");
            $("#infoamortiza").css("display","block");
            if(' . $tot . '== 0){
                $("#btn_guarda").attr("disabled","disabled");
            }else{
                $("#btn_guarda").removeAttr("disabled");
            }
        ');
    }

    /**IMPRIME LAS AFECTACIONES POR RANGO */
    private function imprimir()
    {
        $btn_regresa = new stdClass();
        $btn_regresa->id = 'btn_regresa';
        $btn_regresa->icon = 'plus';
        $btn_regresa->name = 'nuevo';
        $btn_regresa->icon = 'circle-left me-2';
        $btn_regresa->color = 'gris';
        $btn_regresa->text = 'Regresar';
        $btn_regresa->value = 'Regresar';

        $btn_imprime = new stdClass();
        $btn_imprime->id = 'btn_imprime';
        $btn_imprime->icon = 'solid fa-rectangle-list me-2';
        $btn_imprime->name = 'nuevo';
        $btn_imprime->color = 'azul';
        $btn_imprime->text = 'Imprime';
        $btn_imprime->value = 'Imprime';

        $inicio = new stdClass();
        $inicio->id = 'afeinicial';
        $inicio->texto = 'Afectación inicial';
        $inicio->label = 'Afectación inicial';
        $inicio->enDiv = true;
        $inicio->formato = 'entero';
        $inicio->maxLenght = 3;

        $fin = new stdClass();
        $fin->texto = 'Afectación final';
        $fin->label = 'Afectación final';
        $fin->id = 'afefinal';

        $fin->enDiv = true;
        $fin->formato = 'entero';
        $fin->maxLenght = 3;

        $html = '';

        $html .= '<fieldset class="col-md-3 col-xs-3">';
        $html .= $this->ponInput($inicio);
        $html .= $this->ponInput($fin);
        $html .= '</fieldset>';
        $html .= '<fieldset class="col-md-12 col-xs-12">';
        $this->agregaTools($this->ponBoton($btn_regresa) . $this->ponBoton($btn_imprime));
        $html .= '</fieldset>';

        $this->mixHtml($html);
        $this->mixScript('
        /**
         * Usuario: evenegass
         * Fecha Actualización: 04/11/2019 09:33 PM
         * Comentarios: Se actualizo la ruta al archivo rep_Afectaciones
         */
            $("#btn_imprime").click(function(){
                var a = document.createElement(\'a\');
                a.target="_blank";
                if($("#afeinicial").val()=="" || $("#afefinal").val()==""){
                    alertify.alert("¿Continuar?", "¿Los datos son incompletos, se listaran todas las afectaciones del tipo ' . $this->prm->tipo . ' \n ¿Desea proceder?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "generarReporte",
                                tipo : "' . $this->prm->tipo . '"
                            });
                        }
                    ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }else{
                    a.href="rep/doc/scp/afectaciones/rep_Afectaciones.php?in="+ $("#afeinicial").val() + "&tipo=' . $this->prm->tipo . '&fi="+$("#afefinal").val();
                    document.body.appendChild(a);
                    a.click();
                }
            });
            $("#btn_regresa").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    tipo:"' . $this->prm->tipo . '",
                    nombre:"' . $this->prm->nombre . '"
                });
            });
        ');
    }

    private function generarReporte()
    {
        $this->mixScript('
            var a = document.createElement(\'a\');
            a.target="_blank";
            a.setAttribute("href", "rep/doc/scp/afectaciones/rep_Afectaciones.php?tipo=' . $this->prm->tipo . '");
            document.body.appendChild(a);
            a.click();
        ');
    }

    /**BUSCA REFERENCIAS DUPLICADAS PARA GASTOS DE OPERACION */
    private function duplicate()
    {
        $referencia = isset($this->prm->folio) && $this->prm->folio != '' ? $this->prm->folio : '';
        $proveedor = isset($this->prm->rfc) && $this->prm->rfc != '' ? $this->prm->rfc : '';
        $tdf_id = isset($this->prm->tdf_id) && $this->prm->tdf_id != '' ? $this->prm->tdf_id : '';
        if ($referencia == '' || $proveedor == '') {
            return false;
        }
        $docto = ValidaGarantias::gtDocumento('', $proveedor, $referencia, $tdf_id);

        if ($docto->valido == false) {
            if ($docto->error == 2) {
                $this->mixScript('notify("Existen dos documentos identicos en el Banco de Documentos, por favor use la tecla F1 para seleccionar el documento que quiere usar","warning");');
            } else {
                $this->mixScript('
                    notify("' . $docto->mensaje . '","warning");
                    $("#NumReferencia").val("");
                    $("#NumReferencia").attr("importe","0");
                    importes2();
                    importes();
                ');
            }
            unset($docto);
            return false;
        }
        if ($docto->sts_id != '1') {
            $this->mixScript('
                notify("El documento esta inhabilitado, no se puede usar","warning");
                $("#NumReferencia").val("");
                $("#NumReferencia").attr("importe","0");
                importes2();
                importes();
            ');
            return false;
        }
        $this->mixScript('
            imp = unformatNumber($("#ediimporte").val()) + ' . ($docto->importe - $docto->referenciado) . ';
            per = 0;
            ret = 0;
            if($("#ediimporte").attr("tipo") != undefined ){
                if( $("#ediimporte").attr("tipo") == "+" ){
                    per  = unformatNumber($("#ediimporte").val()) + ' . (round((float) ($docto->percepcionContable - ($docto->refPer - $docto->importePerR)), 2)) . ';
                    ret  = ' . (round((float) ($docto->retencionContable - ($docto->refRet - $docto->importeRetR)), 2)) . ';
                }else{
                    per  = ' . (round((float) ($docto->percepcionContable - ($docto->refPer - $docto->importePerR)), 2)) . ';
                    ret  = unformatNumber($("#ediimporte").val()) + ' . (round((float) ($docto->retencionContable - ($docto->refRet - $docto->importeRetR)), 2)) . ';
                }
            }

            $("#NumReferencia").attr("importe",imp);
            $("#NumReferencia").attr("retenciones",ret);
            $("#NumReferencia").attr("percepciones",per);
            $("#NumReferencia").attr("bdo_id","' . $docto->bdo_id . '")
            $("#div_link_info").fadeIn("slow");
            $("#div_link_info").css("display","table-cell");

            if($("#fecha_referencia").length == 1){
                $("#fecha_referencia").val("' . $docto->fechaDocumento . '");
                $("#fecha_referencia").attr("readonly","readonly");
            }
            importes2();
        ');

        if ($docto) {
            unset($docto);
        }
    }

    /**OBTIENE Y MUESTRA INFORMACION DEL COMPROMISO */
    private function infoCompromiso()
    {
        $this->setContenedor('det_fsinfo_referencia');
        $html = '';
        $importe = 0;
        $sql = '
            SELECT cmp_importe,cmp_concepto, rfc_id,rfc_nombre,rfc_paterno,rfc_materno,tcl_id,tcl_nombre,stc_id,stc_nombre,cff_id,fte_id,sft_id,ori_id,sor_id,
            cfi_id,cfi_anio, prg_id,spr_id,py_id,oa_id,cmp_fechaInicio, cmp_fechaFin,tcp_nombre
            FROM scp_Compromiso
            INNER JOIN cat_SubtipoClc USING( tcl_id,stc_id )
            INNER JOIN cat_TipoClc USING( tcl_id )
            INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
            INNER JOIN enc_Programatico USING( gpo_id,ur_id,epg_id )
            INNER JOIN cat_TipoCompra USING( tcp_id )
            LEFT JOIN sinpres.cat_Proveedores USING( rfc_id )
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND cmp_id= :cmp_id';
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':cmp_id' => $this->prm->cmp_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);

        if ($this->getNumeroRegistros() > 0) {
            $data = $this->extraer();
            $html .= ' <div style="padding:4px; border:1px solid #EAEAEA; min-height:40px; max-height:600px; overflow:scroll;"> Información del Compromiso número ' . $this->prm->cmp_id;
            $html .= ' <span style="color:#900;">Una vez Autorizada la Afectacion se Devenga el presupuesto y ese importe ya no es viable de usarse en otros numeros de control</span> <br/>';
            $html .= ' <table id="table_detalle_referencia">';
            $html .= ' <tr><th>Importe Total</th><th>Concepto</th><th>Proveedor</th><th>Periodo Inicio / Fin</th><th>Tipo y Subtipo </th><th>Clave Financiamiento</th><th>Encuadre programatico</th><th>Tipo de Compra</th><tr>';

            $html .= ' <td align="right"><span style="float:left">$</span>' . number_format(floatval($data['cmp_importe']), 2) . '</td>';
            $impcmp = $data['cmp_importe'];

            $html .= ' <td align="right">' . $data['cmp_concepto'] . '   </td>';
            $html .= ' <td align="right">' . $data['rfc_id'] . ' - ' . $data['rfc_nombre'] . ' ' . $data['rfc_paterno'] . ' ' . $data['rfc_materno'] . ' </td>';
            $html .= ' <td align="center">' . substr(isset($data['cmp_fechaInicio']) ? $data['cmp_fechaInicio'] : '', 0, 10) . '  ' . substr(isset($data['cmp_fechaFin']) ? $data['cmp_fechaFin'] : '', 0, 10) . '</td>';
            $html .= ' <td align="center">' . $data['tcl_nombre'] . ' - ' . $data['stc_nombre'] . '</td>';
            $html .= ' <td align="center">' . $data['cff_id'] . $data['fte_id'] . $data['sft_id'] . $data['ori_id'] . $data['sor_id'] . $data['cfi_id'] . $data['cfi_anio'] . '</td>';
            /**
             * Autor: Eduardo Venegas
             * Fecha: 10:00 am 22-11-18
             * Descripcion: En la linea a continuacion usaba $data['cso_id'] como indice, el cual no existe, por eso se quito.
             */
            $html .= ' <td align="center">' . $data['cff_id'] . $data['fte_id'] . $data['sft_id'] . $data['ori_id'] . $data['sor_id'] . $data['cfi_id'] . $data['cfi_anio'] . ' ' . $data['prg_id'] . $data['spr_id'] . $data['py_id'] . $data['oa_id'] . '</td>';
            $html .= ' <td align="center">' . $data['tcp_nombre'] . '</td></tr></table>';

            $html .= ' <br/><table id="table_detalle_referencia" style="width:100%">';
            $html .= '<tr><td colspan="3" style="background-color:#000; color:#FFF">Ajustes hechos al compromiso </td></tr>';

            $sql = '
                SELECT * FROM scp_Compromiso WHERE cmp_cmp_id = ' . $this->prm->cmp_id . ' AND ecm_id="A" AND cmp_tipoCompromiso="A";
            ';
            $this->setSql($sql);
            $this->consultar();
            if ($this->getNumeroRegistros() > 0) {
                $html .= '
                    <tr>
                        <td align="right">Importe</td>
                        <td align="center">Compromiso</td>
                        <td align="center">Tipo de Ajuste</td>
                    </tr>
                ';
                while ($data2 = $this->extraer()) {
                    $html .= '
                        <tr>
                            <td align="right"><span style="float:left">$</span>' . number_format(floatval($data2['cmp_importe']), 2) . '</td>
                            <td align="center">' . $data2['cmp_id'] . '</td>
                            <td align="center">' . ($data2['cmp_tipoMovimiento'] == '-' ? 'REDUCCIóN ' : 'AUMENTO') . '</td>
                        </tr>';
                    if ($data2['cmp_tipoMovimiento'] == '-') {
                        $importe -= $data2['cmp_importe'];
                    } else {
                        $importe += $data2['cmp_importe'];
                    }
                }
            } else {
                $html .= '<tr><td colspan="3">NO HAY AJUSTES A ESTE COMPROMISO</td></tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<div style="padding:4px; border:1px solid #EAEAEA; min-height:40px;"> La información solicitada no existe <span id="close_info_referencia">Cerrar X</span></div>';
        }
        $html .= '<div style="background-color:#000; color:#FFF; padding:5px 10px;">Numeros de control en los que se ha usado este compromiso</div><br/>';
        $html .= '
            <table id="table-referencias">
                <tr class="warning" role="row">
                    <th class="warning" role="columnheader"># Control</th>
                    <th class="warning" role="columnheader"># Afectación</th>
                    <th class="warning" role="columnheader"># CLC</th>
                    <th class="warning" role="columnheader">Fecha</th>
                    <th class="warning" role="columnheader">Concepto</th>
                    <th class="warning" role="columnheader">Importe</th>
                    <th class="warning" role="columnheader">Fecha</th>
                    <th class="warning" role="columnheader">Estado</th>
                </tr>
        ';
        $sql = '
            SELECT * FROM scp_Presupuestal
            INNER JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id)
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id  AND cmp_id= :cmp_id
            AND eaf_id !="C"
            ';
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':cmp_id' => $this->prm->cmp_id,
        ];
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $total = 0;
        if ($this->getNumeroRegistros() > 0) {
            while ($data = $this->extraer()) {
                $html .= '<tr ' . ($data['eaf_id'] == 'A' ? 'class="success" role="row"' : '') . '>';
                $html .= '<td align="center">' . $data['ncp_id'] . '</td>';
                $html .= '<td align="center">' . $data['afe_id'] . '</td>';
                $html .= '<td align="center">' . $data['clc_id'] . '</td>';
                $html .= '<td align="center">' . $data['afe_fechaElaboracion'] . '</td>';
                $html .= '<td>' . $data['ncp_concepto'] . '</td>';
                $html .= '<td align="right"><span style="float:left">$</span>' . number_format(floatval($data['ncp_percepcionNeto']), 2) . '</td>';
                $html .= '<td align="center">' . substr($data['usr_fecha'], 0, 10) . '</td>';
                $html .= '<td align="center">' . ($data['eaf_id'] == 'A' ? 'AUTORIZADA' : 'GENERADA') . '</td>';
                $html .= '</tr>';
                $total += $data['ncp_percepcionNeto'];
            }
            $importetotal = ($impcmp - $total) + $importe;
            if ($importetotal < 0) {
                $html .= '
                    <tr class="warning" rolw="row" style="color:#900">
                        <td align="right" colspan="5">Error en los registros</td><td align="right"><span style="float:left">$</span>' . number_format(floatval($total), 2) . '</td>
                        <td align="right">Por usar</td><td align="right"><span style="float:left">$</span>' . number_format((($impcmp - $total) + $importe), 2) . '</td>
                    </tr>
                ';
            } else {
                $html .= '
                    <tr class="warning" rolw="row"><td align="right" colspan="5">Total Usado</td><td align="right"><span style="float:left">$</span>' . number_format(floatval($total), 2) . '</td>
                        <td align="right">Por usar</td><td align="right"><span style="float:left">$</span>' . number_format((($impcmp - $total) + $importe), 2) . '</td>
                    </tr>
                ';
            }
        } else {
            $html .= '<tr><td colspan="7" style="text-align:center; border:1px solid #EAEAEA;">Esta compromiso no ha sido utilizada en ningun registro</td></tr></table>';
        }
        $this->mixHtml($html);
        $this->mixScript('
            $("#back_info_referencia").fadeIn("slow");
            $("#close_info_referencia, #back_info_referencia").click(function(){
                $("#back_info_referencia").fadeOut("slow");
            });
            $("#fsinfo_referencia").css("height","auto");
        ');
    }

    /**OBTIENE Y MUESTRA INFORMACION DE LA REFERENCIA */
    private function infoReferencia()
    {
        $referencia = isset($this->prm->referencia) && $this->prm->referencia != '' ? $this->prm->referencia : '';
        $proveedor = isset($this->prm->rfc_id) && $this->prm->rfc_id != '' ? $this->prm->rfc_id : '';
        $tdf_id = isset($this->prm->tdf_id) && $this->prm->tdf_id != '' ? $this->prm->tdf_id : '';
        if ($referencia == '' || $proveedor == '') {
            return false;
        }
        $docto = ValidaGarantias::gtDocumento('', $proveedor, $referencia, $tdf_id);

        /*------#  SI HAY ERROR RETORNAMOS UN EL MENSAJE   */
        if ($docto->valido == false) {
            if ($docto->error == 2) {
                $this->mixScript('notify("Existen dos documentos identicos en el Banco de Documentos, por favor use la tecla F1 para seleccionar el documento que quiere usar","warning");');
            } else {
                $this->mixScript('notify("' . $docto->mensaje . '","warning");');
            }
            return;
        }

        $this->setContenedor('det_fs' . $this->prm->rfc_id);
        $cs = 6;
        if ($docto->rectificaciones == true) {
            $cs = 9;
        }

        $html = '
            <table style="width:100%"
                class="table table-bordered table-striped">
                <tr class="success" role="row">
                    <th >Referencia </td>
                    <th >Proveedor</td>
                    <th colspan="4">Concepto</td>
                </tr>
                <tr>
                    <td><strong>' . strtoupper($referencia) . '</strong></td>
                    <td><strong>' . $docto->nombreProveedor . ' - ' . $docto->rfc_id . '</strong></td>
                    <td colspan="4">
                        <div class="ajustable">
                            <strong>' . strtoupper($docto->concepto) . '</strong>
                        </div>
                    </td>
                </tr>
                <tr class="success" role="row">
                    <th>Percepcion Presupuestal</th>
                    <th>Percepciones Contables</th>
                    <th>Retenciones Contables</th>
                    <th>Rectificaciones</th>
                    <th>Fecha del Documento</th>
                    <th>Fondo Rotatorio</th>
                </tr>
                <tr>
                    <td align="right">$' . number_format((float) $docto->importe, 2) . '</td>
                    <td align="right">$' . number_format((float) $docto->percepcionContable, 2) . ' </td>
                    <td align="right">$' . number_format((float) $docto->retencionContable, 2) . ' </td>
                    <td align="center">' . $docto->rectificaciones . ' </td>
                    <td align="center">' . $docto->fechaDocumento . '</td>
                    <td align="center">' . $docto->fondoRotatorio . '</td>
                </tr>
            </table>
            Actualmente el documento ' . ($docto->estadoSat == 1 ? 'no' : 'si') . ' esta validado ante  el SAT<br>
        ';

        /*** NUMEROS DE CONTROL EN LOS QUE SE HA USADO ESTE DOCUMENTO  CON RECTIFICACIONES **/
        if ($docto->rectificaciones == true) {
            $this->mixScript('$("#info_referencia").css("width","100%");');
        }

        $html .= '
            <div style="padding:4px;">
                <span style="font-size:16px">A continuaci&oacute;n el detalle de movimientos en los que aparece este documento</span>
                <span style="color:#333; font-size:14px;"> La columna tipo indica si es P presupuestal o C Contable</span>
            </div>
            <table id="" class="table table-bordered table-striped>
                <tr class="warning" role="row">
                    <th class="warning" role="columnheader">Tipo</th>
                    <th class="warning" role="columnheader"># Control</th>
                    <th class="warning" role="columnheader"># Afectación</th>
                    <th class="warning" role="columnheader"># CLC</th>
                    <th class="warning" role="columnheader">Fecha Autorización</th>
                    <th class="warning" role="columnheader">Concepto</th>
                    <th class="warning" role="columnheader">Importe</th>
                    <th class="warning" role="columnheader">Retenciones</th>
                    <th class="warning" role="columnheader">Percepciones</th>
                    ';
        if ($docto->rectificaciones == true) { #data
            $html .= '
            <th class="warning" role="columnheader">$ Rectificado Pres.</th>
            <th class="warning" role="columnheader">$ Percepcion Rect.    </th>
            <th class="warning" role="columnheader">$ Retencion Rect.     </th>';
        }
        $html .= ' <th class="warning" role="columnheader">Fecha</th></tr>';

        if (count($docto->detalleDevengado) > 0) {
            foreach ($docto->detalleDevengado as $value) {
                $html .= '<tr ' . ($value->estado == 'A' ? 'class="success" role="row"' : '') . '>';
                $html .= '
                <td align="center">' . $value->tipo . '</td>
                <td align="center">' . $value->num_id . '</td>
                <td align="center">' . $value->afe_id . '</td>
                <td align="center">' . $value->clc_id . '</td>
                <td align="center"></td>
                <td>' . $value->concepto . '</td>
                <td align="right"><span style="float:left;">$</span>' . number_format(floatval($value->importe), 2) . '</td>
                <td align="right"><span style="float:left;">$</span>' . number_format(floatval($value->Percepcion), 2) . '</td>
                <td align="right"><span style="float:left;">$</span>' . number_format(floatval($value->Retencion), 2) . '</td>
                ';
                if ($docto->rectificaciones == true) {
                    $html .= '<td align="right"><span style="float:left;">$</span>' .
                        number_format(floatval($value->Rectificado), 2) . '</td>';
                    $html .= '<td align="right"><span style="float:left;">$</span>' .
                        number_format(floatval($value->PercepcionR), 2) . '</td>';
                    $html .= '<td align="right"><span style="float:left;">$</span>' .
                        number_format(floatval($value->RetencionR), 2) . '</td>';
                }
                $html .= '<td align="right">' . $value->fechaCreacion . '</td>';
                $html .= '</tr>';
            }
            $html .= '
                <tr style="background-color:#EAEAEA !important"><td colspan="' . $cs . '">&nbsp;</td><td>&nbsp;</td>
                    <td align="center">Presupuestal</td>
                    <td align="center">Percepciones</td>
                    <td align="center">Retenciones</td>
                </tr>
                <tr>
                    <td colspan="' . $cs . '">&nbsp;</td><td>Total Referenciado</td>
                    <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->referenciado), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->refPer), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->refRet), 2) . '</td>
                </tr>';
            $html .= '<tr><td colspan="' . $cs . '">&nbsp;</td><td>Total Devengado</td>
                    <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->devengado), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->perceD), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->retenD), 2) . '</td>
                    <tr/>
                    ';
            if ($docto->rectificaciones == true) {
                $html .= '
                    <tr>
                        <td colspan="' . $cs . '">&nbsp;</td><td>Total Rectificado</td>
                        <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->importeRectificado), 2) . '</td>
                        <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->importePerR), 2) . '</td>
                        <td align="right"><span style="float:left">$</span>' . number_format(floatval($docto->importeRetR), 2) . '</td>
                    </tr>
                ';
            }
            $html .= '
                <tr class="success" role="row"><td colspan="' . $cs . '">&nbsp;</td>
                    <td>Total Disponible a usar</td>
                    <td align="right"><span style="float:left">$</span>' . number_format((($docto->importe + $docto->importeRectificado) - $docto->referenciado), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format((($docto->percepcionContable + $docto->importePerR) - $docto->refPer), 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format((($docto->retencionContable + $docto->importeRetR) - $docto->refRet), 2) . '</td>
                </tr>';
        } else {
            $html .= '<tr><td colspan="' . $cs . '" style="text-align:center; border:1px solid #EAEAEA;">Esta Referencia no ha sido utilizada en ningun registro</td></tr></table>';
        }
        $this->mixHtml($html);
        $this->mixScript('
            $("#back_info_referencia").fadeIn("slow");
            $("#back_info_referencia").click(function(e){
                if ( e.target ==$(this) )
                    $("#back_info_referencia").animate({opacity:0},300,function(){ $("#back_info_referencia").remove();
                });
            });
            $("#fs' . strtoupper($this->prm->rfc_id) . '").css("height","auto");
        ');
        if (isset($this->prm->tipo) && $this->prm->tipo = 'warning') {
            $this->mixScript('
                $("#NumReferencia").val("");
            ');
        }
    }

    private function presupuesto($data = null)
    {
        //CALCULA EL PRESUPUESTO TOTAL A DEVENGAR
        if ($data == null) {
            $data = new stdClass();
            $data->ecf_id = $this->prm->ecf_id;
            $data->epg_id = $this->prm->epg_id;
            $data->tipo = $this->prm->tipo;
            $data->mes = (date('Y') > APP_EJERCICIO) ? 12 : $this->prm->mes;

            if (isset($this->prm->pg_id)) {
                $data->pg_id = $this->prm->pg_id;
            }
            if (isset($this->prm->pe_id)) {
                $data->pe_id = $this->prm->pe_id;
            }
            if (isset($this->prm->tcp_id)) {
                $data->tcp_id = $this->prm->tcp_id;
            }
            $data->ue_id = $this->prm->ue_id;
            $data->cmp_id = $this->prm->cmp_id;
            $data->grafica = true;
        }

        if ($data->epg_id == '' || $data->epg_id == 0) {
            $this->mixScript(' notify("Error en la afectación, no tiene encuadre programatico ( obra )","error");');
            return false;
        }
        if ($data->ecf_id == '' || $data->ecf_id == 0) {
            $this->mixScript(' notify("Error en la afectación, no tiene encuadre programatico ( obra )","error");');
            return false;
        }

        $datareturn = new stdClass();
        $datareturn->enafectacion = 0;
        if (isset($this->prm->importe)) {
            $datareturn->importe = $this->prm->importe;
        }

        $ade = null;
        $adecuacion = null;

        $trimestre = $this->model->getTrimestre($data->mes);
        switch ($data->tipo) {
            case 'cve':
                $presupuesto = $this->model->presupuestoClaveFin(['ecf_id' => $data->ecf_id]);
                $datareturn->registros = $presupuesto['registros'];
                $presupuesto = $presupuesto['datos'][0];

                $presCveUsado = $this->model->importeAcuerdos(['ecf_id' => $data->ecf_id]);
                $datareturn->Anticipado = $presCveUsado['TOTAL'];
                $datareturn->clavefin = ((isset($presupuesto['prg_id']) ? $presupuesto['prg_id'] : '') . (isset($presupuesto['spr_id']) ? $presupuesto['spr_id'] : '') .
                    (isset($presupuesto['py_id']) ? $presupuesto['py_id'] : '') . (isset($presupuesto['oa_id']) ? $presupuesto['oa_id'] : '') . ' - ' . (isset($presupuesto['oa_nombre']) ? $presupuesto['oa_nombre'] : ''));
                $mensaje = 'PRESUPUESTO PARA LA CLAVE DE FINANCIAMIENTO ' . (isset($presupuesto['cfi_clave']) ? $presupuesto['cfi_clave'] : '') . ' - ' . (isset($presupuesto['cfi_nombre']) ? $presupuesto['cfi_nombre'] : '');
                break;
            case 'anticipo':
                //PRESUPUESTO DE LA OBRA
                $presupuesto = $this->model->presupuestoObra(['ecf_id' => $data->ecf_id, 'epg_id' => $data->epg_id]);
                $datareturn->registros = $presupuesto['registros'];
                $presupuesto = $presupuesto['datos'][0];
                // ANTICIPOS SOLICITADOS
                $anticipoObraSolicitado = $this->model->anticipoObraSolicitado(['ecf_id' => $data->ecf_id, 'epg_id' => $data->epg_id]);
                // anticipo fondo rotatorio
                $anticipoObraGenerado = $this->model->anticipoObraGenerado(['epg_id' => $data->epg_id]);
                // rectificaciones de anticipos
                $anticipoObraRectificado = $this->model->anticipoObraRectificado(['ecf_id' => $data->ecf_id, 'epg_id' => $data->epg_id]);
                $datareturn->enafedetalle = new stdClass();
                foreach ($anticipoObraGenerado as $afe) {
                    $datareturn->enafectacion += isset($afe['total']) ? $afe['total'] : 0;
                    $datareturn->enafedetalle->{isset($afe['afe_id']) ? $afe['afe_id'] : 0} = isset($afe['total']) ? $afe['total'] : 0;
                }

                $datareturn->Anticipado = round((float) (isset($anticipoObraSolicitado['TOTAL']) ? $anticipoObraSolicitado['TOTAL'] : 0) - (isset($anticipoObraRectificado['rectificacion']) ? $anticipoObraRectificado['rectificacion'] : 0), 0);
                $mensaje = 'PRESUPUESTO PARA LA OBRA:' . $presupuesto['prg_id'] . '-' . $presupuesto['spr_id'] . '-' . $presupuesto['py_id']
                    . ' - ' . $presupuesto['oa_id'] . ' - ' . $presupuesto['oa_nombre'];
                break;
            case 'presupuestal':
                $mes1 = $trimestre->mes1;
                $mes2 = $trimestre->mes2;
                $mes3 = $trimestre->mes3;
                $presupuesto = $this->model->presupuestoClave($data, $trimestre);
                $datareturn->registros = $presupuesto['registros'];
                $presupuesto = $presupuesto['datos'][0];
                $mensaje = 'PRESUPUESTO PARA EL TRIMESTRE ' . $trimestre->tri;
                $preGenerado = $this->model->presupuestoGenerado($data);
                $datareturn->enafedetalle = new stdClass();
                /*******************    PRESUPUESTO EN AFECTACIONES NO AUTORIZADAS    ********************/
                if (!empty($preGenerado) && $preGenerado['registros'] > 0) {
                    foreach ($preGenerado['datos'] as $afe) {
                        $datareturn->enafectacion += $afe['Importe'] == '' ? 0 : $afe['Importe'];
                        $datareturn->enafedetalle->{$afe['afe_id']} = $afe['Importe'];
                    }
                }
                $datareturn->clave = (isset($presupuesto['gpo_id']) ? $presupuesto['gpo_id'] : '') . (isset($presupuesto['ur_id']) ? $presupuesto['ur_id'] : '') . (isset($presupuesto['ue_id']) ? $presupuesto['ue_id'] : '') . '-'
                    . (isset($presupuesto['prg_id']) ? $presupuesto['prg_id'] : '') . (isset($presupuesto['spr_id']) ? $presupuesto['spr_id'] : '') . (isset($presupuesto['py_id']) ? $presupuesto['py_id'] : '') . (isset($presupuesto['oa_id']) ? $presupuesto['oa_id'] : '') . '-'
                    . (isset($presupuesto['pg_id']) ? $presupuesto['pg_id'] : '') . (isset($presupuesto['pe_id']) ? $presupuesto['pe_id'] : '') . '-'
                    . (isset($presupuesto['cff_id']) ? $presupuesto['cff_id'] : '') . (isset($presupuesto['fte_id']) ? $presupuesto['fte_id'] : '') . (isset($presupuesto['sft_id']) ? $presupuesto['sft_id'] : '') . (isset($presupuesto['ori_id']) ? $presupuesto['ori_id'] : '') . (isset($presupuesto['sor_id']) ? $presupuesto['sor_id'] : '')
                    . (isset($presupuesto['cfi_id']) ? $presupuesto['cfi_id'] : '') . (isset($presupuesto['cfi_anio']) ? $presupuesto['cfi_anio'] : '') . '-' . (isset($presupuesto['tcp_id']) ? $presupuesto['tcp_id'] : '');
                /*VIGENTE DEL trimestre */
                $datareturn->vig1 = (isset($presupuesto['vig_' . $mes1]) ? $presupuesto['vig_' . $mes1] : 0);
                $datareturn->vig2 = (isset($presupuesto['vig_' . $mes2]) ? $presupuesto['vig_' . $mes2] : 0);
                $datareturn->vig3 = (isset($presupuesto['vig_' . $mes3]) ? $presupuesto['vig_' . $mes3] : 0);

                /*DEVENGADO DEL BIMESTRE */
                $datareturn->dev1 = (isset($presupuesto['dev_' . $mes1]) ? $presupuesto['dev_' . $mes1] : 0);
                $datareturn->dev2 = (isset($presupuesto['dev_' . $mes2]) ? $presupuesto['dev_' . $mes2] : 0);
                $datareturn->dev3 = (isset($presupuesto['dev_' . $mes3]) ? $presupuesto['dev_' . $mes3] : 0);

                /*EJERCIDO DEL BIMESTRE */
                $datareturn->eje1 = (isset($presupuesto['eje_' . $mes1]) ? $presupuesto['eje_' . $mes1] : 0);
                $datareturn->eje2 = (isset($presupuesto['eje_' . $mes2]) ? $presupuesto['eje_' . $mes2] : 0);
                $datareturn->eje3 = (isset($presupuesto['eje_' . $mes3]) ? $presupuesto['eje_' . $mes3] : 0);

                /*BIMESTRE   */
                $datareturn->bim = $trimestre->tri;
                $datareturn->ModificadoAnual = (isset($presupuesto['ModificadoAnual']) ? $presupuesto['ModificadoAnual'] : 0);
                $presupuesto['pre_mov'] = isset($presupuesto['pre_mov']) ? $presupuesto['pre_mov'] : '';
                if ($presupuesto['pre_mov'] != '') {
                    $sqlad = '
                        SELECT (dmo_' . $mes1 . ' + dmo_' . $mes2 . ' + dmo_' . $mes3 . ') as Adecuacion ,mov_id
                        FROM scp_MaestroModificacion
                        INNER JOIN scp_DetalleModificacion USING(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND pg_id = :pg_id AND pe_id = :pe_id AND epg_id = :epg_id AND ecf_id = :ecf_id
                        AND tcp_id =:tcp_id AND ue_id = :ue_id AND emo_id NOT IN(14,15,16)
                        ';
                    $params = [
                        ':gpo_id' => $this->gpo_id,
                        ':ur_id'  => $this->ur_id,
                        ':pg_id'  => $data->pg_id,
                        ':pe_id'  => $data->pe_id,
                        ':epg_id' => $data->epg_id,
                        ':ecf_id' => $data->ecf_id,
                        ':tcp_id' => $data->tcp_id,
                        ':ue_id'  => $data->ue_id,
                    ];
                    $dt = $this->model->getDatos($sqlad, false, $params);
                    $adecuacion = $dt['datos'][0];
                }

                // Valide que existiera
                if (isset($presupuesto['pre_mov'])) {
                    if (strtoupper($presupuesto['pre_mov']) == 'C') {
                        if ($presupuesto['Modificado'] >= $adecuacion['Adecuacion']) {
                            $ade = $presupuesto['Modificado'] - $adecuacion['Adecuacion'];
                        } else {
                            $ade = 0;
                        }

                        ///Esta es
                    } elseif (strtoupper($presupuesto['pre_mov']) == 'R') {
                        $ade = $adecuacion['Adecuacion'];
                    }
                }
                $datareturn->Movimiento = $presupuesto['pre_mov'];
                $mensaje = ' PRESUPUESTO PARA EL TRIMESTRE ' . $datareturn->bim;
                break;
        }
        /*  TOTALES   **/
        $datareturn->enafectacion = 0;
        $datareturn->Mensaje = $mensaje;
        $datareturn->Modificado = (isset($presupuesto['Modificado']) ? $presupuesto['Modificado'] : 0);
        $datareturn->Comprometido = (isset($presupuesto['Comprometido']) ? $presupuesto['Comprometido'] : 0);
        $datareturn->Devengado = (isset($presupuesto['Devengado']) ? $presupuesto['Devengado'] : 0);
        $datareturn->Ejercido = (isset($presupuesto['Ejercido']) ? $presupuesto['Ejercido'] : 0);
        $datareturn->Diferencia = (($datareturn->Modificado - $ade) - ($datareturn->Devengado + $datareturn->enafectacion));
        $datareturn->Adecuacion = $ade;
        $datareturn->cmp_id = isset($data->cmp_id) ? $data->cmp_id : null;

        /*DETALLE VIGENTE ANUAL   */
        $datareturn->vigdet = new stdClass();
        $datareturn->vigdet->ene = (isset($presupuesto['vig_ene']) ? $presupuesto['vig_ene'] : 0);
        $datareturn->vigdet->feb = (isset($presupuesto['vig_feb']) ? $presupuesto['vig_feb'] : 0);
        $datareturn->vigdet->mar = (isset($presupuesto['vig_mar']) ? $presupuesto['vig_mar'] : 0);
        $datareturn->vigdet->abr = (isset($presupuesto['vig_abr']) ? $presupuesto['vig_abr'] : 0);
        $datareturn->vigdet->may = (isset($presupuesto['vig_may']) ? $presupuesto['vig_may'] : 0);
        $datareturn->vigdet->jun = (isset($presupuesto['vig_jun']) ? $presupuesto['vig_jun'] : 0);
        $datareturn->vigdet->jul = (isset($presupuesto['vig_jul']) ? $presupuesto['vig_jul'] : 0);
        $datareturn->vigdet->ago = (isset($presupuesto['vig_ago']) ? $presupuesto['vig_ago'] : 0);
        $datareturn->vigdet->sep = (isset($presupuesto['vig_sep']) ? $presupuesto['vig_sep'] : 0);
        $datareturn->vigdet->oct = (isset($presupuesto['vig_oct']) ? $presupuesto['vig_oct'] : 0);
        $datareturn->vigdet->nov = (isset($presupuesto['vig_nov']) ? $presupuesto['vig_nov'] : 0);
        $datareturn->vigdet->dic = (isset($presupuesto['vig_dic']) ? $presupuesto['vig_dic'] : 0);

        /* DETALLE COMPROMETIDO */
        $datareturn->comdet = new stdClass();
        $datareturn->comdet->ene = (isset($presupuesto['com_ene']) ? $presupuesto['com_ene'] : 0);
        $datareturn->comdet->feb = (isset($presupuesto['com_feb']) ? $presupuesto['com_feb'] : 0);
        $datareturn->comdet->mar = (isset($presupuesto['com_mar']) ? $presupuesto['com_mar'] : 0);
        $datareturn->comdet->abr = (isset($presupuesto['com_abr']) ? $presupuesto['com_abr'] : 0);
        $datareturn->comdet->may = (isset($presupuesto['com_may']) ? $presupuesto['com_may'] : 0);
        $datareturn->comdet->jun = (isset($presupuesto['com_jun']) ? $presupuesto['com_jun'] : 0);
        $datareturn->comdet->jul = (isset($presupuesto['com_jul']) ? $presupuesto['com_jul'] : 0);
        $datareturn->comdet->ago = (isset($presupuesto['com_ago']) ? $presupuesto['com_ago'] : 0);
        $datareturn->comdet->sep = (isset($presupuesto['com_sep']) ? $presupuesto['com_sep'] : 0);
        $datareturn->comdet->oct = (isset($presupuesto['com_oct']) ? $presupuesto['com_oct'] : 0);
        $datareturn->comdet->nov = (isset($presupuesto['com_nov']) ? $presupuesto['com_nov'] : 0);
        $datareturn->comdet->dic = (isset($presupuesto['com_dic']) ? $presupuesto['com_dic'] : 0);

        $datareturn->devdet = new stdClass();
        $datareturn->devdet->ene = (isset($presupuesto['dev_ene']) ? $presupuesto['dev_ene'] : 0);
        $datareturn->devdet->feb = (isset($presupuesto['dev_feb']) ? $presupuesto['dev_feb'] : 0);
        $datareturn->devdet->mar = (isset($presupuesto['dev_mar']) ? $presupuesto['dev_mar'] : 0);
        $datareturn->devdet->abr = (isset($presupuesto['dev_abr']) ? $presupuesto['dev_abr'] : 0);
        $datareturn->devdet->may = (isset($presupuesto['dev_may']) ? $presupuesto['dev_may'] : 0);
        $datareturn->devdet->jun = (isset($presupuesto['dev_jun']) ? $presupuesto['dev_jun'] : 0);
        $datareturn->devdet->jul = (isset($presupuesto['dev_jul']) ? $presupuesto['dev_jul'] : 0);
        $datareturn->devdet->ago = (isset($presupuesto['dev_ago']) ? $presupuesto['dev_ago'] : 0);
        $datareturn->devdet->sep = (isset($presupuesto['dev_sep']) ? $presupuesto['dev_sep'] : 0);
        $datareturn->devdet->oct = (isset($presupuesto['dev_oct']) ? $presupuesto['dev_oct'] : 0);
        $datareturn->devdet->nov = (isset($presupuesto['dev_nov']) ? $presupuesto['dev_nov'] : 0);
        $datareturn->devdet->dic = (isset($presupuesto['dev_dic']) ? $presupuesto['dev_dic'] : 0);

        $datareturn->ejedet = new stdClass();
        $datareturn->ejedet->ene = (isset($presupuesto['eje_ene']) ? $presupuesto['eje_ene'] : 0);
        $datareturn->ejedet->feb = (isset($presupuesto['eje_feb']) ? $presupuesto['eje_feb'] : 0);
        $datareturn->ejedet->mar = (isset($presupuesto['eje_mar']) ? $presupuesto['eje_mar'] : 0);
        $datareturn->ejedet->abr = (isset($presupuesto['eje_abr']) ? $presupuesto['eje_abr'] : 0);
        $datareturn->ejedet->may = (isset($presupuesto['eje_may']) ? $presupuesto['eje_may'] : 0);
        $datareturn->ejedet->jun = (isset($presupuesto['eje_jun']) ? $presupuesto['eje_jun'] : 0);
        $datareturn->ejedet->jul = (isset($presupuesto['eje_jul']) ? $presupuesto['eje_jul'] : 0);
        $datareturn->ejedet->ago = (isset($presupuesto['eje_ago']) ? $presupuesto['eje_ago'] : 0);
        $datareturn->ejedet->sep = (isset($presupuesto['eje_sep']) ? $presupuesto['eje_sep'] : 0);
        $datareturn->ejedet->oct = (isset($presupuesto['eje_oct']) ? $presupuesto['eje_oct'] : 0);
        $datareturn->ejedet->nov = (isset($presupuesto['eje_nov']) ? $presupuesto['eje_nov'] : 0);
        $datareturn->ejedet->dic = (isset($presupuesto['eje_dic']) ? $presupuesto['eje_dic'] : 0);

        if (isset($this->prm->importe)) {
            $datareturn->importe = $this->prm->importe;
        }

        if (isset($data->grafica) && $data->grafica == true) {
            $datareturn->tipo = $data->tipo;
            $this->graficaPresupuesto($datareturn);
        } else {
            return $datareturn;
        }
    }

    /**MUESTRA LA GRAFICA DEL PRESUPUESTO ACTUAL REGRESA EL IMPORTE POR COMPROMETER*/
    private function graficaPresupuesto($data)
    {
        $this->setContenedor('graphic');
        $extra1 = ''; /// PARA LA GRAFICA PRINCIPAL IMPORTES
        $extra2 = ''; /// PARA EL DETALLE DE CADA COLUMNA  IMPORTES POR MES
        $mesesa = ['ene' => 'ENERO', 'feb' => 'FEBRERO', 'mar' => 'MARZO', 'abr' => 'ABRIL', 'may' => 'MAYO', 'jun' => 'JUNIO', 'jul' => 'JULIO', 'ago' => 'AGOSTO', 'sep' => 'SEPTIEMBRE', 'oct' => 'OCTUBRE', 'nov' => 'NOVIEMBRE', 'dic' => 'DICIEMBRE'];
        $data->bim = isset($data->bim) ? $data->bim : '';
        $meses = explode('-', $data->bim);
        $pres = '';
        $porcom = 0;

        if (isset($data->importe)) {
            $data->Diferencia = $data->Diferencia + $data->importe;
        }
        if ($data->cmp_id == 0 && $data->tipo == 'presupuestal') {
            if ($data->Diferencia > ($data->ModificadoAnual - $data->Comprometido)) {
                $porcom = ($data->ModificadoAnual - $data->Comprometido);
            }
        }
        switch ($data->tipo) {
            case 'presupuestal':
                $porcom = $data->Diferencia + 0;
                $pres = '
                    <table id="detalle_grafica" style="display:none; color:#090">
                        <tr>
                            <td>Modificado :  $ ' . number_format(floatval($data->Modificado), 2) . ' </td>
                            <td>Devengado $' . number_format(floatval($data->Devengado), 2) . ' </td>
                            <td>En adecuación  $ ' . number_format(floatval($data->Adecuacion), 2) . '</td>
                            <td>Por Devengar  $ ' . number_format(floatval($porcom), 2) . '</td>
                        </tr>
                    </table>';
                break;
            case 'anticipo':
            case 'cve':
                $poranticipar = round((float) $data->Modificado - ($data->Devengado + $data->Anticipado), 2) + 0;
                $pres = '
                    <table id="detalle_grafica" style="display:none; color:#090">
                        <tr>
                            <td>Modificado : $ ' . number_format(floatval($data->Modificado), 2) . ' </td>
                            <td>Devengado $' . number_format(floatval($data->Devengado), 2) . ' </td>
                            <td>Anticipado $ ' . number_format(floatval($data->Anticipado), 2) . '</td>
                            <td>Por Anticipar $ ' . number_format(floatval($poranticipar), 2) . '</td>
                        </tr>
                    </table>';
                break;
        }

        if ($data->Diferencia == 0) {
            $this->mixScript('$("#btn_guarda").attr("disabled","1");');
        } else {
            $this->mixScript('$("#btn_guarda").removeAttr("disabled");');
        }
        switch ($data->tipo) {
            case 'presupuestal':
                $que = ' EL TRIMESTRE ';
                break;
            case 'cve':
                $que = ' LA CLAVE DE FINANCIAMIENTO ';
                break;
            case 'anticipo':
                $que = ' LA OBRA ';
                break;
        }

        $html = '
            <table width="100%"><tr><td>
                <span id="mensajegra" style="font-size:14px;">' . ($data->registros == 0 ? ' - NO HAY PRESUPUESTO PARA ' . $que . ' - ' : strtoupper($data->Mensaje)) . '</span>' . $pres . '
            </td><td width="250">';

        if ($data->registros != 0) {
            $html .= '<span class="cerrarGrafica" id="cerrarGrafica" style="cursor:pointer;">Minimizar Gr&aacute;fica</span>
            <span class="cerrarGrafica" id="nextGraphic" style="cursor:pointer;">Siguiente Gr&aacute;fica</span>';
        }

        $html .= '</td></tr>';

        switch ($data->tipo) {
            case 'presupuestal':
                $html .= '<tr><td colspan="2" style="text-align:center; font-size:14px;">';
                $html .= 'CLAVE PRESUPUESTAL:' . $data->clave;
                $html .= '</td></tr>';
                break;
        }

        /* ----------- SE PONEN LOS IMPORTES DISPONIBLES POR DEVENGAR / ANTICIPAR PARA LAS VALIDACIONES ASI COMO EL CONTENEDOR DE LA GRAFICA   -------- */
        $html .= '
            <tr><td colspan="2">
            <div align="center" id="grafica"';
        switch ($data->tipo) {
            case 'presupuestal':
                $html .= ' porcom="' . (round((float) $data->ModificadoAnual - $data->Comprometido, 2) + 0) . '" pordev="' . ($data->Diferencia + 0) . '" ';
                break;
            case 'anticipo':
                $html .= ' porant="' . (round((float) $poranticipar, 2)) . '"';
                break;
            case 'cve':
                $html .= ' porant="' . (round((float) $poranticipar, 2)) . '"';
                break;
        }
        $html .= 'name="grafica" style="height:350px; width:auto; display:block">
            </div>
            <div align="center" id="grafica2" style="display:none">
            </div>
            </td>
            </tr>
            </table>';
        $this->mixHtml($html);

        /* ------------ DETALLE DE LOS IMPORTES PARA LA GRAFICA ( ARMADO )----------------------- */
        switch ($data->tipo) {
            case 'presupuestal':
                $afesd = '';
                if ($data->enafectacion > 0) {
                    foreach ($data->enafectacion as $afe => $valor) {
                        $afesd .= "[ 'Afectacion:" . $afe . "', " . $valor . ' ],';
                    }
                }

                if ($data->cmp_id == 0) {
                    $mod = '';
                    $com = '';
                    $extra1 = "
                        { name:'Presupuesto Anual', y: " . ($data->ModificadoAnual + 0) . ",drilldown: 'Presupuesto Anual'  },
                        { name:'Comprometido Anual',y: " . ($data->Comprometido + 0) . ",drilldown: 'Comprometido Anual' },
                        { name:'Por Comprometer',   y: " . ($data->ModificadoAnual - $data->Comprometido + 0) . ',drilldown:null  },';

                    foreach ($data->vigdet as $mes => $valor) {
                        $mod .= " [    '" . $mesesa[$mes] . "'  , " . $valor . ' ],';
                    }
                    foreach ($data->comdet as $mes => $valor) {
                        $com .= " [    '" . $mesesa[$mes] . "'  , " . $valor . ' ],';
                    }

                    $extra2 = "
                        {    name: 'Presupuesto Anual',id: 'Presupuesto Anual',        data: [ " . $mod . " ]    },
                        {    name: 'Comprometido Anual',id: 'Comprometido Anual',    data: [ " . $com . ' ]  },';
                }
                $data->enafectacion = 0;
                $extra1 .= "
                    { name: 'Modificado', y: " . ($data->Modificado + 0) . ", drilldown: 'Modificado' },
                    { name: 'Devengado', y: " . ($data->Devengado + 0) . ", drilldown: 'Devengado' },
                    " . ($data->Adecuacion == 0 ? '' : "{ name: 'En Adecuacion',    y: " . ($data->Adecuacion + 0) . ', drilldown: null}, ') . '
                    ' . ($data->enafectacion == 0 ? '' : "{ name: 'Afectaciones sin Devengar', y: " . ($data->enafectacion + 0) . ",drilldown: 'Afectaciones sin Devengar'},")
                    . "{name: 'Por Devengar', y: " . ($data->Diferencia + 0) . ', drilldown: ' . ($data->Adecuacion == 0 && $data->enafectacion == 0 ? "'Por Devengar'" : 'null') . " },
                    {name: 'Ejercido', y: " . ($data->Ejercido + 0) . ", drilldown: 'Ejercido'}";

                $extra2 .= "
                    { name: 'Modificado',id: 'Modificado',data: [
                        ['" . $meses[0] . "' , " . ($data->vig1 + 0) . " ],
                        ['" . $meses[1] . "' , " . ($data->vig2 + 0) . " ],
                        ['" . $meses[2] . "' , " . ($data->vig3 + 0) . " ]]
                    },{ name: 'Devengado', id: 'Devengado', data: [
                        ['" . $meses[0] . "' , " . ($data->dev1 + 0) . " ],
                        ['" . $meses[1] . "' , " . ($data->dev2 + 0) . " ],
                        ['" . $meses[2] . "' , " . ($data->dev3 + 0) . " ]]
                    },{ name: 'Por Devengar', id: 'Por Devengar', data: [
                        ['" . $meses[0] . "' , " . ($data->vig1 - $data->dev1 + 0) . "],
                        ['" . $meses[1] . "' , " . ($data->vig2 - $data->dev2 + 0) . "],
                        ['" . $meses[2] . "' , " . ($data->vig3 - $data->dev3 + 0) . "]]
                    },{ name: 'Afectaciones sin Devengar', id: 'Afectaciones sin Devengar',
                        data: [" . $afesd . "]
                    },{ name: 'Ejercido', id: 'Ejercido',
                        data: [ [ '" . $meses[0] . "' , " . ($data->eje1 + 0) . " ],
                            [ '" . $meses[1] . "' , " . ($data->eje2 + 0) . " ],
                            [ '" . $meses[2] . "' , " . ($data->eje3 + 0) . ' ],]}';

                $porcom = isset($data->Diferencia) ? $data->Diferencia : 0;
                $porcom += isset($data->importe) ? $data->importe : 0;
                break;
            case 'anticipo':
            case 'cve':
                $afesd = '';
                $data->enafedetalle = isset($data->enafedetalle) ? $data->enafedetalle : [];
                foreach ($data->enafedetalle as $afe => $valor) {
                    if ($afesd == '') {
                        $afesd .= "[ 'Afectacion:" . $afe . "', " . $valor . ' ]';
                    } else {
                        $afesd .= ",[ 'Afectacion:" . $afe . "', " . $valor . ' ]';
                    }
                }
                $poranticipar = round((float) $data->Modificado - ($data->Devengado + $data->Anticipado + $data->enafectacion), 2) + 0;
                $mod = '';
                $dev = '';
                $eje = '';
                $porcom = $poranticipar;

                foreach ($data->vigdet as $mes => $valor) {
                    if ($mod == '') {
                        $mod .= " ['" . $mesesa[$mes] . "' , " . $valor . ' ]';
                    } else {
                        $mod .= ",['" . $mesesa[$mes] . "' , " . $valor . ' ]';
                    }
                }
                foreach ($data->devdet as $mes => $valor) {
                    if ($dev == '') {
                        $dev .= " ['" . $mesesa[$mes] . "' , " . $valor . ' ]';
                    } else {
                        $dev .= ",['" . $mesesa[$mes] . "' , " . $valor . ' ]';
                    }
                }
                foreach ($data->ejedet as $mes => $valor) {
                    if ($eje == '') {
                        $eje .= " ['" . $mesesa[$mes] . "' , " . $valor . ' ]';
                    } else {
                        $eje .= ",['" . $mesesa[$mes] . "' , " . $valor . ' ]';
                    }
                }
                $extra1 .= "
                    { name: 'Modificado', y: " . ($data->Modificado + 0) . ", drilldown: 'Modificado' },
                    { name: 'Devengado', y: " . ($data->Devengado + 0) . ", drilldown: 'Devengado' },
                    { name: 'Anticipado', y: " . ($data->Anticipado + 0) . ', drilldown: null },
                    ' . ($data->enafectacion == 0 ? '' : "{ name: 'Afectaciones sin Devengar', y: " . ($data->enafectacion + 0) . ",drilldown: 'Afectaciones sin Devengar'},") . "
                    { name: 'Por Anticipar', y: " . ($poranticipar) . ", drilldown: null },
                    { name: 'Ejercido', y: " . ($data->Ejercido + 0) . ", drilldown: 'Ejercido'}";
                $extra2 .= "
                    { name: 'Modificado', id: 'Modificado', data: [" . $mod . "]},
                    { name: 'Devengado' , id: 'Devengado' , data: [" . $dev . "]},
                    { name: 'Ejercido'  , id: 'Ejercido' , data: [" . $dev . "]},
                    { name: 'Afectaciones sin Devengar', id: 'Afectaciones sin Devengar', data: [" . $afesd . ']},';
                break;
        }
        if ($data->registros == 0) {
            $this->mixScript('
                $("#grafica").html("");
                $("#grafica2").html("");
                ');
            $porcom = 0;
        } else {
            $scri = $this->vista->pintaGrafica1($extra1, $extra2);
            $grafica2 = $this->vista->pintaGrafica2($data->vigdet, $data->devdet, $data->comdet);

            $this->mixScript($scri);
            $this->mixScript($grafica2);
        }
        $this->mixScript('
            $("#nextGraphic").click( function(){
                $("#grafica").slideToggle("slow");
                $("#grafica2").slideToggle("slow");
            });

            $("#cerrarGrafica").click(function(){
                if(parseInt($("#graphic").css("height"))<329){
                    $("#graphic").css("height","460px");
                    $(this).html("Minimizar Gráfica");
                    $("#detalle_grafica").fadeOut();
                    $("#mensajegra").show();
                }else{
                    $("#graphic").css("height","70px");
                    $(this).html("Maximizar Gráfica");
                    $("#detalle_grafica").fadeIn();
                    $("#mensajegra").hide();
                    $("#detalle_grafica").css("display","inline-block");
                }
            });');

        if ($data->registros == 0) {
            $this->mixScript('
                $("#btn_guarda").attr("disabled","disabled");
                $("#graphic").css("height","50px");
                $("#cerrarGrafica").html("Maximizar Gráfica");
                $("#porcom").val("' . round((float) $porcom, 2) . '");
                $("#grafica").attr("devengar","' . round((float) $porcom, 2) . '");');
        } else {
            $this->mixScript('
                $("#porcom").val("' . round((float) $porcom + 0, 2) . '");
                $("#btn_guarda").removeAttr("disabled");
                $("#graphic").css("height","460px");
                $("#cerrarGrafica").html("Minimizar Gráfica");');
        }
        if ($data->tipo == 'presupuestal') {
            $this->mixScript('    importes2(); ');
        }
    }

    /**INFORMACION SOBRE LA OBRA A AMORTIZAR */
    private function AmortizaObra()
    {
        $html = '';
        if (!isset($this->prm->obra_id)) {
            $this->mixScript('$("#coo_id").change(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"AmortizaObra",
                        obra_id:$(this).find("option:selected").val(),
                        ecf_id:"' . $this->prm->ecf_id . '",
                        epg_id:"' . $this->prm->epg_id . '"
                        ' . (isset($this->prm->importe) ? ',
                        importe:"' . $this->prm->importe . '"' : '') . '
                        ' . (isset($this->prm->ncc_id) ? ',
                        ncc_id:"' . $this->prm->ncc_id . '"' : '') . '
                    })
            });');
            $this->setContenedor('contenedorpagadora');
            $html .= '<div id"obraGeneral" style="padding:10px; border:1px dotted #666; border-radius:4px; width:100%">';
            $comboObra = new stdClass();
            $comboObra->id = 'coo_id';
            $comboObra->name = 'control';
            $comboObra->enDiv = true;
            $comboObra->camposVal = 'coo_id';
            $comboObra->agrupaPor = 'coo_id';
            $comboObra->campoLabelAgrupacion = 'coo_id';
            $comboObra->comoAtr = 'coo_id';
            $comboObra->mensaje = 'Selecciona';
            $comboObra->label = 'Obras Viables de Amortizar';
            $comboObra->requerido = true;
            $comboObra->sql = '
                SELECT epg_id AS coo_id, CONCAT("OBRA:", oa_id, " ", oa_nombre) AS oa_nombre
                FROM cat_ObraActividad
                JOIN enc_Programatico  USING ( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                JOIN poa_OrigenRecurso USING ( gpo_id,ur_id,epg_id )
                JOIN enc_ClaveFinanciamiento USING ( ecf_id )
                WHERE gpo_id = :gpo_id AND
                ur_id  = :ur_id AND
                epg_id = :epg_id AND
                ecf_id = :ecf_id AND
                ( oa_anticipo > 0 OR oa_anticipo' . APP_EJERCICIO_ANTERIOR . ' > 0 );
                ';
            $params = [
                ':gpo_id' => $this->gpo_id,
                ':ur_id'  => $this->ur_id,
                ':epg_id' => $this->prm->epg_id,
                ':ecf_id' => $this->prm->ecf_id,
            ];
            $comboObra->params = $params;
            $html .= $this->ponCombo($comboObra);
            $html .= '<div id="info_clc">
            </div></div>';
            if (isset($this->prm->coo_id)) {
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo:"AmortizaObra",
                        obra_id:$(this).find("option:selected").val(),
                        ecf_id:"' . $this->prm->ecf_id . '",
                        epg_id:"' . $this->prm->epg_id . '"
                        ' . (isset($this->prm->importe) ? ',importe:"' . $this->prm->importe . '"' : '') . '
                        ' . (isset($this->prm->ncc_id) ? ',ncc_id:"' . $this->prm->ncc_id . '"' : '') . '
                    });
                ');
            }
        } else {
            $importe = isset($this->prm->importe) ? $this->prm->importe : 0;
            $this->setContenedor('infoObra');
            $ecf_id = $this->prm->ecf_id;
            $epg_id = $this->prm->epg_id;

            $sql = '
                SELECT * FROM cat_ObraActividad
                JOIN enc_Programatico USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                JOIN poa_OrigenRecurso USING ( gpo_id,ur_id,epg_id )
                JOIN enc_ClaveFinanciamiento  USING ( ecf_id )
                WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND epg_id= :epg
                AND ecf_id= :ecf
                AND (oa_anticipo> 0 OR oa_anticipo' . APP_EJERCICIO_ANTERIOR . ' > 0)
                ';
            $params = [
                ':gpo' => $this->gpo_id,
                ':ur'  => $this->ur_id,
                ':epg' => $epg_id,
                ':ecf' => $ecf_id,
            ];
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($params);
            $data = $conexion->extraer();
            $conexion->desconectar();

            $html .= '
                <div style="padding:5px; background-color:rgba(0,0,0,.2); box-shadow:1px 1px 1px rgba(0,0,0,.15)">
                <table class="tabla_infoObra"><caption ><strong>INFORMACIÓN DE LA OBRA</strong></caption>
                    <tr>
                        <th class="alert alert-success">Obra</th>
                        <th class="alert alert-success">Encuadre Programatico</th>
                        <th class="alert alert-success"> Nombre</th>
                        <th class="alert alert-success" align="right">Importe Original</th>
                    </tr>';
            $html .= '
                <tr>
                    <td>' . ($data ? $data['oa_id'] : '') . '</td>
                    <td>' . ($data ? $data['prg_id'] . $data['spr_id'] . $data['py_id'] . $data['oa_id'] : '') . '</td>
                    <td>' . ($data ? $data['oa_nombre'] : '') . '</td>
                    <td align="right"><strong>$' . ($data ? number_Format(floatval($data['oa_anticipo']), 2) : '') . '</strong></td>
                </tr>';
            $html .= '</table>';

            $importe = ($data ? $data['oa_anticipo'] + $data['oa_anticipo' . APP_EJERCICIO_ANTERIOR . ''] : 0);

            if (isset($this->prm->importe)) {
                $importe = $importe + $this->prm->importe;
            }
            $sql = '
                SELECT clc_id,ncc_id,ncc_concepto,prg_id,spr_id,py_id,oa_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,amo_movimiento,amo_saldo
                FROM cat_AmortizaObra
                JOIN scp_Contable USING(gpo_id,ur_id,clc_id,afe_id,ncc_id)
                JOIN enc_ClaveFinanciamiento USING(ecf_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                JOIN enc_Programatico USING(epg_id,prg_id,spr_id,py_id,oa_id)
                WHERE epg_id = :epg AND ecf_id = :ecf';
            $params = [
                ':epg' => $this->prm->epg_id,
                ':ecf' => $this->prm->ecf_id,
            ];
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($params);
            $data2 = $conexion->extraer();

            $registros = $conexion->getNumeroRegistros();
            if ($registros == 0) {
                $html .= '<br/><div class="alert alert-warning"> NO HAY REGISTROS AMORTIZANDO ESTA OBRA</div>';
                $this->mixScript('
                $("#porcomprometer").css("display","block");
                $("#porcom").val("' . round((float) $importe, 2) . '");
                $("#importe").val(unformatNumber($("#importe").val()));
                $("#importe").blur();
            ');
            } else {
                $html .= '
                    <table class="tabla_infoObra">
                        <caption >
                            <strong>DETALLE DE LOS MOVIMIENTOS (AMORTIZACIONES) REGISTRADOS PARA ESTA OBRA</strong>
                        </caption>
                        <tr>
                            <th class="alert alert-info">CLC</th>
                            <th class="alert alert-info">Número de Control</th>
                            <th class="alert alert-info">Concepto</th>
                            <th class="alert alert-info">Encuadre Programatico</th>
                            <th class="alert alert-info">Clave Financiamiento</th>
                            <th class="alert alert-info">Tipo de Movimiento</th>
                            <th class="alert alert-info" align="right">Pago</th>
                        </tr>
                ';

                do {
                    $html .= '
                        <tr>
                            <td>' . $data2['clc_id'] . '</td>
                            <td>' . $data2['ncc_id'] . '<span style="color:#F00;">' . (isset($this->prm->ncc_id) && $this->prm->ncc_id == $data2['ncc_id'] ? 'EL ACTUAL' : '') . '</span></td>
                            <td>' . $data2['ncc_concepto'] . '</td>
                            <td>' . $data2['prg_id'] . $data2['spr_id'] . $data2['py_id'] . $data2['oa_id'] . '</td>
                            <td>' . $data2['cff_id'] . $data2['fte_id'] . $data2['sft_id'] . $data2['ori_id'] . $data2['sor_id'] . $data2['cfi_id'] . $data2['cfi_anio'] . '</td>
                            <td>' . $data2['amo_movimiento'] . '</td>
                            <td align="right"><strong>$' . number_Format(floatval($data2['amo_saldo']), 2) . '</strong></td>
                        </tr>';
                    if ($data2['amo_movimiento'] == '-') {
                        $importe = $importe - $data2['amo_saldo'];
                    } elseif ($data2['amo_movimiento'] == '+') {
                        $importe = $importe + $data2['amo_saldo'];
                    }
                } while ($data2 = $conexion->extraer());
                $html .= '</table></div>';
                $this->mixScript('
                    $("#porcomprometer").css("display","block");
                    $("#porcom").val("' . round((float) $importe, 2) . '");
                    $("#importe").val(unformatNumber($("#importe").val()));
                    $("#importe").blur();
                ');
            }
            $conexion->desconectar();
            unset($conexion);
        }
        $this->mixHtml($html);
    }

    /**AMORTIZACION DE CLC DE CONTRATO */
    private function pagadoras()
    {
        $this->setContenedor('contenedorpagadora'); /* ESTO SE PINTA DEBAJO DEL CONCEPTO */
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;

        $html = '';
        $ncc_id = '';
        $abc = isset($this->prm->ncc_id) ? 'c' : 'a';
        $sql = '
            SELECT epg_id,ecf_id,cfi_anio
            FROM scp_Afectacion
            INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
            WHERE afe_id = :afe_id AND gpo_id = :gpo_id AND ur_id= :ur_id ;
        ';
        $params = [
            ':afe_id' => $this->prm->afe_id,
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
        ];
        $data = $this->model->getDatos($sql, false, $params);
        $data = $data['datos'][0];

        $gpour = '';
        $clc_id = '';
        $ncc_id = '';
        $numinfo = null;

        if (isset($this->prm->ncc_id) && $this->prm->ncc_id != '') {
            $sql = '
                SELECT cta_gpo_id as ncc_gpo,LPAD(cta_ur_id,2,0)as ncc_ur, clc as ncc_clc,ncc_nccamortiza,ncc_ejercicio
                FROM  scp_Contable
                JOIN (
                SELECT clc_id clc ,ncc_id ncc_nccamortiza,gpo_id gpo ,ur_id ur
                FROM scp_Contable
                WHERE ncc_id IN (
                    SELECT ncc_nccamortiza
                    FROM scp_Contable
                    WHERE
                    gpo_id = :gpo_id AND
                    ur_id  = :ur_id AND
                    ncc_id = :ncc_id
                )
                )amortiza USING(ncc_nccamortiza)
                WHERE
                gpo_id = :gpo_id AND
                ur_id = :ur_id AND
                ncc_id = :ncc_id ';
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id,
                ':ncc_id' => $this->prm->ncc_id,
            ];
            $numinfo = $this->model->getDatos($sql, false, $params);
            $numinfo = $numinfo['datos'][0];
            if ($numinfo !== false) {
                $gpour = $numinfo['ncc_gpo'] . $numinfo['ncc_ur'];
                $clc_id = $numinfo['ncc_clc'];
                $ncc_id = $numinfo['ncc_nccamortiza'];
            }
        }

        $clc_pag = $this->vista->getControlPagadora('clc_pag', $abc);
        $clc_pag->value = $clc_id;

        $clc_pag_ur = null;
        if ($this->gpo_id . $this->ur_id == '118') {
            $clc_pag_ur = $this->vista->getControlPagadora('clc_pag_ur', $this->urs318);
            $clc_pag_ur->selecciona = $gpour;
        }

        $nums_control = $this->vista->getControlPagadora('nums_control');

        if ($ncc_id != '' && $abc == 'c' && $ncc_id > 0) {
            $nums_control->sql = '
                SELECT ncc_id, ncc_concepto as concepto,
                    IF(ncc_tipoMovimiento = "-" ,ncc_retencion,ncc_percepcion) as importe
                FROM scp_Contable
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND clc_id = :ncc_id ';
            $params = [
                ':gpo_id' => $numinfo['ncc_gpo'],
                ':ur_id'  => $numinfo['ncc_ur'],
                ':ncc_id' => $numinfo['ncc_clc'],
            ];
            $nums_control->params = $params;
            $nums_control->selecciona = $ncc_id;
            $nums_control = $this->ponCombo($nums_control);
        } else {
            $nums_control = $this->templateCombo($nums_control);
        }

        $anio = $this->vista->getControlPagadora('cbo_anio');

        /*****************OBTENEMOS EL TIPO Y SUBTIPO DE CLC ********************************************/
        $stc_id = '';
        $tcl_id = 0;
        if (isset($this->prm->tcl_id) && $this->prm->tcl_id != '') {
            $tcl_id = $this->prm->tcl_id;
            $stc_id = $this->prm->stc_id;
        } else {
            return false;
        }
        /******** VERIFICAMOS EL TIPO Y SUBTIPO DE CLC QUE ESTA PAGANDO/AMORTIZANDO *********************/
        $nn = false;
        $limpia = false;
        /**SI NO AMORTIZA NADA SE LIMPIA PARA QUE NO HAYA VALIDACIONES DE AMORTIZACIóN ***/
        /********PARA AHORRAR CODIGO ESTE SE REPITE VARIAS VECES PINTA EL INPUT DE CLCs Y COMBO DE NUMEROS DE CONTROL  *************/
        $htmlnormal = '';
        $htmlnormal .= '<fieldset class="col-md-12 col-xs-12 m-t-10" id="muestraCLC" style="display:block;">
            <legend>Información de la CLC a Amortizar/Pagar</legend>
                <div style="width:600px;">';
        $htmlnormal .= $this->ponInput($clc_pag);

        if ($this->gpo_id == 1 && $this->ur_id == 18) {
            $htmlnormal .= $this->ponCombo($clc_pag_ur);
            $this->mixScript('
                $("#clc_pag_ur").change(function(){
                    if ($("#clc_pago").val()==""){
                        notify("Se requiere un número de CLC","warming");
                    } else if ($(this).val()=="") {
                        notify("Se requiere una UR valida","warning");
                    } else {
                        comunica("' . $this->pantalla . '",{
                            metodo:"clcPagar",tipo:"' . $tcl_id . '",
                            clc_id:$("#clc_pago").val(),subtipo:"' . $stc_id . '",
                            clc_pag_ur:$(this).val(),cvefin:"' . $data['ecf_id'] . '"});
                    }
                });

                $("#ncp_control").change(function() {
                    if ($(this).find("option:selected").val()!="") {
                        var ur= "";
                        if ($("#clc_pag_ur").length==1) {
                            ur=$("#clc_pag_ur").val();
                        }
                        comunica("' . $this->pantalla . '",{
                            metodo:"infoNumCtrl",
                            ncc_id:' . ($abc == 'a' ? ' $(this).find("option:selected").val() ' : '"' . $ncc_id . '"') . ',
                            abc:"' . $abc . '",
                            opur:ur });
                    }
                });
            ');
        } else {
            $script = '
                $("#clc_pago").blur(function(){
                    if($(this).val() !="") {
                        comunica("' . $this->pantalla . '",{
                            metodo:"clcPagar",
                            tipo:"' . $tcl_id . '",
                            clc_id:$(this).val(),
                            subtipo:"' . $stc_id . '",
                            cvefin:"' . $data['ecf_id'] . '"
                        });
                    }
                });
                $("#ncp_control").change(function(){
                    if($(this).find("option:selected").val()!=""){
                        comunica("' . $this->pantalla . '",{
                            metodo:"infoNumCtrl",
                            ncc_id:' . ($abc == 'a' ? ' $(this).find("option:selected").val()' : '"' . $ncc_id . '"') . ',
                            abc:"' . $abc . '"
                        });
                    }
                });
            ';
        }
        $htmlnormal .= $nums_control . '</fieldset></div>';

        /****EVALUAMOS EL TIPO DE CLC QUE ESTA HACIENDO EL REGISTRO   ********/
        $this->prm->abc = isset($this->prm->abc) ? $this->prm->abc : '';
        if ($this->prm->abc == 'a' || ($this->prm->abc != 'a' && $ncc_id == '')) {
            switch ($tcl_id) {
                case 'A':
                case 'B':
                case 'G':
                case 'I': /*  ESTOS SON TIPOS DE AMORTIZACION / PAGO NORMAL  */
                    $nn = true;
                    $limpia = false;
                    break;
                case 'F':
                    $tempRfcObra = [];
                    if (isset($this->prm->cuenta) && (($this->prm->cuenta == 21194 && $this->prm->cta == '005')
                        || ($this->prm->cuenta == 11342 && $this->prm->cta == '001'))) {
                        $rfcObra = new AccesoDatos();
                        $rfcObra->conectar();
                        $rfcObra->setSql('
                            SELECT
                            rfc_id
                            FROM  sinpres.cat_InversionPublica a
                            INNER JOIN (
                            SELECT
                            afe_ipu_id
                            FROM scp_Afectacion
                            WHERE gpo_id = :gpo
                            AND ur_id = :ur
                            AND afe_id = :afe
                            ) afectacion ON afectacion.afe_ipu_id = ipu_id
                            WHERE ipu_id = afectacion.afe_ipu_id;
                            ');
                        $params = [
                            ':gpo' => $this->gpo_id,
                            ':ur'  => $this->ur_id,
                            ':afe' => $this->prm->afe_id,
                        ];
                        $rfcObra->consultar($params);
                        if ($rfcObra->getNumeroRegistros() == 0) {
                            $this->mixScript('notify("No se puede asignar el rfc","warning");');
                            return false;
                        }
                        $tempRfcObra = $rfcObra->extraer();
                        $rfcObra->desconectar();
                        unset($rfcObra);
                    }
                    $mensaje = '';
                    if (isset($this->prm->cuenta) && $this->prm->cuenta == 21192) {
                        $mensaje = 'EL TIPO DE CLC CONTRATO CON LA CUENTA 21192 REQUIERE AMORTIZAR UNA CLC DE ACUERDOS DE MINISTRACIóN / CRÉDITOS PUENTE';
                        $nn = true;
                        $limpia = false;
                        $this->mixScript('$("#mensajeamor").html("' . $mensaje . '");<');
                    } elseif (isset($this->prm->cuenta) && $this->prm->cuenta == 21194 && $this->prm->cta == '005') {
                        $this->mixScript('
                            $("#divpagadoras").css("display","block");
                            $("#mensajeamor").html("CON ESTE TIPO DE CLC CON LA CUENTA CONTABLE 21194 Y CUARTO NIVEL 005 SE AMORTIZARA LA OBRA QUE TIENE LOS DATOS DEBAJO MOSTRADOS");
                            $("#clc_pg, .squaredTwo").css("display","none");
                            $("#contenedorpagadora").css("display","block");
                            $("#clc_pg, #squaredTwo").attr("checked","checked");
                            comunica("' . $this->pantalla . '",{
                                metodo:"AmortizaObra",
                                ecf_id:"' . $data['ecf_id'] . '",
                                epg_id:"' . $data['epg_id'] . '"
                            });
                            $("#rfc_id").val("' . $tempRfcObra['rfc_id'] . '");
                            $("#rfc_id").attr("disabled","1");
                            comunica("' . $this->pantalla . '",{
                                metodo:"proveedor",
                                rfc:"' . $tempRfcObra['rfc_id'] . '",
                                abc:"' . $abc . '"
                                });
                            ');
                        return;
                    } elseif (isset($this->prm->cuenta) && $this->prm->cuenta == 11342 && $this->prm->cta == '001') {
                        $this->mixScript('
                            $("#divpagadoras").css("display","block");
                            $("#mensajeamor").html("CON ESTE TIPO DE CLC CON LA CUENTA CONTABLE 11342 Y CUARTO NIVEL 001 SE AMORTIZARA LA OBRA QUE TIENE LOS DATOS DEBAJO MOSTRADOS");
                            $("#clc_pg, .squaredTwo").css("display","none");
                            $("#contenedorpagadora").css("display","block");
                            $("#clc_pg, #squaredTwo").attr("checked","checked");
                            comunica("' . $this->pantalla . '",{
                                metodo:"AmortizaObra",
                                ecf_id:"' . $data['ecf_id'] . '",
                                epg_id:"' . $data['epg_id'] . '"
                            });
                            $("#rfc_id").val("' . $tempRfcObra['rfc_id'] . '");
                            $("#rfc_id").attr("disabled","1");
                            comunica("' . $this->pantalla . '",{
                                metodo:"proveedor",
                                rfc:"' . $tempRfcObra['rfc_id'] . '",
                                abc:"' . $abc . '"
                            });
                        ');
                        return;
                    } else {
                        $limpia = true;
                        $this->mixScript('
                            $("#rfc_id").removeAttr("disabled");
                            $("#rfc_id").val("");
                            ');
                    }
                    break;
                case 'C':
                    /*****PAGOS A TERCEROS MANEJA 5 SUBTIPOS DE LOS CUALES PAGADORAS SOLO HAY 4  *******/
                    switch ($stc_id) {
                        case '01':
                            /****** PAGOS A TERCEROS - SERVICIOS PERSONALES ********/
                            $html .= '<fieldset class="col-md-12 col-xs-12" id="muestraCLC" style="display:block;"><legend>Información de la CLC a pagar</legend>';
                            $html .= '<div style="width:600px;">';
                            $html .= $this->ponInput($clc_pag);
                            /********* SI ES LA 318 PUEDE AMORTIZAR SERVICIOS PERSONALES DE VARIAS UNIDADES RESPONSABLES  */
                            if ($this->gpo_id == 1 && $this->ur_id == 18) {
                                $html .= $this->ponInput($clc_pag_ur);
                                $this->mixScript('
                                    $("#clc_pag_ur").blur(function(){
                                        if ($("#clc_pag").val()=="") {
                                            return false;
                                        } else if ($(this).val()=="") {
                                            notify("Se requiere una UR valida","warning");
                                        } else {
                                            comunica("' . $this->pantalla . '",{
                                                metodo:"clcPagar",
                                                tipo:"' . $tcl_id . '",
                                                clc_id:$("#clc_pago").val(),
                                                subtipo:"' . $stc_id . '",
                                                clc_pag_ur:$(this).val(),
                                                cvefin:"' . $data['ecf_id'] . '"
                                            });
                                        }
                                    });
                                    $("#ncp_control").change(function(){
                                        if( $(this).find("option:selected").val() != "" ){
                                            var ur= "";
                                            if( $("#clc_pag_ur").length==1  )
                                            {
                                                ur=$("#clc_pag_ur").val();
                                            }
                                            comunica("' . $this->pantalla . '",{
                                                metodo:"infoNumCtrl",
                                                ncc_id:' . ($abc == 'a' ? '$(this).find("option:selected").val()' : '"' . $ncc_id . '"') . ',
                                                abc:"' . $abc . '",
                                                opur:ur }
                                            );
                                        }
                                    });
                                ');
                            } else {
                                $this->mixScript('
                                    $("#clc_pago").blur(function(){
                                        if($(this).val() !=""){
                                            comunica("' . $this->pantalla . '",{
                                                metodo:"clcPagar",
                                                tipo:"' . $this->prm->tcl_id . '",
                                                subtipo:"' . $stc_id . '",
                                                clc_id:$(this).val(),
                                                cvefin:"' . $data['ecf_id'] . '"
                                            });
                                        }
                                    });
                                    $("#ncp_control").change(function(){
                                        if($(this).find("option:selected").val()!=""){
                                            comunica("' . $this->pantalla . '",{
                                                metodo:"infoNumCtrl",
                                                ncc_id:' . ($abc == 'a' ? '$(this).find("option:selected").val()' : '"' . $ncc_id . '"') . ',
                                                abc:"' . $abc . '"
                                            });
                                        }
                                    });
                                ');
                            }
                            $html .= $nums_control . '</fieldset></div>';
                            break;
                        case '02':
                            /****** PAGOS A TERCEROS - GASTOS DE OPERACION ********/
                            $nn = true;
                            $limpia = false;
                            break;
                        case '03':
                            /****** PAGOS A TERCEROS - CONTRATO ********/
                            if (isset($this->prm->cuenta) && $this->prm->cuenta == 21171 && $this->prm->abc == 'a') {
                                $nn = true;
                                $this->mixScript('
                                $("#mensajeamor").html("PARA EL TIPO DE CLC DE PAGOS A TERCEROS DE CONTRATO CON CUENTA 21171, ES NECESARIO AMORTIZAR CLC DE CONTRATO");
                                $("#clc_pg, .squaredTwo").css("display","none");
                                $("#clc_pg,#squaredTwo").attr("checked","checked");
                                $("#divpagadoras").css("display","block");
                                $("#contenedorpagadora").css("display","block");

                                comunica("' . $this->pantalla . '",{
                                    "metodo":"Contrato",
                                        abc:"' . $this->prm->abc . '",
                                        "ecf_id":"' . $data['ecf_id'] . '",
                                        "cuenta":"21171",
                                        cuentacompleta:"' . $this->prm->cuentacompleta . '",
                                        tipo:"' . $tcl_id . '",
                                        stc_id:"' . $stc_id . '",
                                        aniocve:"' . $data['cfi_anio'] . '"
                                    });
                                ');
                                return;
                            } else {
                                $limpia = true;
                            }
                            break;
                        case '04':
                            /****** PAGOS A TERCEROS - ADMINISTRACIóN DIRECTA ********/
                            if ($this->prm->cuenta == 21171) {
                                $nn = true;
                                $this->mixScript('
                                $("#mensajeamor").html("PARA EL TIPO DE CLC DE PAGOS A TERCEROS DE CONTRATO CON CUENTA 21171, ES NECESARIO AMORTIZAR CLC DE ADMINISTRACIóN DIRECTA");
                                $("#clc_pg, .squaredTwo").css("display","none");
                                $("#clc_pg,#squaredTwo").attr("checked","checked");');
                            } else {
                                $limpia = true;
                            }
                            break;
                    } //VALIDACIONES DE PAGOS A TERCEROS SEGUN SU SUBTIPO
                    break;
                default:
                    $html = '';
                    break;
            }
        } else {
            switch ($tcl_id) {
                case 'F':
                    $this->mixScript('
                        comunica("' . $this->pantalla . '",{
                            metodo:"AmortizaObra",
                            ecf_id:"' . $data['ecf_id'] . '",
                            epg_id:"' . $data['epg_id'] . '"});
                        ');
                    break;
                case 'G':
                    $nn = false;
                    break;
                default:
                    $nn = true;
                    break;
            }
        }
        $html .= '<div id="info_clc"><div>';
        $this->prm->cuentacompleta = isset($this->prm->cuentacompleta) ? $this->prm->cuentacompleta : '';
        if ($abc == 'a' && $this->prm->tcl_id == 'C' && $this->prm->stc_id = 1 && ($this->prm->cuentacompleta == '21194.118.001.002.0225' || $this->prm->cuentacompleta == '21194.118.001.003.0230' || $this->prm->cuentacompleta == '21194.118.001.004.0231')) {
            $limpia = true;
        }

        if ($limpia) {
            $this->mixScript('
                $("#clc_pg,#squaredTwo").removeAttr("checked");    $("#divpagadoras").css("display","none");
                $("#contenedorpagadora").fadeOut("slow");
                $("#contenedorpagadora").css("height","1px");
                $("#clc_pag_ur").removeAttr("required");
            ');
            return;
        } else {
            $this->mixScript('$("#divpagadoras").css("display","block");');
        }

        if ($nn) {
            $this->mixHtml($htmlnormal . '<div id="info_clc"><div>');
            $this->mixScript($script);
        } else {
            $this->mixHtml($html);
        }
        if (isset($this->prm->x_n) && $this->prm->x_n == 'r') {
            $html = '';
            $this->mixScript('
                    $("#contenedorpagadora").fadeOut("slow");
                    $("#contenedorpagadora").css("height","1px");
            ');
            $this->mixHtml($html);
            $limpia = true;
        } else {
            $this->mixScript('
                $("#contenedorpagadora").css("height","250px");
                $("#contenedorpagadora").fadeIn("slow");
                $("#clc_pag_ur").attr("required","1");
                ');
        }
        if ($abc == 'c') {
            $this->mixScript('$("#ncp_control").change();');
        }
    }

    private function Contrato()
    {
        // AMORTIZACION DE CLC DE CONTRATO
        if ($this->prm->abc != 'a') {
            return false;
        }

        $suma = '';
        $cuenta = $this->prm->cuenta;
        $ger_id = substr($cuenta, 0, 1);
        $cgp_id = substr($cuenta, 1, 1);
        $rub_id = substr($cuenta, 2, 1);
        $cgcu_id = substr($cuenta, 3, 1);
        $cgsc_id = substr($cuenta, 4, 1);
        $cuentas = explode('.', $this->prm->cuentacompleta);
        $ger_id = substr($cuentas[0], 0, 1);
        $cgp_id = substr($cuentas[0], 1, 1);
        $rub_id = substr($cuentas[0], 2, 1);
        $cgcu_id = substr($cuentas[0], 3, 1);
        $cgsc_id = substr($cuentas[0], 4, 1);
        $cta_gpo_id = substr($cuentas[1], 0, 1);
        $cta_ur_id = substr($cuentas[1], 1, 2);
        $cta_ue_id = $cuentas[2];
        $cgc_id = $cuentas[3];

        if (strlen($this->prm->cuenta) == 25) {
            $cta_id = $cuentas[4] . $cuentas[5];
        } else {
            $cta_id = $cuentas[4];
        }

        $this->setContenedor('contenedorpagadora');
        $sql = '
            SELECT * FROM scp_Contable
            JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
            JOIN scp_Clc USING(gpo_id,ur_id,clc_id)
            ';
        switch ($this->prm->tipo) {
            case 'G':
                $sql .= 'WHERE scp_Clc.tcl_id ="D" AND scp_Clc.stc_id =01 ';
                break;
            case 'C':
                $suma = 'retencion';
                if ($this->prm->stc_id == 03 && $cuenta == 21171) {
                    $sql .= 'WHERE scp_Clc.tcl_id ="F" ';
                } elseif ($this->prm->stc_id == 04 && $cuenta == 21171) {
                    $sql .= 'WHERE scp_Clc.tcl_id ="G" ';
                }

                break;
            default:
                return;
                break;
        }
        $sql .= ' AND gpo_id =' . $this->gpo_id . ' AND ur_id =' . $this->ur_id . '
                AND ncc_amortizado=2
                AND ecl_id BETWEEN 10 AND 18
                AND ger_id = :ger AND cgp_id = :cgp
                AND rub_id = :rub AND cgcu_id = :cgcu
                AND cgsc_id = :cgsc AND cta_gpo_id= :ctagpo
                AND cta_ur_id = :ctaur AND cta_ue_id= :ctaue
                AND cgc_id = :cgc AND cta_id = :cta
                ';
        $sql .= ' AND scp_Clc.ecf_id=:ecf';
        $params = [
            ':ger'    => $ger_id,
            ':cgp'    => $cgp_id,
            ':rub'    => $rub_id,
            ':cgcu'   => $cgcu_id,
            ':cgsc'   => $cgsc_id,
            ':ctagpo' => $cta_gpo_id,
            ':ctaur'  => $cta_ur_id,
            ':ctaue'  => $cta_ue_id,
            ':cgc'    => $cgc_id,
            ':cta'    => $cta_id,
            ':ecf'    => ($this->prm->ecf_id),
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        if ($this->getNumeroRegistros() == 0 && $this->prm->aniocve == 14) {
            $this->mixScript(' $("#muestraCLC").css("display","block");$("#contenedorpagadora").css("height","38px");');
            $html = '<div style="padding:10px; color:#F33; border:1px solid #FBB; background-color:#FCC;">No hay Registros para Amortizar y la Clave de finaciamiento no permite amortizar Años anteriores, si es obligatoria la amortización o el pago, no podrá continuar con su registro</div>';
            $this->mixHtml($html);
            return;
        } elseif ($this->getNumeroRegistros() == 0 && $this->prm->aniocve < 14) {
            $permite = false; // PARA  PERMITIR EL PASO DEL REGISTRO SIN VALIDAR LA AMORTIZACION
            switch ($cuentas[0]) {
                case 21171:
                    if ($cgc_id == '011' || $cgc_id == '008' || $cgc_id == '009' || $cgc_id == '015' || $cgc_id == '013' || $cgc_id == '014' || $cgc_id == '012' || $cgc_id == '003' || $cgc_id == '005') {
                        $permite = true;
                    }
                    break;
                default:
                    $permite = false;
                    break;
            }
            if (!$permite) {
                $html = '
                    <div style="padding:10px; color:#F33; border:1px solid #FBB; background-color:#FCC;">
                        No hay Registros para Amortizar y la cuenta contable no es valida para amortizar años anteriores, si es obligatoria la amortización o el pago, no podrá continuar con su registro
                    </div>';
                $this->mixHtml($html);
                $this->mixScript('
                    $("#contenedorpagadora").css("height","38px");
                    $("#clc_pg,#squaredTwo").attr("checked","checked");');
            } elseif ($this->getNumeroRegistros() == 0 && $permite == true) {
                $html = '
                    <div style="padding:10px; color:#F33; border:1px solid #FBB; background-color:#FFF;">
                        No hay Registros para Amortizar, pero su cuenta contable permite pasar sin amortizar
                    </div>';
                $this->mixHtml($html);
                $this->mixScript('
                    $("#contenedorpagadora").css("height","38px");
                    $("#clc_pg,#squaredTwo").removeAttr("checked");
                    $("#importe").removeAttr("disabled");
                ');
            }
        } elseif ($this->getNumeroRegistros() == 0) {
            $html = '<div style="padding:10px; color:#F33; border:1px solid #FBB; background-color:#FCC;">No hay Registros para Amortizar y la Clave de finaciamiento no permite amortizar Años anteriores, si es obligatoria la amortización o el pago, no podrá continuar con su registro</div>';
            $this->mixScript('$("#contenedorpagadora").css("height","38px");');
            $this->mixHtml($html);
            return false;
        }

        $data = $this->extraer();
        $this->desconectar();
        $class = 'style="padding:4px; background-color:#EAEAEA;';
        $classtd = 'background-color:#EAFFEA;';
        $celda = 0;
        $html = '<legend>Números de Control que puede amortizar</legend>';
        $html .= '
            <table style="width:98.8%; border:1px solid #EAEAEA; background-color:#FFF;  padding:5px;  border:1px solid #AAA; box-shadow:2px 2px 2px #999;">
                <tr>
                    <th width="5%" ' . $class . '">TIPO / CLC</th>
                    <th width="5%" ' . $class . '">Afectación</th>
                    <th width="5%" ' . $class . '">Número de Control</th>
                    <th width="55%" ' . $class . '">Concepto del Número</th>
                    <th width="10%" ' . $class . ' text-align:right">Percepción</th>
                    <th width="10%" ' . $class . ' text-align:right">Retención</th>
                    <th width="5%" ' . $class . ' text-align:center">Movimiento</th>
                    <th width="5%" ' . $class . '">Amortizar
                    <input type="checkbox" id="SeleccionaTodas"/> Todas
                    </th>
                </tr></table>';
        $html .= '<div id="divporamortizar"><table width="100%" style="background-color:#FFF;">';
        do {
            $html .= '
                <tr>
                    <td width="5%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px;"> ' . $data['tcl_id'] . ' / ' . $data['clc_id'] . ' </td>
                    <td width="5%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px;"> ' . $data['afe_id'] . '</td>
                    <td width="5%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px;"> ' . $data['ncc_id'] . '</td>
                    <td width="55%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px;"> ' . $data['ncc_concepto'] . '</td>
                    <td width="10%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px; text-align:right;">$ ' . number_format(floatval($data['ncc_percepcion']), 2) . '</td>
                    <td width="10%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px; text-align:right;">$ ' . number_format(floatval($data['ncc_retencion']), 2) . '</td>
                    <td width="5%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px; text-align:center;">  ' . $data['ncc_tipoMovimiento'] . '</td>
                    <td width="5%" style="    ' . ($celda == 1 ? $classtd : '') . 'border-bottom:1px solid #EAEAEA;padding:4px;">
                    <input type="checkbox" ncc_id ="' . $data['ncc_id'] . '" clc_id="' . $data['clc_id'] . '" percepcion="' . $data['ncc_percepcion'] . '" retencion="' . $data['ncc_retencion'] . '"  class="checkamortizar"/>
                    </td>
                </tr>';

            if ($celda == 0) {
                $celda = 1;
            } else {
                $celda = 0;
            }
        } while ($data = $this->extraer());
        $html .= '
            </table></div><div id="totalesamortizar" style="padding:5px; text-align:right; border:1px solid #EAEAEA;  padding:5px;  border:1px solid #AAA; box-shadow:2px 2px 2px #999;">
            No hay nada seleccionado para amortizar....</div>';
        $this->mixHtml($html);
        $this->mixScript('
            $("#contenedorpagadora").css("height","auto");

            if("' . $suma . '" == "percepcion"){
                $("#percepciones").attr("disabled","disabled");
            }else{
                $("#retenciones").attr("disabled","disabled");
            }

            $("#muestraCLC").css("display","block");
            ' . (isset($permite) ? '' : '
                $("#importe").attr("disabled","disabled");
                $("#clc_pg,#squaredTwo").prop("checked",true);
            ') . '.

            function contratoTodosImporte(){
                var cuantas =0;
                $(".checkamortizar").each(function(){
                    tmp = $(this).attr("' . $suma . '");
                    cuantas  = cuantas + parseFloat(tmp);
                });
                cuantas = parseFloat(cuantas).toFixed(2);
                return cuantas;
            }

            function contratoTodos(){
                var cuantas =0;
                $(".checkamortizar").each(function(){
                    cuantas ++;
                });
                return cuantas;
            }
            $(".checkamortizar").click(function(){
                var m = 0;
                var n = 0;
                $(".checkamortizar").each(function(){
                    if($(this).is(":checked")){
                        m++;
                        tmp = $(this).attr("' . $suma . '");
                        n = n+parseFloat(tmp);
                    }
                });
                n = parseFloat(n).toFixed(2);
                $("#totalesamortizar").html("Hay <strong>" + m +"</strong> número(s) de control para amortizar por un total de <string>$"+ agregacomas(n)+ "</strong>");
                $("#importe").val(""+ n +"");
                $("#importe").blur();
            });

            $("#SeleccionaTodas").click(function(){
                var m = contratoTodos();
                var n = contratoTodosImporte();

                if($(this).is(":checked")){
                    if(confirm("Confirme la amortización de  lo(s) " + m + " números de control por la cantidad de $"+ agregacomas(n) +"?" )){
                        $(".checkamortizar").attr("checked","checked");
                        $("#totalesamortizar").html("Hay " + m +" número(s) de control para amortizar por un total de $"+ agregacomas(n));
                        $("#importe").val(""+ n +"");
                        $("#importe").blur();
                    }else{
                        return false;
                    }
                }else{
                    $(".checkamortizar").removeAttr("checked");
                }
            });
        ');
    }

    private function whyscreen()
    {
        $this->setContenedor('det_fsinfo_referencia');

        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $html = '';
        $html .= '<span style="font-size:16px"> Para el ejercicio ' . APP_EJERCICIO . ' el disponible para sus numeros de control se rige por los siguientes elementos:</span><br>
            Compromiso - Que es el compromiso de pago que Usted realiza en la pantalla de Compromisos, este es el primer Importe.<br/>
            <span style="font-family:Arial;padding-left:20px;color:#900">Usted no puede devengar mas del importe del compromiso</br>
                Si selecciona Generar Compromiso nuevo, este compromiso no podrá superar al importe máximo por comprometer en el año
            </span><br>
            Documento Comprobatorio: Es el documento que Usted registra como referencia, este tiene un importe que es el segundo importe que rige el numero de control<br/>
            <span style="font-family:Arial; padding-left:20px;color:#900">Usted no puede devengar mas del importe del Documento referenciado</span></br>
            Presupuesto Disponible por Devengar: es el presupuesto que no se ha devengado en el Trimestre Actual este es el ultimo y el mas importante.<br/>
            <span style="font-family:Arial; padding-left:20px;color:#900">Usted no puede devengar mas del importe Disponible por devengar.</span></br>

            Utilize las ayudas que vienen en el compromiso <img src="img/img.php?img=link_compromiso" hspace="5"/>,referencia asi como la gráfica del presupuesto
            <hr/>';
        if ((isset($this->prm->rfc_id) && $this->prm->rfc_id != '')
            && (isset($this->prm->referencia) && $this->prm->referencia != '')
            && (isset($this->prm->afe_id) && $this->prm->afe_id != '')
            && (isset($this->prm->cmp_id) && $this->prm->cmp_id != 0)
            && (isset($this->prm->pg_id) && $this->prm->pg_id != '')
            && (isset($this->prm->pe_id) && $this->prm->pe_id != '')
        ) {
            $sql = '
                SELECT ue_id,epg_id,ecf_id,tcp_id, MONTH(NOW()) AS Mes,prg_id,spr_id,py_id,oa_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
                FROM scp_Afectacion
                INNER JOIN enc_Programatico USING( gpo_id,ur_id,epg_id )
                INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND afe_id = :afe_id';
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id,
                ':afe_id' => $this->prm->afe_id,
            ];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $dat = $this->extraer();

            $ex = new stdClass();
            $ex->epg_id = $dat['epg_id'];
            $ex->mes = $dat['Mes'];
            $ex->ecf_id = $dat['ecf_id'];
            $ex->tcp_id = $dat['tcp_id'];
            $ex->pg_id = $this->prm->pg_id;
            $ex->pe_id = $this->prm->pe_id;
            $ex->ue_id = $dat['ue_id'];
            $ex->tipo = 'presupuestal';

            $this->desconectar();
            $max = 0;

            $presupuesto = $this->presupuesto($ex);
            $documento = ValidaGarantias::gtDocumento('', $this->prm->rfc_id, $this->prm->referencia, '');
            $compromiso = ValidaGarantias::gtCompromiso($this->prm->cmp_id);

            if ($presupuesto->Diferencia > $documento->importe) {
                $max = $presupuesto->Diferencia;
            } else {
                $max = $documento->importe;
            }
            if ($compromiso->cmp_importe > $max) {
                $max = $compromiso->cmp_importe;
            }

            $cmp = round((float) ($compromiso->cmp_importe / $max) * 600);
            $cmd = round((float) ($compromiso->devengado / $compromiso->cmp_importe) * $cmp);
            $cmd += 0;

            $docto = round((float) (($documento->importe) / $max) * 600);
            $doctd = round((float) (($documento->referenciado) / $documento->importe) * $docto);

            $pre = round((float) (($presupuesto->Modificado) / $max) * 600);
            $prd = round((float) ($presupuesto->Devengado + $presupuesto->enafectacion) / $presupuesto->Modificado * $pre);

            $dCmp = $compromiso->cmp_importe + $compromiso->ajustado - ($compromiso->devengado + $compromiso->usado);
            $dDoc = $documento->importe - $documento->referenciado;
            $dPre = $presupuesto->Modificado - ($presupuesto->Devengado + $presupuesto->enafectacion);
            $mensaje = '';
            $cc = 'class="danger" style="color:#900"';
            $cd = 'class="danger" style="color:#900"';
            $cp = 'class="danger" style="color:#900"';
            if ($dCmp > $dDoc) {
                if ($dDoc > $dPre) {
                    $mensaje = 'En este caso se usa el importe del Presupuesto como el limite porque es el menor de todos los importes';
                    $cp = 'class="success" style="color:#090"';
                } else {
                    $mensaje = 'En este caso se usa el importe del Documento como el limite porque es el menor de todos los importes';
                    $cd = 'class="success" style="color:#090"';
                }
            } else {
                if ($dCmp > $dPre) {
                    $mensaje = 'En este caso se usa el importe del Presupuesto como el limite porque es el menor de todos los importes';
                    $cp = 'class="success" style="color:#090"';
                } else {
                    $mensaje = 'En este caso se usa el importe del Compromiso como el limite porque es el menor de todos los importes';
                    $cc = 'class="success" style="color:#090"';
                }
            }

            $html .= '
                <table class="table table-bordered table-striped" style="font-size:14px;color:#000">
                    <tr>
                        <td style="width:10%"></td>
                        <td style="width:30%" class="warning">Compromiso</td>
                        <td style="width:30%" class="warning">Documento</td>
                        <td style="width:30%" class="warning">Presupuesto</td>
                    </tr>
                    <tr>
                        <td>Info</td>
                        <td>' . $this->prm->cmp_id . '</td>
                        <td>' . $documento->rfc_id . '-' . $documento->referencia . '</td>
                        <td>' . $presupuesto->clave . '</td>
                    </tr>
                    <tr>
                        <td class="warnign">
                            Importe
                        </td>
                        <td align="right">$ ' . number_format(floatval($compromiso->cmp_importe + $compromiso->ajustado), 2) . '</td>
                        <td align="right">$ ' . number_format(floatval(($documento->importe)), 2) . '</td>
                        <td align="right">$ ' . number_format(floatval($presupuesto->Modificado), 2) . ' </td>
                    <tr>
                        <td class="warnign">Devengado</td>
                        <td align="right">$' . number_format(floatval($compromiso->devengado), 2) . '</td>
                        <td align="right">$' . number_format(floatval($documento->devengado), 2) . '</td>
                        <td align="right">$' . number_format(floatval($presupuesto->Devengado), 2) . '</td>
                    </tr>
                    <tr>
                        <td class="warnign">Importe Usado </td>
                        <td align="right">$' . number_format(floatval($compromiso->usado), 2) . '</td>
                        <td align="right">$' . number_format(floatval($documento->referenciado), 2) . '</td>
                        <td align="right">$' . number_format(floatval($presupuesto->Devengado), 2) . '</td>
                    </tr>
                    <tr>
                        <td class="success">Disponible por usar</td>
                        <td ' . $cc . ' align="right">$ ' . number_format(floatval($dCmp), 2) . '</td>
                        <td ' . $cd . ' align="right">$ ' . number_format(floatval($dDoc), 2) . '</td>
                        <td ' . $cp . ' align="right">$ ' . number_format(floatval($dPre), 2) . '</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                        ' . $mensaje . '
                        </td>
                    </tr>
            </table>
            ';
        } else {
            $html .= '
            Si su compromiso es por $10,000.00, y su documento es por 5,000.00 Usted solo podra usar 5,000.00 ya que es el monto de documento.<br/>
            Si fuese alreves, suponiendo que su compromiso es por 3,000.00 y su documento por  $10,000.00 solo podra usar los 3,000.00.<br/>

            En cualquiera de los dos casos, el importe derivado de esa comparación aun se tiene que comparar con el Disponible a devengar en el Bimestre.</br>
            En cualquiera de los ejemplos anteriores. Si el disponible por devengar es solo por 500.00 solo podra usar esos $ 500.00 en el numero de control.<br><br>
            <center>
            <span class="warning" style="padding:6px;">Si desea datos de toda su información real, rellene todo el formulario e invoque esta ventana nuevamente!!</span>
            </center>';
        }
        $this->mixScript('
            $("#back_info_referencia").fadeIn("slow");
            $("#close_info_referencia, #back_info_referencia").click(function(){
                $("#back_info_referencia").fadeOut("slow");
            });
            $("#fsinfo_referencia").css("height","auto");
        ');
        $this->mixHtml($html);
    }

    /**ACTUALIZA AMORTIZADOS PóR LA CANCELACION DE AFECTACION */
    private function actualizaAmortizados($ncc)
    {
        $sql = '
            SELECT ncc_id,ncc_percepcion,ncc_retencion,Percepcion,Retencion FROM  scp_Contable
            LEFT JOIN (
            SELECT SUM(ncc_percepcion) Percepcion, SUM(ncc_retencion) Retencion, ncc_nccamortiza AS ncc_id
            FROM scp_Contable
            INNER JOIN scp_Afectacion USING( gpo_id,ur_id,clc_id,afe_id )
            WHERE ncc_nccamortiza = :ncc_nccamortiza AND eaf_id !="C"
            ) B USING ( ncc_id )
            WHERE  ncc_id  = :ncc_id';
        $params = [
            ':ncc_nccamortiza' => $ncc,
            ':ncc_id'          => $ncc,
        ];
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $con->desconectar();
        $sql2 = '';
        $data = $con->extraer();
        if ($data['ncc_percepcion'] > 0) {
            if ($data['Retencion'] == $data['ncc_percepcion']) {
                $sql2 = ' UPDATE scp_Contable SET ncc_amortizado = 1 WHERE ncc_id = :ncc_id';
            } else {
                $sql2 = ' UPDATE scp_Contable SET ncc_amortizado = 2 WHERE ncc_id = :ncc_id';
            }
        } elseif ($data['ncc_retencion'] > 0) {
            if ($data['Percepcion'] == $data['ncc_retencion']) {
                $sql2 = ' UPDATE scp_Contable SET ncc_amortizado = 1 WHERE ncc_id = :ncc_id';
            } else {
                $sql2 = ' UPDATE scp_Contable SET ncc_amortizado = 2 WHERE ncc_id = :ncc_id';
            }
        }
        $params = [':ncc_id' => $ncc];
        $con->conectar();
        $con->setSql('SET @scp = 1;');
        $con->consultar($params);
        $con->setSql($sql2);
        $con->consultar();
        $con->setSql('SET @scp = NULL;');
        $con->consultar();
        $con->desconectar();
    }

    private function revisaDocumentos($afe, $tdf)
    {
        $sql = '
            SELECT gpo_id,ur_id,afe_id,C
            FROM scp_Afectacion
            LEFT JOIN (
            SELECT gpo_id,ur_id,afe_id,SUM(C) AS C FROM(
            SELECT gpo_id,ur_id,afe_id,COUNT(bdo_id) C FROM scp_Contable
            INNER JOIN cat_BancoDocumentos USING ( gpo_id,ur_id,bdo_id )
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id AND tdf_id ' . ($tdf == 'RCT' ? '!=' : ' = ') . ' "RCT"
            UNION ALL
            SELECT gpo_id,ur_id,afe_id,COUNT(bdo_id) C FROM scp_Presupuestal
            INNER JOIN cat_BancoDocumentos USING ( gpo_id,ur_id,bdo_id )
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id AND tdf_id ' . ($tdf == 'RCT' ? '!=' : ' = ') . ' "RCT"
            GROUP BY tdf_id
            ) v
            GROUP BY gpo_id,ur_id,afe_id
            ) m USING ( gpo_id,ur_id,afe_id )
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id;
            ';
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':afe_id' => $afe,
        ];
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $data = $con->extraer();
        $con->desconectar();
        if ($data['C'] > 0) {
            return false;
        } else {
            return true;
        }
    }

    private function pintaDetalles()
    {
        $html = '';
        switch ($this->prm->tipo) {
            case 'epg':
                $this->setContenedor('detalle_afe' . $this->prm->epg_id);
                $datos = $this->model->getDetalleAfectacion($this->prm->epg_id);
                $html = $this->vista->vwDetalleAfectacion($datos);
                break;
            case 'Presupuestal':
                $this->setContenedor('detalle_afe' . $this->prm->num);
                $data = $this->model->getDetalleClavePresupuestal($this->prm->num);
                $html = $this->vista->vwDetalleClavePresupuestal($data);
                break;
            case 'Contable':
                $this->setContenedor('detalle_afe' . $this->prm->num);
                $data = $this->model->getDetalleContable($this->prm->num);
                $html = $this->vista->vwDetalleContable($data);
                break;
        }
        $this->mixHtml($html);
    }

    private function ponF1($prm)
    {
        $encProg = $this->vista->getF1Control('encProg');
        $nomProg = $this->vista->getF1Control('nomProg');
        $encCveFin = $this->vista->getF1Control('encCveFin');
        $nomCveFin = $this->vista->getF1Control('nomCveFin');
        $cveFin = new stdClass();
        $tipos = ['M', 'C', 'D', 'E', 'K'];

        if ($prm['abc'] == 'c') {
            $cveFin->ecf_id = $prm['ecf_id'];
            $cveFin->lectura = true;
            $cveFin->selecciona = $prm['ecf_id'];
        }
        if ($prm['abc'] == 'c') {
            ClasificacionFinanciamiento::PonEnVariablesEcf($prm['ecf_id']);
            $cve = $this->model->getClaveFinF1($prm['ecf_id']);

            $encCveFin->lectura = true;
            $encCveFin->value = $cve['encuadre'];
            $nomCveFin->value = $cve['nombre'];

            $cveFin->ecf_id = $prm['ecf_id'];
            $cveFin->lectura = true;
            $cveFin->selecciona = $prm['ecf_id'];

            $valorEpg = $this->model->getEpgF1($prm['epg_id']);
            if (empty($valorEpg)) {
                $valorEpg = '';
            }

            $nomProg->value = ClasificacionAdministrativa::PonEnVariablesEpg($prm['epg_id']);
            $encProg->value = $valorEpg;
            $encProg->lectura = true;
        } else {
            $encCveFin->keydown = '
                var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;
                ' . (in_array($prm['tipo'], $tipos) ? '' : '
                if ( key == 112 ) {
                    event.preventDefault();
                    dlgC.dialog("open");
                } else ') . ' if( key == 113 ) {
                    if ( $("#encProg").val() != "" ) {
                        event.preventDefault();
                        comunica( "srv/ClasificacionFinanciamiento.php", {
                            metodoCveFin: "muestraCveFin"
                        });
                    } else {
                        notify("Debes seleccionar primero el Encuadre Programático", "warning");
                        setTimeout( function() { $( "#encProg" ).focus() }, 100 );
                    }
                } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                    notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                    e.preventDefault();
                    return false;
                }';
        }

        $html = '';
        if (in_array($prm['tipo'], $tipos)) {
            $cveFin = new stdClass();
            $cveFin->horizontal = 2;
            $cveFin->tipo = $prm['tipo'];
            $cveFin->ue_id = $prm['ue_id'];
            switch ($this->prm->tipo) {
                case 'E':
                    $cveFin->filtro = 'anticiposFondoRotatorio';
                    break;
                case 'D':
                    $cveFin->filtro = 'AcuerdosMinistracion';
                    break;
                case 'M':
                    $cveFin->filtro = 'Afectaciones';
                    break;
                case 'C':
                    $cveFin->filtro = 'PagosATerceros';
                    break;
                default:
                    $cveFin->filtro = 'Afectaciones';
                    break;
            }
            if ($prm['abc'] == 'c') {
                $cveFin->ecf_id = $prm['ecf_id'];
                $cveFin->lectura = true;
                $cveFin->selecciona = $prm['ecf_id'];
            }
            $html = ClasificacionFinanciamiento::creaComboCveFin($cveFin);
        } else {
            $progFin = new stdClass();

            $progFin->epg_id = $prm['epg_id'] ?? '';
            $progFin->ecf_id = $prm['ecf_id'] ?? '';
            $html = CatalogoProgramaticoFinanciamiento::ponF1($progFin);
        }

        return $html;
    }


    private function guardaLog($prm, $tipo)
    {
        /******************* LOG DE LAS AFECTACIONES************************************/
        if (!file_exists('./log/ur' . $this->gpo_id . $this->ur_id)) {
            mkdir('./log/ur' . $this->gpo_id . $this->ur_id, 0777, true);
        }

        $rutalog = '';
        switch ($tipo) {
            case 'afe':
                $rutalog = 'log/ur' . $this->gpo_id . $this->ur_id . '/abcAfectaciones';
                break;
            case 'pres':
                $rutalog = 'log/ur' . $this->gpo_id . $this->ur_id . '/abcPresupuestal';
                break;
            case 'cont':
                $rutalog = 'log/ur' . $this->gpo_id . $this->ur_id . '/abcContable';
                break;
        }
        $file = fopen($rutalog . '.log', 'a');
        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');
        $id = date('YmdHis');
        foreach ($prm as $field => $value) {
            if (!is_object($value)) {
                $tempdata .= '|' . $field . '=' . $value;
            }
        }

        $tempdata .= '|IPADDRESS=' . $this->ip;
        $tempdata .= '|USRID=' . $this->usr_id;

        fwrite($file, '[' . $id . ']-' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog . '.log');
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $rutamodificada = $rutalog . $hoy . '.log';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog . '.log', 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
    }

    private function guardaLogAutorizacion($datos)
    {
        if (!file_exists('./log/ur' . $this->gpo_id . $this->ur_id)) {
            mkdir('./log/ur' . $this->gpo_id . $this->ur_id, 0777, true);
        }

        /******************* LOG DE LAS AFECTACIONES************************************/
        $rutalog = 'log/ur' . $this->gpo_id . $this->ur_id . '/AutorizaAfe.log';
        $file = fopen($rutalog, 'a');
        $tempdata = $datos;
        $hoy = date('d/m/Y H:i:s');
        $hoy2 = date('YmdHis');
        $tempdata .= '|IPADDRESS=' . $this->ip;
        $tempdata .= '|USRID=' . $this->usr_id;

        fwrite($file, '[' . $hoy2 . ']' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog);
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $rutamodificada = 'log/ur' . $this->gpo_id . $this->ur_id . '/AutorizaAfe' . $hoy2 . '.log';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
        /******************* FIN DEL LOG************************************/
    }

    private function forbidden()
    {
        $html = <<<HTML
            <div class="alert alert-danger">
                El nivel del usuario no le permite manipular datos de las dependencias
            </div>
        HTML;
        $this->mixHtml($html);
        $this->agregaTools('');
    }
}

if (tienePermiso('S.2.1.1') && tienePermiso('S.2.1.2')) {
    // PERMISOS DEL USUARIO QUE PUEDE VER ESTE CONTROLADOR
    new ControladorAfectacion();
} else {
    WebResponse::sinPermisos();
}
