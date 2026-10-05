<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

/**
 * Autor @Eduardo Venegas              Fecha de Creación:  21/12/2018
 * Se actualizaron las varables de inicio de sesion de s18 a s19.
 * Se actualizaron las consultas por el modo estricto.
 * Se actualizaron referencias a tablas, por ejemplo, en lugar Bitacora2018 ahora se llama a Bitacora2019
 * Se agrego una condición a la consulta de CLC, ahora se incluye el ejercicio actual
- Ultima Fecha de actualización 28/02/19
 */

class ControladorConsultaClc extends WebResponse
{
    public $noavailable = [];
    public $urnoavalilable = [];
    public $ur;
    public $ejercicioActual;
    public $bxUr;
    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();

        $this->pantalla = 'scp/Consultas/ControladorConsultaClc.php';

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];
        $this->ur->select();

        if (isset($_SESSION[_S]['prm']['S']['5']['1']['1'])) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 2:case 3:case 4:case 5:case 6:case 7:

                    switch ($this->metodo) {
                        case 'llenaTablaClc':
                            $this->llenaTablaClc();
                            break;
                        case 'ConsultaPagadoras':
                            $this->ConsultaPagadoras();
                            break;
                        case 'download':
                            $this->download();
                            break;
                        default:$this->main();
                            break;
                    }

            }
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('');
        $this->mixScript('
            function validaTip(){
                var s = "";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                });
                if(s ==""){
                    return false
                }else return s;
            }
        ');

        $bxClc = new stdClass();
        $bxClc->id = 'bxClc';
        $bxClc->name = 'bxClc';
        $bxClc->label = 'Número de Clc';
        $bxClc->class = 'limpiar';
        $bxClc->placeholder = 'N° CLC';
        $bxClc->value = isset($this->prm->clc) ? $this->prm->clc : '';
        $bxClc->enDiv = true;

        $bxUr = new stdClass();
        $bxUr->id = 'bxUr';
        $bxUr->name = 'bxUr';
        $bxUr->label = 'UR';
        $bxUr->class = 'limpiar';
        $bxUr->placeholder = 'N° UR';
        $bxUr->value = isset($this->prm->gpour) ? $this->prm->gpour : '';
        $bxUr->enDiv = true;

        $cereco = new stdClass();
        $cereco->id = 'cereco';
        $cereco->name = 'cereco';
        $cereco->label = 'Cere(co)';
        $cereco->class = 'limpiar';
        $cereco->placeholder = 'N° Cere(co)';
        $cereco->value = isset($this->prm->clc_cereCo) ? $this->prm->clc_cereCo : '';
        $cereco->enDiv = true;

        $importe = new stdClass();
        $importe->id = 'importe';
        $importe->name = 'importe';
        $importe->label = 'Importe $';
        $importe->class = 'limpiar';
        $importe->placeholder = '$';
        $importe->enDiv = true;

        $bxEstado = new stdClass();
        $bxEstado->id = 'bxEstado';
        $bxEstado->width = '400px';
        $bxEstado->class = 'limpiar';
        $bxEstado->sql = '
            SELECT
                ecl_id,ecl_nombre
            FROM cat_EstadoClc
            WHERE ecl_id != 9
                AND ecl_id < 11
                OR ecl_id = 17
                OR ecl_id = 18
                OR ecl_id = 21
                OR ecl_id = 20';
        $bxEstado->label = 'SELECCIONA EL ESTADO DE LA CLC';
        $bxEstado->enRow = 1;
        if (isset($this->prm->ecl_id)) {
            $bxEstado->selecciona = $this->prm->ecl_id;
        }

        $combo = new stdClass();
        $combo->sql = '
            SELECT cfi_clave,cfi_nombre
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            INNER JOIN enc_ClaveFinanciamiento USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING( cff_id, fte_id, sft_id, ori_id, sor_id )
            WHERE ter_id="B" AND sts_id=1 ';

        $combo->label = 'BUSQUEDA DE CLC POR CLAVES DE FINANCIAMIENTO DE RECURSO FEDERAL';
        $combo->id = 'cfi_clave';
        $combo->name = 'cfi_clave[]';
        $combo->form = 'enviaPost';
        $combo->mensaje = false;
        $combo->enDiv = true;
        $combo->detonar = false;
        $combo->multiple = 'multiple';
        $comboClave = $this->ponCombo($combo);

        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->color = 'gris';
        $buscar->icon = 'fa fa-search me-1';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->color = 'amarillo';
        $clean->icon = 'fa fa-trash me-1';
        $wf = '';
        if ($_SESSION[_S]['ssc_id'] == 2) {
            $wf = 'where tcl_id in("F","G","H")';
        }
        $radios = '';
        $this->conectar();
        $this->setSql('
            SELECT tcl_id,tcl_nombre
            FROM cat_TipoClc
            ' . $wf . '
            GROUP BY tcl_id
            ORDER BY tcl_id;
        ');

        $tipo = isset($this->prm->tcl_id) ? $this->prm->tcl_id : '';

        $this->consultar();
        $this->desconectar();
        $ns = $this->getNumeroRegistros();
        if ($ns != 0) {
            $por = 100 / $ns;
        }

        $vars = $this->extraer();
        if (isset($this->prm->tipo)) {
            $tipo = $this->prm->tipo;
            $this->mixScript('show("' . $tipo . '","");');
        } else {
            $tipo = '';
        }

        $radios = '<form id="myForm"><div class="d-flex justify-content-start flex-nowrap tcl-tipos-v2 mb-4">';
        $checked = $extra = '';
        $ur_id = isset($this->prm->bxUr) ? substr($this->prm->bxUr, 1, 2) : '';
        $gpo_id = isset($this->prm->bxUr) ? substr($this->prm->bxUr, 0, 1) : '';
        do {
            if ($vars['tcl_id'] == $tipo) {
                $checked = 'tcl_ida';
            } elseif (in_array($vars['tcl_id'], $this->noavailable) && in_array($gpo_id . $ur_id, $this->urnoavalilable)) {
                $checked = 'tcl_idb';
            } else {
                $checked = 'tcl_idn';
            }
            $radios .= '<div name="tcl_id" tabindex="0" class="tcl_idfont ' . $checked . '" id="' . $vars['tcl_id'] . '" nombre="' . $vars['tcl_nombre'] . '">';
            $radios .= '<label>' . $vars['tcl_id'] . '</label>';
            $radios .= '<span class="tcl_nombre">' . $vars['tcl_nombre'] . '</span>';
            $radios .= $extra . '</div>';

        } while ($vars = $this->extraer());

        $radios .= '</div></form>';

        $radios .= '
            <div class="panel-body">
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-12 col-xs-12 mb-4">
                        ' . $comboClave . '
                    </fieldset>
                </div>
                <div class="row ">
                    <fieldset class="col-md-1 col-xs-1 mb-4">
                        ' . $this->ponInput($bxUr) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponInput($bxClc) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponInput($cereco) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponInput($importe) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->templateCombo($bxEstado) . '
                    </fieldset>
                </div>
                <div class="row justify-content-end">
                    <fieldset class="col-md-3 ">
                    ' . $this->ponBoton($buscar) . '   ' . $this->ponBoton($clean) . '
                    </fieldset>
                </div>
            </div>
            ';

        $scriptFecha = $this->getScript();

        $tabla = new stdClass();
        $tabla->id = 'tbl-ceres';
        $tabla->titulo = 'Consultar CLC';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->bxUr = '{$("#bxUr").val()}';
        $tabla->comunica->bxClc = '{$("#bxClc").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val()}';
        $tabla->comunica->importe = '{$("#importe").val()}';
        $tabla->comunica->tipo = '{validaTip()}';
        $tabla->comunica->bxEstado = '{$("#bxEstado").val()}';
        $tabla->numFilas = 150;
        $tabla->filasAmostrar = [10, 50, 100, 150];

        $tr = [];

        $tabla->accion = new stdClass();
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/rep_clc.php';
        $tabla->accion->excel = true;
        $tabla->accion->urlExcel = 'rep/excel/scp/afectaciones/repClc.php';
        //UR

        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        //CONCEPTO DE LA CLC
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        //FECHA
        $th = new stdClass();
        $th->texto = 'CERE(CO)';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NUMERO';
        array_push($tr, $th);
        //FECHA
        $th = new stdClass();
        $th->texto = 'FECHA GENE.';
        array_push($tr, $th);

        //TIPO DE CLC
        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        //SUBTIPO DE CLC
        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);

        //SUBTIPO DE CLC
        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        //Fecha de envio de clc a la tesoreria
        $th = new stdClass();
        $th->texto = 'FECHA ENVÍO TESORERÍA';
        array_push($tr, $th);

        //SUBTIPO DE CLC
        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE NETO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RE-IMPRESIÓN';
        $th->width = '60px';
        array_push($tr, $th);

        $trbody = [];

        /* UR*/
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-eye';
        $td->color = 'gris';
        $td->class = 'clcdetail';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->attr->tcl_nombre = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($trbody, $td);

        //numero de clc
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        //concepto
        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cereco';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);

        //fecha
        $td = new stdClass();
        $td->campoTxt = 'clc_generacionCereCo';
        array_push($trbody, $td);

        //tipo de clc
        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        //subtipo de clc
        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($trbody, $td);

        //Estado de la Clc
        $td = new stdClass();
        $td->campoTxt = 'eclnombre';
        array_push($trbody, $td);

        //fecha de envio a la tesoreria -- antes tenia el Lote
        $td = new stdClass();
        $td->campoTxt = 'FechaEnvioCLC';
        array_push($trbody, $td);

        //Estado de la Clc
        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-print';
        $td->color = 'gris';
        $td->class = 'reimprimir';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->attr->stc_id = 'stc_id';
        $td->attr->mpc_id = 'mpc_id';
        $td->attr->tcl_id = 'tcl_id';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->recargaCombo($bxEstado);

        $this->mixHtml(
            '<form  id="imprep" action="rep/doc/rep_clc.php" method="post" target="_blank">
                <input id="gpo_id" name="gpo_id" type="hidden" ">
                <input id="ur_id" name="ur_id" type="hidden" ">
                <input id="clc_id" name="clc_id" type="hidden" ">
            </form>'
        );

        $this->mixScript($scriptFecha . '

                function observarBotonAlertify() {
                    const observer = new MutationObserver((mutations, obs) => {
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-outline-success");
                            btnAceptar.textContent = "ACEPTAR";
                            obs.disconnect(); // deja de observar
                        }
                    });

                    // Observar el body o el contenedor de Alertify si lo conoces (como #alertify)
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                }

                $("#bxUr").blur(function(){
                    if( $("#bxUr").val().length < 3 ){
                        observarBotonAlertify(); // <- iniciar el observer antes
                        alertify.alert("Aviso", "Debes  escribir la UR con 3 digitos")
                        .set({ labels: { ok: "Aceptar" } });
                    }
                return false;
                });

                $("#bxClc").blur(function(){
                    if( $("#bxClc").val().length <1 ){
                        observarBotonAlertify(); // <- iniciar el observer antes
                        alertify.alert("Aviso", "Debes  escribir almenos  1 digito")
                        .set({ labels: { ok: "Aceptar" } });
                    }
                });
               /* const observer = new MutationObserver(() => {
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                        observer.disconnect(); // ya no observar
                    }
                }); */
                $("#buscar").click(function(){
                    var obj = {"metodo":"llenaTablaClc"};
                    obj.bxUr = $("#bxUr").val();
                    obj.bxClc = $("#bxClc").val();
                    obj.cfi_clave = $("#cfi_clave").val();
                    obj.bxEstado = $("#bxEstado").val();
                    obj.cereco = $("#cereco").val();
                    obj.importe = $("#importe").val();
                    obj.tipo=validaTip();
                    if(!comunica("' . $this->pantalla . '", obj,true ))
                    $(this).removeAttr("disabled");
                    return false;
                });

                function show(id,id_name){
                    if(!$("#"+id).hasClass("tcl_ida")){
                        $(".tcl_idfont").removeClass("tcl_ida");
                        $(".tcl_idfont").removeClass("loadingclas");
                        $(".tcl_idfont").addClass("tcl_idn");
                        $(".tcl_idfont").removeAttr("checked");
                        $("#"+ id).removeClass("tcl_idn");
                        $("#"+id).addClass("loadingclas");
                        if (comunica("' . $this->pantalla . '",{metodo:"llenaTablaClc",tipo:id,nombre:id_name},false,false)){
                            $("#"+id).addClass("tcl_ida");
                        }
                    }
                }

                $(".tcl_idfont").click(function(){
                    if($(this).hasClass("tcl_idb")){	notify("Este tipo de CLC no esta disponible","error");	}
                    else{	show($(this).attr("id"),$(this).attr("nombre"));	}
                });

                $("#clean").click(function(){
                    $(".rad_afectacion:checked").removeAttr("checked");
                    $(".limpiar").val("");
                    comunica("scp/Consultas/ControladorConsultaClc.php",{
                        metodo:"main",
                        });
                    return false;
                    });
            ');

        if ($tipo != '') {
            $this->mixScript('show("' . $tipo . '");');
        }

    }

    private function llenaTablaClc()
    {
        $script = $importes = $ur = $gpo_id = '';
        $wf = '';
        $parametros = [];
        if (isset($this->prm->tipo) and $this->prm->tipo != '') {
            $wf .= ($wf == '') ? 'where tcl_id= :tcl_id ' : ' and tcl_id= :tcl_id';
            $parametros += [
                'tcl_id' => $this->prm->tipo,
            ];
            $script .= ',tcl_id:"' . $this->prm->tipo . '"';
        }
        if (isset($this->prm->cereco) and $this->prm->cereco != '') {
            $wf .= ($wf == '') ? 'where clc_cereCo= :clc_cereCo ' : ' and clc_cereCo= :clc_cereCo ';
            $parametros += [
                'clc_cereCo' => $this->prm->cereco,
            ];
            $script .= ',clc_cereCo:"' . $this->prm->cereco . '"';
        }
        if (isset($this->prm->bxUr) and $this->prm->bxUr != '') {
            $ur = substr($this->prm->bxUr, 1, 2);
            $gpo_id = substr($this->prm->bxUr, 0, 1);
            $wf .= ($wf == '') ? 'where gpo_id= :gpo_id and  ur_id= :ur_id' : ' and gpo_id= :gpo_id and  ur_id= :ur_id ';
            $parametros += [
                'gpo_id' => $gpo_id,
                'ur_id'  => $ur,
            ];
            $script .= ',gpour:"' . $gpo_id . $ur . '"';
        }
        if (isset($this->prm->bxClc) and $this->prm->bxClc != '') {
            $wf .= ($wf == '') ?
            ($this->prm->bxClc < 10 ? 'where clc_id= :clc_id ' : 'where clc_id = :clc_id ') :
            ($this->prm->bxClc < 10 ? ' and clc_id = :clc_id ' : ' AND clc_id = :clc_id ');
            $parametros += [
                'clc_id' => $this->prm->bxClc,
            ];
            $script .= ',clc:"' . $this->prm->bxClc . '"';
        }
        if (isset($this->prm->bxEstado) and $this->prm->bxEstado != '') {
            $wf .= ($wf == '') ? 'where ecl_id= :ecl_id' : ' and ecl_id= :ecl_id ';
            $parametros += [
                'ecl_id' => $this->prm->bxEstado,
            ];
            $script .= ',ecl_id:"' . $this->prm->bxEstado . '"';
        }
        if (isset($this->prm->gpo_id) and $this->prm->gpo_id != '') {
            $wf .= ($wf == '') ? 'where gpo_id= :gpo_id ' : ' and gpo_id= :gpo_id ';
            $parametros += [
                'gpo_id' => $this->prm->gpo_id,
            ];
        }
        if (isset($this->prm->ur_id) and $this->prm->ur_id != '') {
            $wf .= ($wf == '') ? 'WHERE  ur_id= :ur_id ' : ' AND  ur_id= :ur_id ';
            $parametros += [
                'ur_id' => $this->prm->ur_id,
            ];
        }
        if (isset($this->prm->cfi_clave) > 0 && $this->prm->cfi_clave != '') {
            foreach ($this->prm->cfi_clave as $key => $val) {
                $in = '';
                $in .= '"' . $val . '",';
            }
            $in = trim($in, ',');
            if ($in != '') {
                $wf .= ($wf == '') ? 'WHERE ecf_clave in (' . $in . ') ' : ' AND  ecf_clave in (' . $in . ') ';
            }
        }

        if (isset($this->prm->importe) and $this->prm->importe != '') {
            $importes = ' HAVING importe like "%' . $this->prm->importe . '%" ';
        }

        $wf .= ($wf == '') ? 'WHERE clc_id != 0   ' : ' AND clc_id != 0 ';

        $this->conectar();
        $sql = '
        SELECT gpo_id,ur_id,clc_id,clc_concepto,DATE_FORMAT( clc_generacionCereCo,"%d/%m/%Y") clc_generacionCereCo,tcl_nombre,stc_nombre,
        CASE
            WHEN ecl_id between 1 and 9 THEN ecl_nombre
            WHEN clc_recursoFederal=2 AND ecl_id=17 THEN "PAGO TRANSFERIDO"
            WHEN clc_recursoFederal=2 AND ecl_id=18 THEN "CERE O CERECO PAGADA"
            WHEN clc_recursoFederal=1 AND ecl_id=17 THEN "RECURSO FEDERAL TRANSFERIDO"
            WHEN clc_recursoFederal=1 AND ecl_id=18 THEN "RECURSO FEDERAL PAGADO"
            WHEN ecl_id between 10 and 16 THEN "ENVIADO A TESORERÍA"
            WHEN ecl_id=21 THEN "CLC CANCELADA"
            WHEN ecl_id=20 THEN "CLC INHABILITADA"
        END AS eclnombre, ecl_id,
        if(clc_tipocereCo=1 AND clc_generacionCereCo IS NOT NULL,"CERE",
        if( clc_tipocereCo=2  AND clc_generacionCereCo IS NOT NULL, "CERECO","SIN CERE(CO)"))cereco,
        if(clc_generacionCereCo IS  NOT  NULL,clc_cereCo,"")clc_cereCo,
        DATE_FORMAT( tes.lot_fechaPago,"%d/%m/%Y") lot_fechaPago,
        DATE_FORMAT(t.FechaEnvioCLC,"%d/%m/%Y") FechaEnvioCLC,
        "" as lote,
        TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) as importe
        FROM scp_Clc
        LEFT JOIN (SELECT gpo_id, ur_id, clc_id, ecl_id, MAX(usr_fecha) FechaEnvioCLC
            FROM ' . BD_BITACORA . '.bit_Clc WHERE ecl_id = 10 ' . ($ur != '' ? ' AND gpo_id= :gpo_id AND ur_id= :ur_id' : ' ') .
        ' GROUP BY gpo_id, ur_id, clc_id, ecl_id)t USING (gpo_id, ur_id, clc_id, ecl_id)
        LEFT JOIN scp_ClcAuxiliar b USING (gpo_id,ur_id,clc_id,ecl_id)
        LEFT JOIN (
            SELECT lot_fechaPago, gpo_id, ur_id, clc_id FROM ' . BD_TESORERIA . '.lot_LoteClc
                INNER JOIN ' . BD_TESORERIA . '.lot_Lote USING(fpa_id, msc_id,lot_ejercicio, lot_id )
                -- Se agrega para que solo se muestre el ejercicio actual
                WHERE lot_ejercicio = ' . $this->ejercicioActual . '
                ) AS tes USING (gpo_id,ur_id,clc_id)
        INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
        INNER JOIN cat_TipoClc USING(tcl_id)
        INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
        INNER JOIN cat_ModoPagoClc USING(mpc_id)
        INNER JOIN cat_EstadoClc USING(ecl_id)
        ' . $wf . '
        ' . $importes . '
        ';

        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ceres';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 150;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros.","warning");
            ');
        }

        $this->mixScript('
                $("#btnregresarindex").click(function(){
                    document.location.href = "../sinpres2018";
                });

                $(".reimprimir").click(function(){
                    $("#gpo_id").val( $(this).attr("gpo_id"));
                    $("#ur_id").val( $(this).attr("ur_id"));
                    $("#clc_id").val( $(this).attr("clc_id"));

                    alertify.prompt(
                        "<h4><font color=\"red\">AVISO</font></h4>",
                        "<h5>Ingresa la fecha y hora de impresion original<br/> Escribe con el siguiente formato<br/> <strong>dd/mm/yy - 00:00:00</strong> <br/></h5>",
                        "",
                        function(evt, palabra) {
                            if (palabra) {
                                if (palabra.length === 19) {
                                    if (palabra.substring(6, 8) === "25") {
                                        $("#imprep").append($("<input/>").attr("id", "reimprecion").attr("name", "reimprimir").val(palabra));
                                        $("#imprep").submit();
                                        $("#reimprecion").remove();
                                    } else {
                                        notify("El año es incorrecto, solo se permite ' . APP_EJERCICIO . '", "warning");
                                    }
                                } else {
                                    $("#imprep").append($("<input/>").attr("id", "reimprecion").attr("name", "reimprimir").val(""));
                                    $("#imprep").submit();
                                    $("#reimprecion").remove();
                                }
                            }
                        },
                        function() {},
                        ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });

                const observer = new MutationObserver(() => {
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-success");
                        observer.disconnect(); // ya no observar
                    }
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                });
      
                $(".clcdetail").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"ConsultaPagadoras",
                        gpo_id:$(this).attr("gpo_id"),
                        ur_id:$(this).attr("ur_id"),
                        clc_id:$(this).attr("clc_id"),
                        tcl_nombre:$(this).attr("tcl_nombre")
                        ' . $script . '
                    });
                }
                );
            ');
    }

    private function ConsultaPagadoras()
    {
        /**
         *  Autor: Eduardo Venegas
         *  Descripcion: Se actualizo la consulta por lo de modo estricto.
         */

        $parametros = [];
        $sql = ' SELECT tcl_id, SUM(IF(ncc_amortizado=1,1,0)) AS o, SUM(IF(ncc_nccamortiza>0,1,0) )AS p, sc.ncc_gpo, sc.ncc_ur, sc.ncc_clc';
        $sql .= ' FROM scp_Contable sc INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id)';
        $sql .= ' WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND clc_id = :clc_id GROUP BY sc.ncc_gpo,sc.ncc_ur,sc.ncc_clc;';
        $parametros = [
            'gpo_id' => $this->prm->gpo_id,
            'ur_id'  => $this->prm->ur_id,
            'clc_id' => $this->prm->clc_id,
        ];

        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar($parametros);
        $d = $c->extraer();

        $c->desconectar();
        unset($c);
        $titulo = 'CLC NORMAL (NI PAGADORA NI DE ORIGEN)';
        $t = 'N';
        if (!empty($d)) {
            if ($d['o'] == 0 && $d['p'] == 0) {
                $titulo = 'CLC NORMAL (NI PAGADORA NI DE ORIGEN)';
                $t = 'N';
            }
            if ($d['o'] > 0 && $d['p'] == 0) {
                $titulo = 'CLC DE ORIGEN';
                $t = 'O';
            }
            if ($d['o'] == 0 && $d['p'] > 0) {
                $titulo = 'CLC PAGADORA';
                $t = 'P';
            }
            if ($d['o'] > 0 && $d['p'] > 0) {
                $titulo = 'ERROR EN LA CLC ESTA MARCADA COMO ORIGEN Y PAGADORA';
                $t = 'E';
            }
        }

        if (!isset($this->prm->tcl_id)) {
            if (!empty($d)) {
                $this->prm->tcl_id = $d['tcl_id'];
            } else {
                switch ($this->prm->tcl_nombre) {
                    case 'SERVICIOS PERSONALES':
                        $this->prm->tcl_id = 'A';
                        break;
                    case 'GASTOS DE OPERACIÓN':
                        $this->prm->tcl_id = 'B';
                        break;
                    case 'PAGOS A TERCEROS':
                        $this->prm->tcl_id = 'C';
                        break;
                    case 'ACUERDOS DE MINISTRACIÓN':
                        $this->prm->tcl_id = 'D';
                        break;
                    case 'ANTICIPOS - FONDO ROTATORIO':
                        $this->prm->tcl_id = 'E';
                        break;
                    case 'CONTRATO':
                        $this->prm->tcl_id = 'F';
                        break;
                    case 'ADMINISTRACIÓN DIRECTA':
                        $this->prm->tcl_id = 'G';
                        break;
                    case 'ANTICIPO DE OBRA':
                        $this->prm->tcl_id = 'H';
                        break;
                    case 'MUNICIPIOS':
                        $this->prm->tcl_id = 'I';
                        break;
                    case 'DEUDA PÚBLICA':
                        $this->prm->tcl_id = 'J';
                        break;
                    case 'BURSATILIZACIÓN':
                        $this->prm->tcl_id = 'K';
                        break;
                    case 'TRANSFERENCIAS':
                        $this->prm->tcl_id = 'L';
                        break;
                    case 'PAGOS A  TERCEROS (OTROS)':
                        $this->prm->tcl_id = 'M';
                        break;
                }
            }
        }
        $sql = '';
        $encabezado = '';

        switch ($t) {
            case 'N':
                $encabezado = '
                    <tr>
                        <th class="warning" colspan="9">Numeros de Control </th>
                    </tr>';
                $sql = '
                    SELECT gpo_id,ur_id,clc_id,ncc_id,
                    CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id) AS cta1,';
                $sql .= '
                    ncc_percepcion AS per1, ncc_retencion AS ret1,	ncc_tipoMovimiento as mov1,	rfc_id AS rfc1,	ncc_referencia as ref1 ';
                $sql .= 'FROM scp_Contable';
                $sql .= ' WHERE gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND clc_id = :clc_id';
                break;
            case 'O':
                $encabezado = '
                    <tr>
                        <th class="warning" colspan="9">Numero de Control Origen (CLC ACTUAL)</th>
                        <th class="warning" colspan="9">CLC PAGADORA</th>
                    </tr>';
                $sql = '
                    SELECT gpo_id,ur_id,clc_id,ncc_id,
                    CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".", cta_ue_id,".",cgc_id,".",cta_id) AS cta1, ncc_percepcion AS per1, ncc_retencion AS ret1,
                    ncc_tipoMovimiento as mov1,	rfc_id AS rfc1,	ncc_referencia as ref1,urp AS ur2,clcp AS clc2 ,nccp as ncc2,
                    cta_pagadora AS cta2,percepcionp as per2,retencionp AS ret2,movp AS mov2,rfcp as rfc2,referenciap AS ref2
                    FROM scp_Contable  #ORIGEN
                    LEFT JOIN
                        (SELECT ncc_gpo AS gpo_id,ncc_ur AS ur_id,ncc_clc AS clc_id, ncc_nccamortiza  AS ncc_id,
                            ncc_id AS nccp,	rfc_id AS rfcp,	ncc_referencia AS referenciap, ncc_percepcion AS percepcionp,
                            ncc_retencion AS retencionp,ncc_tipoMovimiento AS movp,	CONCAT(gpo_id,ur_id) AS urp,clc_id AS clcp,
                            CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",
                            cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id)  AS cta_pagadora
                        FROM scp_Contable
                        WHERE ncc_gpo = :gpo_id AND ncc_ur = :ur_id
                        ) AS PAGADORA USING(gpo_id,ur_id,clc_id,ncc_id)
                        WHERE  gpo_id = :gpo_id AND ur_id = :ur_id AND clc_id = :clc_id;';
                break;
            case 'P':
                $encabezado = '
                    <tr>
                        <th class="warning" colspan="9">Numero de Control Pagador (CLC ACTUAL)</th>
                        <th class="warning" colspan="9">CLC ORIGEN</th>
                    </tr>';
                $sql = '
                    SELECT gpo_id,ur_id,clc_id,ncc_id,
                    CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id) AS cta1,
                    ncc_percepcion AS per1,ncc_retencion AS ret1 ,ncc_tipoMovimiento as mov1,rfc_id as rfc1 ,ncc_referencia as ref1,
                    CONCAT(ncc_gpo,LPAD(ncc_ur,2,0)) AS ur2,ncc_clc  as clc2,ncc_nccamortiza as ncc2,ctaorigen AS cta2,
                    percepciono as per2,retenciono as ret2,movo as mov2,rfco as rfc2,referenciao as ref2
                    FROM scp_Contable
                    LEFT JOIN (
                        SELECT gpo_id  AS ncc_gpo,ur_id  AS ncc_ur,clc_id AS ncc_clc,ncc_id AS ncc_nccamortiza,rfc_id AS rfco,
                        ncc_percepcion AS percepciono, ncc_retencion AS retenciono,ncc_tipoMovimiento AS movo,ncc_referencia AS referenciao,
                        CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id) AS ctaorigen
                        FROM scp_Contable
                        ) B USING(ncc_gpo,ncc_ur,ncc_clc,ncc_nccamortiza)
                    WHERE gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND clc_id =:clc_id';
                break;
        }

        //ve/descarga el oficio de cancelación de la CLC
        $file = 'OficiosCancelacionCLC/' . $this->prm->gpo_id . '-' . str_pad($this->prm->ur_id, 2, 0, STR_PAD_LEFT) . '-' . $this->prm->clc_id . '.pdf';
        $ofc = '';
        $params = [];
        $estadoClc = '
            SELECT ecl_id FROM scp_Clc
            INNER JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND clc_id = :clc_id AND ecl_id = 15;'
        ;
        $params = [
            'gpo_id' => $this->prm->gpo_id,
            'ur_id'  => $this->prm->ur_id,
            'clc_id' => $this->prm->clc_id,
        ];
        $this->conectar();
        $this->setSql($estadoClc);
        $this->consultar($params);
        $this->desconectar();
        // Si la CLC esta cancelada
        if ($this->getNumeroRegistros() > 0) {
            if (!file_exists($file)) {
                // Si no existe el oficio de cancelación especifico de la CLC pagadora, consultamos la CLC de origen cersiorandonos que tambien se encuentre cancelada.
                if ($t == 'P') {
                    $sqlClc = '
                        SELECT ecl_id FROM scp_Clc
                        INNER JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND clc_id = :clc_id;'
                    ;
                    $parametrosqlClc = [
                        'gpo_id' => $d['ncc_gpo'],
                        'ur_id'  => $d['ncc_ur'],
                        'clc_id' => $d['ncc_clc'],
                    ];
                    $this->conectar();
                    $this->setSql($sqlClc);
                    $this->consultar($parametrosqlClc);
                    $this->desconectar();
                    $ecl = $this->extraer();

                    $sqlClcP = '
                        SELECT ecl_id FROM scp_Clc
                        INNER JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND clc_id = :clc_id;'
                    ;
                    $parametroClcp = [
                        'gpo_id' => $this->prm->gpo_id,
                        'ur_id'  => $this->prm->ur_id,
                        'clc_id' => $this->prm->clc_id,
                    ];
                    $this->conectar();
                    $this->setSql($sqlClcP);
                    $this->consultar($parametroClcp);
                    $this->desconectar();
                    $eclP = $this->extraer();

                    // Si la clc es pagadora y esta cancelada, y además la clc de origen esta cancelada, se mostrará el oficio de cancelación de la clc de origen
                    if ($eclP['ecl_id'] == 15 && $ecl['ecl_id'] == 15) {
                        $file = 'OficiosCancelacionCLC/' . $d['ncc_gpo'] . '-' . str_pad($d['ncc_ur'], 2, 0, STR_PAD_LEFT) . '-' . $d['ncc_clc'] . '.pdf';
                        $this->prm->gpo_id = $d['ncc_gpo'];
                        $this->prm->ur_id = $d['ncc_ur'];
                        $this->prm->clc_id = $d['ncc_clc'];
                    } else {
                        $this->mixScript('
                                notify("El oficio de cancelación no existe","warning");
                            ');
                    }

                } else {
                    $this->mixScript('
                            notify("El oficio de cancelación no existe","warning");
                        ');
                }
            }
            // Si existe el archivo se procede a mostrarlo
            if (file_exists($file)) {
                $ofc .= '
                    <div class="archivo" onClick="downloadfile(\'' . $this->prm->gpo_id . '-' . $this->prm->ur_id . '-' . $this->prm->clc_id . '\',\'v\')" tor="fil">Ver oficio de cancelación</div>
                ';
            }

        }

        $html = '<legend class="mb-4">' . $titulo . '<div style="color:red; font-size:12px;">' . $ofc . '</div></legend>';
        $html .= '<p>SOLO SE MUESTRA INFORMACIÓN DE NUMEROS DE CONTROL CONTABLE, SI ES PAGADORA SE MOSTRARÁ SU DETALLE DE ORIGEN, SI ES DE ORIGEN SE MOSTRARÁ SU DETALLE PAGADOR</p>';
        $html .= '<table class="table table-striped table-bordered">';
        $html .= $encabezado;
        if ($t == 'N') {
            $html .= '<tr><th>UR</th><th>CLC</th><th>N° Control</th><th>Cta.Contable</th><th>Percepción</th><th>Retención</th><th>+/-</th>';
            $html .= '<th>RFC</th><th>Referencia</th>';
        } else {
            $html .= '<tr><th>UR</th><th>CLC</th><th>N° Control</th><th>Cta.Contable</th><th>Percepción</th><th>Retención</th><th>+/-</th>';
            $html .= '<th>RFC</th><th>Referencia</th>';
            $html .= '<th>UR</th><th>CLC</th><th>N° Control</th><th>Cta.Contable</th><th>Percepción</th><th>Retención</th><th>+/-</th>';
            $html .= '<th>RFC</th><th>Referencia</th></tr>';
        }

        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar($parametros);
        $d = $c->extraer();
        while ($d = $c->extraer()) {
            if ($t == 'N') {
                $html .= '<tr><td>' . $d['gpo_id'] . $d['ur_id'] . '</td><td>' . $d['clc_id'] . '</td><td>' . $d['ncc_id'] . '</td><td>' . $d['cta1'] . '</td>';
                $html .= '<td>' . $d['per1'] . '</td><td>' . $d['ret1'] . '</td><td>' . $d['mov1'] . '</td>';
                $html .= '<td>' . $d['rfc1'] . '</td><td>' . $d['ref1'] . '</td></tr>';
            } else {
                $html .= '<tr><td>' . $d['gpo_id'] . $d['ur_id'] . '</td><td>' . $d['clc_id'] . '</td><td>' . $d['ncc_id'] . '</td><td>' . $d['cta1'] . '</td>';
                $html .= '<td>' . $d['per1'] . '</td><td>' . $d['ret1'] . '</td><td>' . $d['mov1'] . '</td>';
                $html .= '<td>' . $d['rfc1'] . '</td><td>' . $d['ref1'] . '</td>';
                $html .= '<td>' . $d['ur2'] . '</td><td>' . $d['clc2'] . '</td><td>' . $d['ncc2'] . '</td><td>' . $d['cta2'] . '</td>';
                $html .= '<td>' . $d['per2'] . '</td><td>' . $d['ret2'] . '</td><td>' . $d['mov2'] . '</td>';
                $html .= '<td>' . $d['rfc2'] . '</td><td>' . $d['ref2'] . '</td></tr>';
            }
        }
        $html .= '</table>';

        $this->mixHtml($html);
        $c->desconectar();
        unset($c);
        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->class = 'me-1';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->icon = ' fa-solid fa-circle-left me-2';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $sc = '';
        if (isset($this->prm->gpour)) {
            $sc .= ',gpour:"' . $this->prm->gpour . '"';
        }
        if (isset($this->prm->clc)) {
            $sc .= ',clc:"' . $this->prm->clc . '"';
        }
        if (isset($this->prm->clc_cereCo)) {
            $sc .= ',clc_cereCo:"' . $this->prm->clc_cereCo . '"';
        }
        if (isset($this->prm->ecl_id)) {
            $sc .= ',ecl_id:"' . $this->prm->ecl_id . '"';
        }

        $this->mixScript('
            $("#btnRegresarIndex").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    tcl_id:"' . $this->prm->tcl_id . '"
                    ' . $sc . '
                });
            });

            function verpdf(n,t){
                $("#verpdf").remove();
                if(t=="v" ) {
                    n   = $("<iframe/>").attr("src","scp/download_file.php?tmp=0&t=v&file="+ n ).css("width","96%").css("height","100%");
                    c   = $("<span/>").addClass("glyphicon glyphicon-remove-circle").css("float","right").css("font-size","30px").css("cursor","pointer").click(function(){ $("#verpdf").remove(); });
                    m   = $("<div/>").addClass("documento_fs").css("color","#FFF").attr("id","verpdf").append(c).append(n).appendTo("body");
                }
            }

            function downloadfile(data,e){
                comunica("' . $this->pantalla . '",{metodo:"download",x:data,t:e});
            }
        ');

    }

    private function download()
    {
        if (!isset($this->prm->x)) {
            $this->mixScript('notify("ocurrio un error al obtener el archivo","error");');
            return false;
        }

        $otro = 'OficiosCancelacionCLC/' . $this->prm->x . '.pdf';

        if (!file_exists($otro)) {
            $this->mixScript('notify("Ocurrio un error al obtener el archivo- El archivo no existe","error");');
            return false;
        }

        $nombre = 'OficiosCancelacionCLC/' . $this->prm->x . '.pdf';
        $this->mixScript('verpdf("' . $nombre . '","' . $this->prm->t . '");');

    }
}
if (isset($_SESSION[_S]['prm']['S']['5']['1']['1'])) {
    // PERMISOS DEL USUARIO QUE PUEDE VER ESTE CONTROLADOR
    new ControladorConsultaClc();
} else {
    WebResponse::sinPermisos();
}
