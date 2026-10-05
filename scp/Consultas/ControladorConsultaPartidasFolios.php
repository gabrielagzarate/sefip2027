<?php

/**
 * Description of ControladorSeguimientoPrevisiones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 01-Feb-2019
 * Comment: -----
 * Objective: Muestra en pantalla los folios de Adecuaciones Presupuestales
- Clave presupuestal
- Número de folio
- Unidad Responsable
- Origen
- Status.
 * Regla de Negocio: Permisos de lectura.
 * */
//Inicio de sesion
session_start();
//Llamada a librerias
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class ControladorConsultaPartidasFolios extends WebResponse
{
    public $nvj ;
    public $gpo;
    public $ur;
    public $perfil;

    public function __construct()
    {
        //Constructor
        parent::__construct();
        //variables de session
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id']
            != '') ? $_SESSION[_S]['nvj_id'] : '';
        $this->gpo = (isset($_SESSION[_S]['gpo_id']) && $_SESSION[_S]['gpo_id']
            != '') ? $_SESSION[_S]['gpo_id'] : '';
        $this->ur = (isset($_SESSION[_S]['ur_id']) && $_SESSION[_S]['ur_id'] != '')
        ? $_SESSION[_S]['ur_id'] : '';
        $this->perfil = (isset($_SESSION[_S]['prf_id']) && $_SESSION[_S]['prf_id']
            != '') ? $_SESSION[_S]['prf_id'] : '';

        switch ($this->metodo) {
            case 'llenaTablaClaves':
                $this->llenaTablaClaves();
                break;
            case 'LlenaTablaPresupuestal':
                $this->LlenaTablaPresupuestal();
                break;
            case 'TablaPresupuestal':
                $this->TablaPresupuestal();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->titulo('Seguimiento de Folios');
        //Agrega el boton a la barra de herramientas
        $this->agregaTools('');

        //Espacio para ingresar Clave presupuestal
        $clavePres = new stdClass();
        $clavePres->id = 'clavePres';
        $clavePres->placeholder = 'F1 Clave presupuestal';
        $clavePres->class = 'form-control F1Catalogo';
        $clavePres->maxlength = 34;

        //Boton buscar
        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->value = 'Buscar';
        $buscar->icon = 'search me-2';
        $buscar->color = 'gris';

        $programa = new StdClass();
        $programa->id = 'programa';
        $programa->name = 'programa';
        $programa->label = 'Programa';
        $programa->maxlength = 3;
        $programa->placeholder = 'Programa';
        $programa->enDiv = true;

        //Boton Pdf
        $pdf = new stdClass();
        $pdf->id = 'pdf';
        $pdf->value = 'PDF';
        $pdf->icon = 'fa fa-file-pdf me-2';
        $pdf->color = 'rojo';

        //Boton Excel
        $excel = new stdClass();
        $excel->id = 'excel';
        $excel->value = 'Excel';
        $excel->icon = 'file-excel me-2';
        $excel->color = 'verde';

        //Boton Excel
        $limpiar = new stdClass();
        $limpiar->id = 'clean';
        $limpiar->value = 'Limpiar';
        $limpiar->icon = 'fa fa-trash me-2';
        $limpiar->class = 'btn btn-outline-info clean';
        $limpiar->color = 'amarillo';

        $html = '
            <div class="row">
                <!--<h4>Buscar por:</h4>-->
                <fieldset class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $this->ponInput($clavePres) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $this->ponBoton($buscar) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $this->ponBoton($limpiar) . '
                        ' . $this->ponBoton($pdf) . '
                        ' . $this->ponBoton($excel) . '
                    </fieldset>
                </fieldset>
            </div><br/>
        ';

        //Grid para mostrar la información
        $tabla = new stdClass();
        $tabla->titulo = 'Claves Presupuestales';
        $tabla->id = 'clavePresupuestal';
        $tabla->tools = $html;
        $tabla->numFilas = 10;

        //Encabezados
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Clave Presupuestal';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Folio';
        $th->width = '10px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Unidad Responsable';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Origen';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Status';
        array_push($tr, $th);

        //Agrega las columnas al encabezado
        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClaves';
        $tabla->comunica->CvePres = '{$("#clavePres").val()}';
        $tabla->numFilas = 10;
        $tabla->filasAmostrar = [5, 10, 15, 20, 25];

        //Se inicia el cuerpo de la Tabla
        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pre_claveCompleta', 'guion', 'tcp_id'];
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id', 'guion', 'ur_nombre'];
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'omo_nombre';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'emo_color';
        $td->color = 'emo_color';
        $td->campoTxt = 'emo_id';
        $td->lbl = 'emo_nombre';
        $td->title = 'emo_nombre';
        array_push($tabla->cuerpo, $td);

        $this->mixScript('
            var pulso = false;
            $("#S_5_6").click(function(){//comprobando si se realizo click en seguimiento de folio
                pulso = true;
                if (pulso){//si se selecciona la opcion se limpia para mostrar  F1
                    $("div[aria-describedby=cargaDatosCvePre]").html("");
                }
            });
        ');

        $this->mixHtml($this->wrPonTabla($tabla));

        //F1
        $this->mixScript('
            $("#clavePres").focus( function(e) {
                cveFinID= "clavePres";
                cveFinNom = "";
            });
            // F1 muestra el catálogo */
            $("#clavePres").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (key == 112 ) { //F1
                    e.preventDefault();
                comunica("' . $this->pantalla . '",{
                    metodo:"TablaPresupuestal",
                });
                return false;
                }
            });
        ');

        //Valida que el tamaño minimo para la clave presupuestal ingresada sea de 21
        $this->mixScript('
            $("#buscar").click(function(event){
                if($("#clavePres").val().length >=21){
                    ' . $this->wrLlamaLlenaTabla('clavePresupuestal') . '
                }else{
                    notify("La clave presupuestal debe tener por lo menos 21 digitos","warning");
                }
            });
            $("#pdf").hide();
            $("#excel").hide();

            $("#clean").click(function(){
                $("div[aria-describedby=cargaDatosCvePre]").html("");
                $("#clean").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                });
                return false;
            });
        ');
    }

    private function llenaTablaClaves()
    {
        //Define variable
        $and = '';
        $parametros = [];
        //Se reemplaza la clave presupuestal
        $this->prm->CvePres = str_replace('-', '', $this->prm->CvePres);
        //Define bandera en true
        $ban = true;
        //Valida que el total de caracteres de la clave presupuestal sea mayor a 24
        if (strlen($this->prm->CvePres) < 24) {
            //Envia mensaje de alerta
            $this->mixScript('notify("La clave presupuestal debe tener por lo menos 24 digitos","warning");');
            //Establece la bandera de validacion en false
            $ban = false;
        } else {
            //Verifica el nivel jerarquico
            switch ($this->nvj) {
                case 1: //Unidad Ejecutora
                case 2: //Unidad Responsable
                    //Verifica si el perfil es Supervisor o Supervisor de Compras Consolidadas
                    if ($this->perfil == 'S' || $this->perfil == 'U') {
                        //Obtiene el grupo y la UR de la clave presupuestal
                        $ur = substr($this->prm->CvePres, 0, 3);
                        $and .= ' and b.gpo_id = :gpo_id and b.ur_id = :ur_id ';
                        $and .= ' and ur_rh=1 ';
                        $parametros += [
                            'gpo_id' => substr($ur, 0, 1),
                            'ur_id'  => substr($ur, 1, 2),
                        ];
                    } else {
                        //Obtiene el grupo y la ur de la sesion
                        $and .= ' and b.gpo_id = :gpo_id and b.ur_id = :ur_id ';
                        $parametros += [
                            'gpo_id' => $this->gpo,
                            'ur_id'  => $this->ur,
                        ];
                    }
                    break;
                default:
                    //Obtiene el grupo y la UR de la clave presupuestal
                    $ur = substr($this->prm->CvePres, 0, 3);
                    $and .= ' and b.gpo_id = :gpo_id and b.ur_id = :ur_id ';
                    $parametros += [
                        'gpo_id' => substr($ur, 0, 1),
                        'ur_id'  => substr($ur, 1, 2),
                    ];
                    break;
            }
            //Unidad ejecutora
            $and .= ' and b.ue_id = :ue_id ';
            $parametros += [
                'ue_id' => substr($this->prm->CvePres, 3, 3),
            ];
            ///Programa
            $and .= ' and prg_id = :prg_id ';
            $parametros += [
                'prg_id' => substr($this->prm->CvePres, 6, 3),
            ];
            //Subprograma
            $and .= ' and spr_id = :spr_id ';
            $parametros += [
                'spr_id' => substr($this->prm->CvePres, 9, 2),
            ];
            //Proyecto
            $and .= ' and py_id = :py_id ';
            $parametros += [
                'py_id' => substr($this->prm->CvePres, 11, 3),
            ];
            //Obra actividad
            $and .= ' and oa_id = :oa_id ';
            $parametros += [
                'oa_id' => substr($this->prm->CvePres, 14, 3),
            ];
            //Partida generica
            $and .= ' and b.pg_id = :pg_id ';
            $parametros += [
                'pg_id' => substr($this->prm->CvePres, 17, 3),
            ];
            //Partida de especifica
            $and .= ' and b.pe_id = :pe_id ';
            $parametros += [
                'pe_id' => substr($this->prm->CvePres, 20, 3),
            ];

            $cff_id = substr($this->prm->CvePres, 23, 1);
            //Fuente de financiamiento
            $fte_id = substr($this->prm->CvePres, 24, 1);
            //Sub-fuente de financiamiento
            $sft_id = substr($this->prm->CvePres, 25, 1);
            //Origen de financiamiento
            $ori_id = substr($this->prm->CvePres, 26, 1);
            //Suborigen de financiamiento
            $sor_id = substr($this->prm->CvePres, 27, 1);
            //Indentificador de la clave de financiamineto
            $cfi_id = substr($this->prm->CvePres, 28, 2);
            //Indentificador del año de la clave de financiamiento
            $cfi_anio = substr($this->prm->CvePres, 30, 2);

            $and .= (strlen($cff_id) == 1) ? ' and cff_id = :cff_id ' : '';
            $and .= (strlen($fte_id) == 1) ? ' and fte_id = :fte_id ' : '';
            $and .= (strlen($sft_id) == 1) ? ' and sft_id = :sft_id ' : '';
            $and .= (strlen($ori_id) == 1) ? ' and ori_id = :ori_id ' : '';
            $and .= (strlen($sor_id) == 1) ? ' and sor_id = :sor_id ' : '';
            $and .= (strlen($cfi_id) == 2) ? ' and cfi_id = :cfi_id ' : '';
            $and .= (strlen($cfi_anio) == 2) ? ' and cfi_anio = :cfi_anio ' : '';

            $parametros += [
                'cff_id'   => $cff_id,
                'fte_id'   => $fte_id,
                'sft_id'   => $sft_id,
                'ori_id'   => $ori_id,
                'sor_id'   => $sor_id,
                'cfi_id'   => $cfi_id,
                'cfi_anio' => $cfi_anio,
            ];
        }

        //Valida que la clave presupuestal tenga el minimo de digitos
        if ($ban) {
            $sql = "
                select a.omo_id,a.mod_gpo_id,a.mod_ur_id,a.mod_id, c.ur_nombre,
                pre_claveCompleta,b.gpo_id,b.ur_id,'-' as guion,
                f.emo_nombre, f.emo_color, a.emo_id,b.tcp_id,omo_nombre
                from scp_MaestroModificacion a
                join scp_DetalleModificacion b using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                join presupuesto using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                join cat_PartidaEspecifica d using(pg_id, pe_id)
                join cat_EstadoModificacion f using (emo_id)
                join cat_UnidadResponsable c on c.gpo_id = a.mod_gpo_id and c.ur_id = a.mod_ur_id
                join cat_OrigenModificacion e using(omo_id)
                join enc_OrigenOperacion using(omo_id,enc_oop)
                where a.emo_id not in(14,15,16) $and;";
            $this->setSql($sql);
            $this->setParams($parametros);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
            $nav->idTabla = 'clavePresupuestal';
            $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 10;

            if ($this->wrLlenaTabla($nav) == 0) {
                $this->mixScript('
                    notify("No hay registros","warning");
                    $("#pdf").hide();
                    $("#excel").hide();
                ');
            } else {
                $this->mixScript('
                    $("#pdf").show();
                    $("#excel").show();
                    $("#pdf").click(function(){//exporta a pdf
                        var form = $("<form>")
                            .attr("action","rep/doc/scp/consultas/rep_SeguimientoFolio.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "nvj").attr("value", "' . $this->nvj . '"))
                            .append($("<input>").attr("type", "hidden").attr("name", "perfil").attr("value", "' . $this->perfil . '"))
                            .append($("<input>").attr("type", "hidden").attr("name", "cvePres").attr("value", "' . $this->prm->CvePres . '"))
                            .append($("<input>").attr("type", "hidden").attr("name", "grupo").attr("value", "' . $this->gpo . '"))
                            .append($("<input>").attr("type", "hidden").attr("name", "ur").attr("value", "' . $this->ur . '"))
                        $("body").append(form);
                        form.submit();
                                    form.remove();
                    });

                    $("#excel").click(function(){//exporta a excel
                        var form = $("<form>")
                            .attr("action","rep/excel/scp/consultas/exp_SeguimientoFolio.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "nvj").attr("value", "' . $this->nvj . '"))
                                .append($("<input>").attr("type", "hidden").attr("name", "perfil").attr("value", "' . $this->perfil . '"))
                                .append($("<input>").attr("type", "hidden").attr("name", "cvePres").attr("value", "' . $this->prm->CvePres . '"))
                                .append($("<input>").attr("type", "hidden").attr("name", "grupo").attr("value", "' . $this->gpo . '"))
                                .append($("<input>").attr("type", "hidden").attr("name", "ur").attr("value", "' . $this->ur . '"))
                        $("body").append(form);
                        form.submit();
                                    form.remove();
                    });
                ');
            }
        }
    }

    public function TablaPresupuestal()
    {
        $botones = '';
        $botones .= <<<HTML
                <input type="search" width=50
                    id="filtroTabla"
                    style="display: inline; width:300px; height: 30px;"
                    class="form-control" 
                    placeholder="Filtrar"/>
                    <span class="mb-4">(doble click sobre la línea de la clave para seleccionarlo)</span>
            HTML;

        $this->setContenedor('divContenedorGeneralModal');

        $tabla = new stdClass();
        $tabla->id = 'myGridCvePre';
        $tabla->seleccionable = true;
        $tabla->tools = $botones;
        $tabla->numFilas = 20;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'LlenaTablaPresupuestal';

        //Encabezados
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Clave Presupuestal';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Proyecto';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Obra/Actividad';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave de Financiamiento';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de la Clave de Financiamiento';
        array_push($tr, $th);

        //Agrega las columnas al encabezado
        $tabla->encabezado = [$tr];

        //Se inicia el cuerpo de la Tabla
        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'prog';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'npy';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'npro';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'cveF';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'ncve';

        array_push($tabla->cuerpo, $td);

        $this->mixHtml('
            <div class="modal fade modal-xl" id="modalClvPres" tabindex="-1" aria-labelledby="modalClvPresLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header" style="background: #942949;">
                        <h5 class="modal-title" id="modalClvPresLabel">BUSCAR Y SELECCIONAR CLAVE PRESUPUESTAL</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row"> ' . $this->wrPonTabla($tabla) . ' </div>
                    </div>
                    <div class="modal-footer">
                    </div>
                    </div>
                </div>
            </div>
        ');
        $this->mixScript('
            $("#modalClvPres").modal("show");
        ');
        $this->LlenaTablaPresupuestal();

    }

    public function LlenaTablaPresupuestal()
    {
        $sql = "
                SELECT
                CONCAT(p.gpo_id, p.ur_id, ue_id, '-', p.prg_id, p.spr_id, p.py_id, p.oa_id,'-',p.pg_id,p.pe_id,MIN(CONCAT(p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio )) ) AS prog,
                if(p.oa_id != 0,
                oa_nombre,
                py_nombre) AS nombre,
                oa_nombre as npro,
                py.py_nombre AS npy,
                MIN(CONCAT(p.cff_id, '-', p.fte_id, p.sft_id, p.ori_id, p.sor_id, '-', p.cfi_id, p.cfi_anio)) AS cveF,
                MIN(cfi_nombre) AS ncve
                FROM presupuesto p
                INNER JOIN scp_DetalleModificacion d using(gpo_id, ur_id, ue_id,ecf_id,epg_id)
                INNER JOIN enc_Programatico using(epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_ObraActividad oa using(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                INNER JOIN cat_Proyecto py using(gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                WHERE d.gpo_id= '" . $_SESSION[_S]['gpo_id'] . "' AND d.ur_id = '" . $_SESSION[_S]['ur_id'] . "' AND p.oa_id <> 0
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,p.pg_id,p.pe_id
                UNION
                SELECT
                CONCAT(p.gpo_id, p.ur_id, ue_id, '-', p.prg_id, p.spr_id, p.py_id, p.oa_id,'-',p.pg_id,p.pe_id,MIN(CONCAT(p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio )) ) AS prog,
                if(p.oa_id != 0,
                oa_nombre,
                py_nombre) AS nombre,
                oa_nombre as npro,
                py.py_nombre AS npy,
                MIN(CONCAT(p.cff_id, '-', p.fte_id, p.sft_id, p.ori_id, p.sor_id, '-', p.cfi_id, p.cfi_anio)) AS cveF,
                MIN(cfi_nombre) AS ncve
                FROM presupuesto p
                INNER JOIN scp_DetalleModificacion d using(gpo_id, ur_id, ue_id,ecf_id,epg_id)
                INNER JOIN enc_Programatico using(epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_ObraActividad oa using(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                INNER JOIN cat_Proyecto py using(gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                WHERE d.gpo_id= '" . $_SESSION[_S]['gpo_id'] . "' AND d.ur_id = '" . $_SESSION[_S]['ur_id'] . "' AND p.oa_id = 0
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,p.pg_id,p.pe_id
                ORDER BY prog;";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = 'myGridCvePre';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript(
                'notify("No hay registros" ,"warning");
                '
            );

        }

        $this->mixScript('
            $(document).ready(function() { 
                $("#tabla-myGridCvePre").filterTable({ 
                    inputSelector: "#filtroTabla",
                    minRows: 1
                }); 
            });

            const input = document.getElementById("clavePres");
            $("#myGridCvePre tr").on("dblclick", function (ev){
                row = $("#myGridCvePre").tabla("getFilaSeleccion");
                input.value = row.prog;
                $("#modalClvPres").modal("hide");
            });
            ');

    }

}
// Permisos del usuario que puede ver este controlador
if (isset($_SESSION[_S]['prm']['S']['5']['6']['1'])) {
    new ControladorConsultaPartidasFolios();
} else {
    WebResponse::sinPermisos();
}
