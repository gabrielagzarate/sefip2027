<?php
//inicio de sesion y llamada a las librerias
/*$pathInPieces = explode(DIRECTORY_SEPARATOR , __FILE__);
require_once $_SERVER["DOCUMENT_ROOT"] . "/" . $pathInPieces[3] . "/lib/constantes.php"; */
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

class ControladorListadoRectificaciones extends WebResponse
{

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        /*
            @Jordy 8-10-2018
            Se cotejaron los niveles de usuario para este controloador con Israel
            me comento que este controlador es para todos los niveles.
        */

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                switch ($this->metodo) {
                    case 'llenaTablaMain':
                        $this->llenaTablaMain();
                        break;
                    case 'llenaTablaRct':
                        $this->llenaTablaRct();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $tipoRct = new StdClass();
        $tipoRct->class = "radio habilitar";
        $tipoRct->name = "tipoRct";
        $tipoRct->values = array(1 => "REINTEGRO", 2 => "SIN REINTEGRO");
        $tipoRct->cols = 3;
        $tipoRct->required = true;
        $this->ponRadio($tipoRct);

        $meses = new stdClass();
        $meses->id = 'meses';
        $meses->name = 'meses';
        $meses->width = "400px";
        $meses->required = true;
        $meses->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        $meses->enRow = 1;

        $ur_id        = new stdClass();
        $ur_id->id    = 'ur_id';
        $ur_id->label = 'Numero de UR';
        $ur_id->enDiv    = true;
        $ur_id->maxlength = 6;
        $this->ponInput($ur_id);

        #-----
        $reimprimir = new stdClass();
        $reimprimir->value = ' Reimprimir';
        $reimprimir->id = 'reimprimir';
        $reimprimir->class = 'btn btn-success';
        $reimprimir->name = 'reimprimir';
        $reimprimir->icon = 'download';
        $reimprimir = $this->ponBoton($reimprimir);

        $exportar = new stdClass();
        $exportar->value = '&nbsp;&nbsp;Exportar';
        $exportar->id = 'exportar';
        $exportar->class = 'btn btn-warning';
        $exportar->name = 'exportar';
        $exportar->icon = 'plus';
        $exportar = $this->ponBoton($exportar);
        #-----

        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->color = 'azul';
        $buscar->icon = 'search';

        $busqueda2 = '';
        $paso = false;

        #----
        $busqueda2 = '
            <fieldset class="col-md-1 col-xs-1">
                ' . $this->ponInput($ur_id) . '         
            </fieldset>
        ';
        #---

        $radios = '';
        $radios .= '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4">
                    <legend>Tipo de Lista a consultar</legend>
                        ' . $this->ponRadio($tipoRct) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3"><br/>
                    <legend>Mes de la lista</legend>
                        ' . $this->ponCombo($meses) . '         
                    </fieldset>
                    ' . $busqueda2 . '
                    <fieldset class="col-md-4 col-xs-4">
                    <br/><br/><br/><br/>
                    ' . $this->ponBoton($buscar) . ' ' . $reimprimir . ' ' . $exportar . '          
                    </fieldset>
                </div>      
            </form>
        ';
        //$nav->nf = isset($this->prm->nav->nf)  ? $this->prm->nav->nf : 5 ;    
        $tabla = new stdClass();
        $tabla->id = 'tbl-Listado';
        $tabla->titulo = 'Listado de  Rectificaciones';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMain';
        $tabla->comunica->tipoRct = '{$("input[name=tipoRct]:checked").val()}';
        $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        $tabla->comunica->meses = '{$("#meses").val()}';


        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'RECTIFICACIÓN';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        //CONCEPTO DE LA CLC
        $th = new stdClass();
        $th->texto = 'JUSTIFICACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        //FECHA
        $th = new stdClass();
        $th->texto = 'FECHA APLICACIÓN.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $trbody = array();

        /* UR*/
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = array('gpo_id', 'ur_id');
        array_push($trbody, $td);

        //numero de clc
        $td = new stdClass();
        $td->campoTxt = 'rct_id';
        array_push($trbody, $td);

        //numero de clc
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        //concepto
        $td = new stdClass();
        $td->campoTxt = 'rct_justificacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_fechaAutorizacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);


        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->recargaCombo($meses);

        $validarur = $envio = '';
        $envio = 'obj.ur_id=$("#ur_id").val();';

        $this->mixScript('
            $("#buscar").click(function(){  
                ' . $validarur . '
                var obj = {"metodo":"llenaTablaMain"};
                obj.tipoRct = $("input[name=tipoRct]:checked").val();
                obj.meses = $("#meses").val();
                ' . $envio . '
                console.log(obj);
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled"); 
                return false;   
            });
        ');
    }

    private function llenaTablaMain()
    {
        $wf = '';
        if (isset($this->prm->tipoRct) && $this->prm->tipoRct != '') {
            switch ($this->prm->tipoRct) {
                case 1:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro = 1' : ' AND  rct_reintegro = 1 ';
                    break;
                case 2:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro IN (2,3)' : ' AND  rct_reintegro IN (2,3) ';
                    break;
            }
        }

        if (isset($this->prm->ur_id) && $this->prm->ur_id <> "") {
            $wf .= ' AND  concat(gpo_id,ur_id) = ("' . $this->prm->ur_id . '") ';
        }

        $this->conectar();

        $sql = '
            SELECT 
                gpo_id,
                ur_id,
                clc_id,
                tcl_id,
                tcl_nombre,
                rct_id,
                rct_justificacion,
                rct_reintegro,
                DATE_FORMAT(rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
                ecl_nombre,
                rct_importe,
                lr.lrct_listado_id AS lrct_listado_id
            FROM scp_MaestroRectificacion a
                INNER JOIN scp_ListadoRectificacion lr USING (gpo_id, ur_id, rct_id)
                INNER JOIN scp_Clc b USING (gpo_id,ur_id,clc_id)
                INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
                INNER JOIN cat_TipoClc USING (tcl_id)
                INNER JOIN cat_EstadoClc e ON a.ecl_id = e.ecl_id
            ' . $wf . '
               AND (a.ecl_id=6 AND b.ecl_id=18 and clc_recursoFederal=2)
               AND lr.lrct_mes=' . $this->prm->meses . '  
            GROUP BY gpo_id,ur_id,rct_id,clc_id
        ';

        $this->setSql($sql);

        //echo $this->getSql();
        //die();
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Listado';
        $nav->nf = 5;

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("NO HAY REGISTROS","error",true);');
        }

        $this->mixScript('
            $("#reimprimir").unbind( "click" );
            $("#reimprimir").click(function(){
                var form = $("<form>")
                .attr("action", "rep/doc/scp/consultas/RepConsultaListadoRectificaciones.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "tipoRct").val( "' . $this->prm->tipoRct . '"))
                ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ?
            '.append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( "' . $this->prm->ur_id . '"))' : '') . '
                .append($("<input>").attr("type", "hidden").attr("name", "meses").val("' . $this->prm->meses . '" ));
                if( $(this).is(".impresion"))
                    form.append($("<input>").attr("type", "hidden").attr("name", "listado").val( $(this).attr("lrct_listado_id") ));
                $("body").append(form);
                form.submit();
                form.remove();
            });
            $("#exportar").unbind( "click" );
            $("#exportar").click(function(){
                var form = $("<form>")
                .attr("action", "rep/excel/scp/consultas/RepConsultaListadoRectificaciones.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "tipoRct").val( "' . $this->prm->tipoRct . '"))
                ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ?
            '.append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( "' . $this->prm->ur_id . '"))' : '') . '
                .append($("<input>").attr("type", "hidden").attr("name", "meses").val("' . $this->prm->meses . '" ));
                if( $(this).is(".exportar"))
                form.append($("<input>").attr("type", "hidden").attr("name", "listado").val( $(this).attr("lrct_listado_id") ));                 
                $("body").append(form);
                form.submit();
                form.remove();
            }); 

        ');
    }

    /**METODO EN DESUSO */
    /*private function llenaTablaRct()
    {
        echo var_dump($this->prm);
        die();
        $wf = '';
        if (isset($this->prm->tipoRct) && $this->prm->tipoRct != '') {
            switch ($this->prm->tipoRct) {
                case 1:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro = 1' : ' AND  rct_reintegro = 1 ';
                    break;
                case 2:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro IN (2,3)' : ' AND  rct_reintegro IN (2,3) ';
                    break;
            }

            if (isset($this->prm->fechacreacion) && $this->prm->fechacreacion <> "") {
                $wf .= ' AND  month(rct_fechaAutorizacion) = "' . $this->prm->fechacreacion . '"  ';
            }

            if (isset($this->prm->ur_id) && $this->prm->ur_id <> "") {
                $wf .= ' AND  concat(gpo_id,ur_id) = ("' . $this->prm->ur_id . '") ';
            }
        }

        $this->conectar();
        $sql = '
            SELECT 
                gpo_id,
                ur_id,
                clc_id,
                tcl_id,
                tcl_nombre,
                rct_id,
                rct_justificacion,
                rct_reintegro,
                DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
                a.ecl_id,
                ecl_nombre,
                rct_importe
            FROM scp_MaestroRectificacion a
                INNER JOIN scp_ListadoRectificacion lr USING (gpo_id, ur_id, rct_id)
                INNER JOIN scp_Clc b USING(gpo_id,ur_id,clc_id)
                INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
                INNER JOIN cat_TipoClc using (tcl_id)
                INNER JOIN cat_EstadoClc e  on  a.ecl_id = e.ecl_id
            ' . $wf . '
                AND (a.ecl_id = 6 AND b.ecl_id = 18 and b.clc_recursoFederal = 2)
                AND lr.lrct_mes = ' . $this->prm->meses . '  
            group by gpo_id,ur_id,rct_id,clc_id
        ';
        echo $this->prm->nav->nf;
        die();
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf)  ? $this->prm->nav->nf : 5;
        $nav->idTabla = 'tbl-Listado';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("NO HAY REGISTROS","error",true);
            ');
        }


        $this->desconectar();

        //$this->mixScript('0
            //$("#btnregresarindex").click(function(){                
            //    document.location.href = "../sinpres2018";
            //});
        //'); 
    }*/
}

if (isset($_SESSION[_S]['s24-prm']['S']['5']['27']['55']) && ($_SESSION[_S]['s24-gpo'] = '1') && ($_SESSION[_S]['s24-ur'] = '15' || $_SESSION[_S]['s24-ur'] = '16')) {
    new ControladorListadoRectificaciones();
} else {
    WebResponse::sinPermisos();
}
