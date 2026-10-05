<?php

/* --------------------------------------------------------------------------------------------------------
    Objetivo: Consultar y dar de alta Programas y Subprogramas por Unidad Responsable
    Elaboró : ?
    Colaboradores : Zuli Dorey López Barbosa
    Permisos:
    Esta pantalla no pregunta por permisos de escritura, se basa en el nivel jerarquico del usuario
  -------------------------------------------------------------------------------------------------------- */

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/Programas.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/Subprogramas.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/bit_Programas.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/bit_Subprogramas.php';
require_once dirname(__FILE__) . '/../../srv/PedObjetivo.php';
require_once dirname(__FILE__) . '/../../srv/Pes.php';
require_once dirname(__FILE__) . '/../../srv/ProgramaPresupuestario.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoFuncional.php';
require_once dirname(__FILE__) . '/../../srv/Pep.php';
require_once dirname(__FILE__) . '/../../srv/Pin.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/sp_VinculaSubprogramaUr.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/sp_ActualizarUrNombrePrograma.php';

class ControladorEstructuraProgramatica extends WebResponse
{
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->pantalla = 'poa/programacion/ControladorEstructuraProgramatica.php';
        switch ($this->metodo) {
            case 'ponTablaPrg':
                $this->ponTablaPrg();
                $this->mixScript(ClasificacionAdministrativa::onChangeUr('' . $this->wrLlamaLlenaTabla('tbl-Prg') . ''));
                break;
            case 'llenaPonTablaPrg':
                $this->llenaPonTablaPrg();
                break;
            case 'ponTablaSpr':
                $this->ponTablaSpr();
                break;
            case 'llenaPonTablaSpr':
                $this->llenaPonTablaSpr();
                break;
            case 'addModPrograma':
                $this->addModPrograma();
                break;
            case 'addModSubprograma':
                $this->addModSubprograma();
                break;
            case 'abcPrograma':
                $this->abcPrograma();
                break;
            case 'abcSubprograma':
                $this->abcSubprograma();
                break;
            case 'validaPrograma':
                $this->validaPrograma();
                break;
            case 'ponTablaHistorial':
                $this->ponTablaHistorial();
                break;
            case 'llenaTablaHistorial':
                $this->llenaTablaHistorial();
                break;
            case 'validaDatosPrograma':
                $this->validaDatosPrograma();
                break;
            case 'validaSubprograma':
                $this->validaSubprograma();
                break;
            case 'datosComplementarios':
                $this->datosComplementarios();
                break;
            case 'llenaTablaUrs':
                $this->llenaTablaUrs();
                break;
            case 'vincular':
                $this->vincular();
                break;
            case 'desvincular':
                $this->desvincular();
                break;
            case 'productoFinal':
                $this->productoFinal();
                break;
            case 'vinProductoFinal':
                $this->vinProductoFinal();
                break;
            case 'modalActualizacion':
                $this->modalActualizacion();
                break;
            case 'validaActualizacion':
                $this->validaActualizacion();
                break;
            case 'modalValidacionModificacionPrograma':
                $this->modalValidacionModificacionPrograma();
                break;
            case 'muestraPlanInstitucional':
                $this->muestraPlanInstitucional();
                break;
            case 'validaActualizacionPrograma':
                $this->validaActualizacionPrograma();
                break;
            case 'agregaPi':
                $this->agregaPi();
                break;
            case 'eliminaPi':
                $this->eliminaPi();
                break;
            default:
                $this->ponTablaPrg();
                break;
        }
        $this->returnJson();
    }

    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
      ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function ponTablaPrg()
    {
        $botones = '';

        if ($_SESSION[_S]['nvj_id'] >= 3 && isset($_SESSION[_S]['prm']['A']['2']['3']['2'])) {
            $botones .= <<<HTML
                <button id="nuevoP" type="button" class="btn btn-outline-primary me-1 
                    nuevo m-r-5" title="Registrar nuevo programa">
                    <i class="fa-solid fa-plus me-2"></i>
                    Nuevo Programa
                </button>
                <button id="imprimirficha" class="btn btn-outline-danger me-1" title="Ficha Programas">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
            HTML;
        }

        // Todos pueden exportar a excel
        $botones .= <<<HTML
            <button id="excelPS" class="btn btn-outline-success me-1"
                title="Exportar a Excel Programas y Subprogramas PARA TABLAS DINÁMICAS">
                <i class="fa-solid fa-file-excel"></i> Excel
            </button>
            <input type="search" width=50
                id="filtroTabla"
                style="display: inline; width:300px; height: 30px;"
                class="form-control m-r-10" 
                placeholder="Filtrar Programa" />
            HTML;


        $this->agregaTools($botones);
        $this->mixScript(
            '
                var btn = document.getElementById("imprimirficha");
                if (btn) {
                    btn.addEventListener("click", function () {
                        window.open("rep/doc/poa/ReporteFichaPrograma.php", "_blank");
                    });
                }'
        );

        $tabla = new stdClass();
        $tabla->titulo = 'Programas';
        $tabla->id = 'tbl-Prg';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaPonTablaPrg';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];
        //ENCABEZADO VER/EDITAR
        $tabla->accion = new stdClass();
        $tabla->accion->prm = ['prgs_id', 'eup_mir'];
        $tabla->accion->editar = true;
        $tabla->accion->titulo = 'Ver/Editar';
        $tabla->accion->metodoEditar = 'addModPrograma';

        //BITACORA DEL PROGRAMA
        $th = new stdClass();
        $th->texto = 'Bitacora';
        $th->width = '5px';
        array_push($tr, $th);

        //ENCABEZADO NÚMERO EL PROGRAMA
        $th = new stdClass();
        $th->texto = 'Número';
        $th->width = '18%';
        $th->align = 'center';
        array_push($tr, $th);

        //ENCABEZADO NOMBRE DEL PROGRAMA
        $th = new stdClass();
        $th->texto = 'Nombre del Programa';
        $th->width = '50%';
        $th->align = 'center';
        array_push($tr, $th);

        //ENCABEZADO ESTADO DEL PROGRAMA
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '11%';
        $th->align = 'center';
        array_push($tr, $th);

        //ENCABEZADO SUBPROGRAMA
        $th = new stdClass();
        $th->texto = 'SPrg';
        array_push($tr, $th);
        $th->width = '10%';

        
        //BODY TABLA
        $trbody = [];

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'bitacora';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'list-alt';
        $td->casos->verdadero->align = 'center';
        $td->casos->verdadero->class = 'showBitacora';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->prgs_id = 'prgs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'prgs_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'prgs_nombre';
        $td->align = 'left';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'epr_color';
        $td->color = 'epr_color';
        $td->campoTxt = 'epr_id';
        $td->lbl = 'epr_nombre';
        $td->title = 'epr_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'tieneSub';
        $td->casos->operador = '==';
        $td->casos->valor = '1';
        $td->align = 'right';
        $td->title = 'Mostrar los Subprogramas del Programa actual';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->texto = '';
        $td->casos->verdadero->campoTxt = 'sprgs';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->prgs_id = 'prgs_id';
        $td->casos->verdadero->attr->spr = 'sprgs';
        $td->casos->verdadero->attr->pdf_id = 'pdf_id';
        $td->casos->verdadero->class = 'sprgs btn-sm';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        // Dibujamos la estructura de la pantalla
        $this->mixHtml('
            <div class="tab-content container">
                <div class="tab-pane active" id="prg1">
                    <div class="row">
                        <fieldset>
                            <legend></legend>
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                    </div>
                </div>
                <div class="tab-pane" id="spr1">
                    <div class="row">
                        <fieldset>
                            <legend></legend>
                                <div id="subprogramas"/>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div id="historial" />
        ');
        $this->llenaPonTablaPrg();

        $this->mixScript('
            $("#techos").show();
            limpiaClasAdmin();
            $("#tituloPantalla").html("");
            $("#tituloPantalla").text("Alta de Programas");
            
            $("#nuevoP").click(function(){
                comunica("' . $this->pantalla . '",{ metodo: "addModPrograma" });
            });
            $("#cancelP").click(function(){
                location.reload();
            });
            
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });            
            
            $("#homeMenu").click(function(){
                location.reload();
            });
            // Eliminamos la paginación, ya que no se usará
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            
            $("#excelPS").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/excel/programacion/exp_ProgramasSubprogramas.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "filtro").attr("value", $("#filtroTabla").val() ));
                $("body").append(form);
                form.submit();
            });
            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );
        ');
    }

    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
      ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function llenaPonTablaPrg()
    {
        // Armamos la consulta
        // Se divide en dos porque si lo hago en la misma, al hacer left join a bit_Programa multiplica los subprogramas :(
        $params = [];
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $sql = '
                SELECT base.*, CASE WHEN bit_id IS NULL THEN 0 ELSE 1 END AS bitacora,0 eup_mir
                FROM (
                    SELECT prgs_id,prgs_nombre, ep.epr_id, epr_nombre,
                    CASE WHEN(SELECT COUNT(bit_observacion)
                    FROM ' . BD_BITACORA . ".bit_ActualizacionPrograma
                    WHERE p.prgs_id=prgs_id AND bit_observacion = 'Validado')=0 THEN epr_color ELSE '9033FF' END AS epr_color,
                    IF(prgs_id >99,1,0) tieneSub,
                    SUM( CASE WHEN s.sprs_id IS NULL THEN 0 WHEN s.sprs_id = 0 THEN 0 ELSE 1 END ) AS sprgs,
                    IF(0 IN (SELECT sprs_id FROM cat_Subprogramas where prgs_id = p.prgs_id),
                    (SELECT pdf_id FROM cat_Subprogramas where prgs_id = p.prgs_id and sprs_id=0),0) AS pdf_id
                    FROM cat_Programas p
                    LEFT JOIN cat_Subprogramas s USING( prgs_id )
                    JOIN cat_EstadoPrograma ep ON ( p.epr_id = ep.epr_id)
                    GROUP BY prgs_id
                ) base
                LEFT JOIN " . BD_BITACORA . '.bit_Programas b USING( prgs_id )
                GROUP BY prgs_id,bitacora
            ;';
        } else {
            $sql = '
                SELECT  gpo_id,ur_id,prgs_id, prgs_nombre, epr_id, epr_nombre, epr_color, IF(SUM(tieneSub)>0,1,0) AS tieneSub,
                    MAX(sprgs) AS sprgs, IF(SUM(bitacora)>0,1,0) AS bitacora, SUM(eup_mir) AS eup_mir
                FROM ( 
                    SELECT * FROM (
                        SELECT  base.*,
                            CASE WHEN bit_id IS NULL THEN 0 ELSE 1 END AS bitacora,1 eup_mir
                        FROM    (
                            SELECT resp.gpo_id,resp.ur_id, prgs_id,prgs_nombre, ep.epr_id, epr_nombre,epr_color, IF(prgs_id >99,1,0) tieneSub,
                                SUM( CASE WHEN s.sprs_id IS NULL THEN 0 WHEN s.sprs_id = 0 THEN 0 ELSE 0 END ) AS sprgs
                            FROM cat_Programas p
                            INNER JOIN (SELECT prgs_id, gpo_id, ur_id, 1 eup_mir FROM vin_ProgramasMir
                            WHERE gpo_id = :grupo AND ur_id = :ur)resp USING( prgs_id )
                            LEFT JOIN cat_Subprogramas s USING( prgs_id )
                            JOIN cat_EstadoPrograma ep ON ( p.epr_id = ep.epr_id)
                            GROUP BY prgs_id
                        ) base
                        LEFT JOIN ' . BD_BITACORA . '.bit_Programas b USING( prgs_id )
                        GROUP BY prgs_id,bitacora
                    ) AS responsable
                    UNION ALL (
                        SELECT base.*
                        FROM (
                            SELECT gpo_id,ur_id,prg_id prgs_id,p.prg_nombre AS prgs_nombre, ENC.epr_id, epr_nombre,epr_color, IF(prg_id >99,1,0) tieneSub, sprgs,
                                CASE WHEN bit_prg IS NULL THEN 0 ELSE 1 END AS bitacora,eup_mir
                            FROM cat_Programa  p
                            LEFT JOIN ' . BD_BITACORA . '.bit_Programa b USING( gpo_id,ur_id,prg_id )
                            INNER JOIN(
                                SELECT enc.gpo_id,enc.ur_id,prgs_id AS prg_id, 0 eup_mir,epr_id ,epr_nombre,epr_color,
                                    SUM( CASE WHEN sprs_id IS NULL THEN 0 
                                    WHEN enc.gpo_id= :grupo 
                                    AND enc.ur_id= :ur 
                                    AND sprs_id != 0 THEN 1 ELSE 0 END )  AS sprgs
                                FROM enc_UrSubprogramas enc
                                LEFT JOIN(
                                    SELECT prgs_id,sprs_id ,p.epr_id,epr_nombre,epr_color
                                    FROM cat_Subprogramas
                                    INNER JOIN cat_Programas p USING (prgs_id )
                                    INNER JOIN cat_EstadoPrograma ep ON ( p.epr_id = ep.epr_id)
                                )prgs  USING ( prgs_id,sprs_id )
                                WHERE enc.gpo_id = :grupo AND enc.ur_id = :ur
                                GROUP BY prgs_id,epr_id
                            )ENC USING ( gpo_id,ur_id,prg_id )
                            GROUP BY prgs_id,epr_id,bitacora
                        ) base
                        WHERE gpo_id = :grupo AND ur_id = :ur
                        GROUP BY prgs_id,epr_id,sprgs,bitacora
                    )
                ) T GROUP BY gpo_id,ur_id,prgs_id, prgs_nombre, epr_id, epr_nombre,epr_color
            ;';
            $params = [':grupo' => $_SESSION[_S]['clasAdm-gpo'], ':ur' => $_SESSION[_S]['clasAdm-ur']];
        }

        $this->setSql($sql);
        $this->setParams($params);

        //echo "<pre>";echo $this->getSql();echo "</pre>";

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Prg';
        // Sin paginación
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".sprgs").click(function(){
                var prg = $(this).attr("prgs_id");
                var nomPrg = $(this).closest("tr").find("td:eq(2)").text();
                //Si no esta dentro del rango de los programas 0 al 900, el subprograma de estos programas no tendrá vinculación un producto final
                comunica("' . $this->pantalla . '",{ 
                    metodo: "ponTablaSpr", 
                    p: prg, 
                    nomP: nomPrg
                });
                return false;
            });

            $(".addModPrograma").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "addModPrograma",
                    prgs_id:$(this).attr("prgs_id"),
                    eup_mir:$(this).attr("eup_mir")
                });
            });

            $(".showBitacora").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"ponTablaHistorial",
                    historial:"prg",
                    prgs_id:$(this).attr("prgs_id")
                });
            });

            $(document).ready(function() { 
                $("#tabla-tbl-Prg").filterTable({ 
                    inputSelector: "#filtroTabla",
                    minRows: 1
                }); 
            });

            // Limpiamos el nombre del programa (lo usamos al entrar al subprograma)
            $("#otraInfoFooter").html( "" );
        ');
    }

    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Dibujamos la tabla de Subprogramas
      ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function ponTablaSpr()
    {
        $prg = isset($this->prm->p) ? $this->prm->p : $this->prm->prog;

        $pgm = new Programas();
        $pgm->setPrgs_id($prg);
        $pgm->select();

        $this->mixStyle('
            div.estado {float: left; }
            div.estado + span {
                font-size:.8em;
                margin: 0 0 0 10px;
                float:left;
            }
        ');

        // Si es un usuario de nivel UR
        $nuevo = '
            <button id="cancelS" type="button" class="btn btn-outline-secondary me-1" title="Regresar">
                <i class="fa-solid fa-circle-left me-2"></i>
                Regresar
            </button>';
        if ($_SESSION[_S]['nvj_id'] > 2 && isset($_SESSION[_S]['prm']['A']['2']['3']['2'])) {
            $nuevo .= '
                <button id="nuevoS" type="button" prg_id="' . $prg . '" class="btn btn-outline-primary me-1" title="Registrar nuevo Subprograma">
                    <i class="fa-solid fa-plus me-2"></i>
                    Nuevo Subprograma
                </button>
            ';
        }
        // Cuadro de búsqueda (filtra las Unidades Responsables a mostrar)
        $nuevo .= '
            <input type="search" width=50
                id="filtroTabla"
                style="display: inline; width:300px; height: 30px;"
                class="form-control me-2" 
                placeholder="Filtrar Subprograma"/> <span style="font-size:18px; text-shadow:1px 2px 3px #999">' . htmlspecialchars($this->prm->nomP) . '</span>
        ';

        $this->agregaTools($nuevo);

        $tabla = new stdClass();
        $tabla->titulo = 'Subprogramas';
        $tabla->id = 'tbl-Spr';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaPonTablaSpr';
        $tabla->comunica->progr = $prg;
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];
        $tabla->cabeceraFija = true;

        $tabla->accion = new stdClass();
        $tabla->accion->prm = ['prgs_id', 'sprs_id'];
        $tabla->accion->editar = true;
        $tabla->accion->titulo = 'Ver/Editar';
        $tabla->accion->metodoEditar = 'addModSubprograma';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Bitacora';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Programa';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Subprograma';
        $th->width = '60px';
        array_push($tr, $th);
        if ($prg >= 100 && $prg < 900) {
            $th = new stdClass();
            $th->texto = 'Producto Final';
            $th->width = '60px';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->texto = 'Descripción del Subprograma';
        $th->width = '270px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UR\'s Participantes';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Status del subprograma';
        $th->width = '200px';
        array_push($tr, $th);

        $trbody = [];

        // El botón de ver historial sólo se mostrará cuando tenga historial el programa
        // El parámetro CASOS sirve para llenar la celda dependiendo de una comparación
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'bitacora';
        $td->casos->valor = '1';   // La consulta regresará 1 en el campo 'bitacora' si hay al menos una entrada para éste programa
        // Esta es lo que se mostrará cuando el campo regrese 1
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'list-alt';
        $td->casos->verdadero->align = 'center';
        $td->casos->verdadero->class = 'showBitacoraS';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->prgs_id = 'prgs_id';
        $td->casos->verdadero->attr->sprs_id = 'sprs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'prgs_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'sprs_id';
        array_push($trbody, $td);
        if ($prg >= 100 && $prg < 900) {
            $td = new stdClass();
            $td->campoTxt = 'pdf_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->campoTxt = 'sprs_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'urs';
        $td->title = 'ursNom';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'esr_color';
        $td->color = 'esr_color';
        $td->campoTxt = 'esr_id';
        $td->lbl = 'esr_nombre';
        $td->title = 'esr_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml(
            $this->wrPonTabla($tabla)
                . '<div id="historialSubprogramas" />'
        );

        // Para que al regresar de la edición del subprograma no actualice el nombre del programa
        if (isset($this->prm->nomP)) {
            $this->mixScript('
                // Ponemos el nombre del programa
                /*  $("#otraInfoFooter").html( "<h5>' . (isset($this->prm->nomP) ? $this->prm->nomP : '') . '</h5>" );*/
            ');
        }

        $this->mixScript('
            comunica(
                "' . $this->pantalla . '",{ 
                    metodo: "llenaPonTablaSpr",
                    progr: "' . (isset($this->prm->p) ? $this->prm->p : '') . '"
                }
            );
            $("#tituloPantalla").html("");
            $("#tituloPantalla").text("Alta de Subprograma");

            $("#cancelS").click(function(){
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{ metodo: " " });
            });

            $("#nuevoS").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "addModSubprograma",
                    prgs_id: $(this).attr("prg_id")
                });
                return false;
            });

            // Eliminamos la paginación, ya que no se usará
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();

            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );
        ');
    }

    /* --------------------------------------------------------------------------------------------------------
    Mostramos los subprogramas de una UR + Programa determinado
      --------------------------------------------------------------------------------------------------- */

    private function llenaPonTablaSpr()
    {
        $params = [];
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $sql = '
                SELECT gpo_id,ur_id,sp.prgs_id,sp.sprs_id,sprs_nombre, sp.esr_id, esr_nombre,esr_color, sp.pdf_id,
                CASE WHEN bit_id IS NULL THEN 0 ELSE 1 END AS bitacora,urs,ursNom
                FROM cat_Subprogramas sp
                JOIN cat_EstadoSubprograma USING(esr_id)
                LEFT JOIN ' . BD_BITACORA . '.bit_Subprogramas USING( prgs_id, sprs_id )
            ';
            if ($this->prm->progr > 899) {
                $sql .= "LEFT JOIN (
                    SELECT group_concat(ur_clave) urs,group_concat(concat(ur_clave,' - ',ur_nombre)) ursNom,spr_id as sprs_id 
                    FROM cat_Subprograma
                    JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    WHERE prg_id= ? 
                    AND spr_id != 0
                    GROUP BY spr_id
                    )urSpr USING(sprs_id)
                    LEFT JOIN (
                        SELECT spr_id as sprs_id,gpo_id,ur_id
                        FROM cat_Subprograma
                        JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                        LIMIT 1
                    )b USING (sprs_id)";
                array_push($params, $this->prm->progr);
            }
            if ($this->prm->progr < 900) {
                $sql .= " LEFT JOIN (
                    SELECT group_concat(ur_clave) urs,group_concat(concat(ur_clave,' - ',ur_nombre)) ursNom,pdf_id 
                    FROM cat_Subprograma
                    JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    WHERE prg_id = ? 
                    AND spr_id != 0
                    GROUP BY pdf_id
                        ) urSpr on sp.pdf_id = urSpr.pdf_id
                    LEFT JOIN (
                    SELECT pdf_id,gpo_id,ur_id
                    FROM cat_Subprograma
                    JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    LIMIT 1
                ) b on  sp.pdf_id = b.pdf_id 
                ";
                array_push($params, $this->prm->progr);
            }

            $sql .= '
                WHERE prgs_id = ? 
                AND sprs_id != 0
                GROUP BY prgs_id,sprs_id,gpo_id,ur_id,bitacora
                ORDER BY prgs_id,sprs_id;';
            array_push($params, $this->prm->progr);
        } else {
            $sql = "
                SELECT gpo_id, ur_id, prgs_id, sprs_id, 
                    sprs_nombre, esr_id, esr_nombre, esr_color, 
                    sps.pdf_id, bitacora,
                    group_concat( ur_clave ) urs,
                    group_concat( concat( ur_clave, ' - ', ur_nombre )) ursNom
                FROM cat_Subprograma sp
                JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
                LEFT JOIN (
                    SELECT s.prgs_id, s.sprs_id, s.sprs_nombre, 
                        s.esr_id, s.pdf_id, esr_nombre, esr_color,
                        CASE 
                        WHEN bit_id IS NULL THEN 0 ELSE 1 END 
                        AS bitacora
                    FROM cat_Subprogramas s
                    JOIN cat_EstadoSubprograma USING ( esr_id )
                    LEFT JOIN " . BD_BITACORA . '.bit_Subprogramas bsp USING (prgs_id, sprs_id)
                    WHERE prgs_id = ?
                    AND sprs_id != 0
                    GROUP BY prgs_id, sprs_id, bitacora
                ) sps ON sps.prgs_id=sp.prg_id AND sps.sprs_id=sp.spr_id AND sps.pdf_id=sp.pdf_id
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND prg_id = ? 
                    AND spr_id != 0 
                GROUP BY gpo_id, ur_id, prgs_id, sprs_id, bitacora
                ORDER BY prgs_id, sprs_id
            ';
            array_push($params, $this->prm->progr, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->progr);
        }

        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Spr';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { 
                e.preventDefault(); 
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });            
                comunica("' . $this->pantalla . '",{ metodo:"" } );
                e.stopPropagation(); 
            });

            $(".addModSubprograma").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "addModSubprograma",
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id")
                });
            });

            $(".showBitacoraS").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"ponTablaHistorial",
                    historial:"spr",
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id")
                });
            });

            $(document).ready(function() { 
                $("table").filterTable({ 
                    inputSelector: "#filtroTabla",
                    minRows: 1
                });
            });
        ');
    }

    /* --------------------------------------------------------------------------------------------------------
    Adicionamos, Eliminamos o Modificamos la información de un programa
    --------------------------------------------------------------------------------------------------- */

    private function addModPrograma()
    {
        $this->prm->fun_id = 1;
        $this->prm->sfu_id = 1;

        $guardar = '';
        $soloLectura = false;
        $programa = (isset($this->prm->prgs_id)) ? $this->prm->prgs_id : '';

        $ban = true;
        if ($programa != '') {
            $pr = new Programas($this->prm);
            $abc = 'c';
        } else {
            $pr = new Programas();
            $abc = 'a';
        }

        $prg_id = new stdClass();
        $prg_id->id = 'nprg_id';
        $prg_id->label = 'Número';
        $prg_id->required = true;
        $prg_id->enDiv = true;
        $prg_id->maxlength = 3;
        $prg_id->value = $pr->getPrgs_id();

        $prg = new stdClass();
        $prg->id = 'programa';
        $prg->label = 'Nombre del programa:';
        $prg->required = true;
        $prg->enDiv = true;
        $prg->maxlength = 200;
        $prg->width = '100px';
        $prg->height = '100px';
        $prg->value = $pr->getNombre();

        #población potencial
        $pop = new stdClass();
        $pop->id = 'pbf_id';
        $pop->sql = 'SELECT pbf_id,pbf_nombre,pbf_Registro FROM cat_PoblacionBeneficiada';
        $pop->selecciona = $pr->getPbf_id();
        $pop->label = 'Población Potencial';
        $pop->comoAtr = 'pbf_Registro';

        #hombres
        $hombres = new stdClass();
        $hombres->id = 'hombres';
        $hombres->formato = 'entero';
        $hombres->required = true;
        $hombres->label = 'Hombres';
        $hombres->maxlength = 10;
        $hombres->enDiv = true;
        $hombres->blur = '
            $("#total").val( ($("#mujeres").val() *1 ) + ( $(this).val() * 1) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $hombres->value = $pr->getHombres();
        #mujeres
        $mujeres = new stdClass();
        $mujeres->id = 'mujeres';
        $mujeres->formato = 'entero';
        $mujeres->required = true;
        $mujeres->label = 'Mujeres';
        $mujeres->enDiv = true;
        $mujeres->blur = '
            $("#total").val( ( $("#hombres").val() *1 ) + ( $(this).val() * 1 ) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $mujeres->value = $pr->getMujeres();

        #total
        $total = new stdClass();
        $total->id = 'total';
        $total->formato = 'entero';
        $total->label = 'Total';
        $total->required = true;
        $total->enDiv = true;
        $total->value = $pr->getTotal();

        $total->blur = '
            if( $(this).val() * 1 == 0 ){
                $("#mujeres,#hombres").removeAttr("readonly");
            }else if($("#mujeres").val()!=0 || $("#hombres").val()!=0){ 
                if($("#total").val()!= ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1)){
                    $("#mujeres").val(0);
                    $("#hombres").val(0);
                    $("#mujeres,#hombres").attr("readonly",true);
                }else{
                    $("#total").val( ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1) );
                    $("#mujeres,#hombres").removeAttr("readonly");
                }
            }else{
                $("#mujeres").val(0);
                $("#hombres").val(0);
                $("#mujeres,#hombres").attr("readonly",true);
            }
        ';
        /*---------------------------------------------------población potencial Objetivo-----------------------------------------------*/
        #población potencial Objetivo
        $popO = new stdClass();
        $popO->id = 'pbf_idO';
        $popO->sql = 'SELECT pbf_id,pbf_nombre,pbf_Registro FROM cat_PoblacionBeneficiada';
        $popO->selecciona = $pr->getPbf_idO();
        $popO->label = 'Población Potencial Objetivo';
        $popO->required = true;
        $popO->comoAtr = 'pbf_Registro';


        #hombres Objetivo
        $hombresO = new stdClass();
        $hombresO->id = 'hombresO';
        $hombresO->formato = 'entero';
        $hombresO->required = true;
        $hombresO->label = 'Hombres';
        $hombresO->maxlength = 10;
        $hombresO->enDiv = true;
        $hombresO->blur = '
            $("#totalO").val( ($("#mujeresO").val() *1 ) + ( $(this).val() * 1) );
            if($("#totalO").val() * 1 == 0 )
                $("#totalO").removeAttr("readonly");
            else
                $("#totalO").attr("readonly",true);
        ';
        $hombresO->value = $pr->getHombresO();
        #mujeres Objetivo
        $mujeresO = new stdClass();
        $mujeresO->id = 'mujeresO';
        $mujeresO->formato = 'entero';
        $mujeresO->required = true;
        $mujeresO->label = 'Mujeres';
        $mujeresO->enDiv = true;
        $mujeresO->blur = '
            $("#totalO").val( ( $("#hombresO").val() *1 ) + ( $(this).val() * 1 ) );
            if($("#totalO").val() * 1 == 0 )
                $("#totalO").removeAttr("readonly");
            else
                $("#totalO").attr("readonly",true);
        ';
        $mujeresO->value = $pr->getPrgsMujerO();

        #total Objetivo
        $totalO = new stdClass();
        $totalO->id = 'totalO';
        $totalO->formato = 'entero';
        $totalO->label = 'Total';
        $totalO->required = true;
        $totalO->enDiv = true;
        $totalO->value = $pr->getTotalO();
        $totalO->blur = '
            if( $(this).val() * 1 == 0 ){
                $("#mujeresO,#hombresO").removeAttr("readonly");
            }else if($("#mujeresO").val()!=0 || $("#hombresO").val()!=0){ 
                if($("#totalO").val()!= ($("#mujeresO").val() *1 ) + ( $("#hombresO").val() * 1)){
                    $("#mujeresO").val(0);
                    $("#hombresO").val(0);
                    $("#mujeresO,#hombresO").attr("readonly",true);
                }else{
                    $("#totalO").val( ($("#mujeresO").val() *1 ) + ( $("#hombresO").val() * 1) );
                    $("#mujeresO,#hombresO").removeAttr("readonly");
                }
            }else{
                $("#mujeresO").val(0);
                $("#hombresO").val(0);
                $("#mujeresO,#hombresO").attr("readonly",true);
            }
        ';
        $totalO->value = $pr->getTotalO();
        #Medio de Verificación
        $medVerificacion = new stdClass();
        $medVerificacion->id = 'medVerificacion';
        $medVerificacion->label = '';
        $medVerificacion->formato = 'numfolio';
        $medVerificacion->required = true;
        $medVerificacion->maxlength = 2000; //Máximo300
        $medVerificacion->enDiv = true;
        $medVerificacion->style = 'text-transform: none;!important';
        $medVerificacion->texto = ltrim($pr->getMedioVerificacion());

        if ($programa != '') {
            // Si el status es 2 o 4 ( en validación, o validado ) NO permitimos que lo modifiquen
            // Solo en Alta podemos modificar el dato del ID programa
            //SE QUITA VALIDACIÓN Ticket #753958 - (isset($this->prm->eup_mir) && $this->prm->eup_mir == 0) ||
            if ($pr->getEpr_id() == 4 || empty($_SESSION[_S]['prm']['A']['2']['3']['2'])) {
                $pop->lectura = true;
                $hombres->disabled = true;
                $mujeres->disabled = true;
                $total->disabled = true;

                $popO->lectura = true;
                $hombresO->disabled = true;
                $mujeresO->disabled = true;
                $totalO->disabled = true;

                $medVerificacion->disabled = true;
            }
            $prg_id->readonly = true;
            $soloLectura = ($pr->getEpr_id() == 4 || $_SESSION[_S]['nvj_id'] < 3 || empty($_SESSION[_S]['prm']['A']['2']['3']['2']) || !$ban ? 'true' : 'false');
        }

        if ($soloLectura == 'true') {
            $prg->readonly = $soloLectura;
        }

        // Checamos si viene desde la pantalla de partidad de previsión ...
        // AQUI : MARCOS 06/OCT/2015
        // Estaban comentadas, las descomenté porque estoy probando lo de Previsiones
        $pp = isset($this->prm->pp) ? 1 : 0;

        // Definimos a qué pantalla regresaremos, dependiendo de cómo llegamos aquí
        if ($pp == 1) {
            $pantallaRetorno = 'mga/ControladorPrevisiones.php';
            $this->pantalla = 'poa/programacion/ControladorEstructuraProgramatica.php';
        } else {
            $pantallaRetorno = $this->pantalla;
        }

        $guardar = '
            <button id="regresarP" type="button" class="btn btn-outline-secondary me-1" title="Regresar al listado de los Programas" >
                <i class="fa-solid fa-circle-left me-2"></i>
                Regresar
            </button>';
        # se agrego al al final  if un or en caso de que venga de prevision
        #SI EL PROGRAMA ESTA VALIDADO LA UR PUEDE AGREGAR LA POBLACIÓN POTENCIAL
        $complementaUR = (in_array($pr->getPrgs_id(), range(100, 899)) && $pr->getEpr_id() != 4 && $_SESSION[_S]['nvj_id'] <= 2 && $this->prm->eup_mir == 1 && isset($_SESSION[_S]['prm']['A']['2']['3']['2']));

        if (($pr->getEpr_id() != 4 && $pr->vinculacionModificable() && $_SESSION[_S]['nvj_id'] > 2) || $pp == 1 || $complementaUR) {
            //GUARDAR
            if (($_SESSION[_S]['nvj_id'] >= 3 && (isset($_SESSION[_S]['prm']['A']['2']['3']['2']))) || ($_SESSION[_S]['nvj_id'] == 2)) {
                $guardar .= '
                    <button id="saveP" type="submit" form="Programa" class="btn btn-outline-success me-1" title="Guardar programa" >
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Guardar
                    </button>';
            }
        }
        if ($pr->getEpr_id() != '' && $pr->getEpr_id() != 4 && isset($_SESSION[_S]['prm']['A']['2']['3']['3']) && !$pr->TieneSubProgramasValidados()) {
            switch ($_SESSION[_S]['ssc_id']) {
                //usuario de planeación
                case 2:
                    if ($pr->getEpr_id() != 6 && ($pr->getPrgs_id() > 99 && $pr->getPrgs_id() < 900)) {
                        $guardar .= '
                            <button id="btnValidar" total = "' . $pr->getTotal() . '" edo = 6 prgs_id= "' . $pr->getPrgs_id() . '" type="buttom" class="btn btn-outline-success me-1" title="Validar Programa">
                                <i class="fa-solid fa-check me-2"></i>
                                Validar
                            </button>';
                    }
                    break;
                    //usuario de egresos
                case 3:
                    if (($pr->getEpr_id() != 5 && (($pr->getPrgs_id() < 100 || $pr->getPrgs_id() >= 900))) || ($pr->getEpr_id() == 6 && ($pr->getPrgs_id() > 99 && $pr->getPrgs_id() < 900))) {
                        $guardar .= '
                            <button id="btnValidar" total = "' . $pr->getTotal() . '" edo = ' . (($pr->getPrgs_id() > 99 && $pr->getPrgs_id() < 900) ? 5 : 6) . ' prgs_id= "' . $pr->getPrgs_id() . '" type="buttom" class="btn btn-outline-success me-1" title="Validar Programa">
                                <i class="fa-solid fa-check me-2"></i>
                                Validar
                            </button>';
                    }
                    break;
            }
        }
        if ($pr->getEpr_id() == 4 && isset($_SESSION[_S]['prm']['A']['2']['3']['3'])) {

            if ($pr->getPrgs_id() > 99 && $pr->getPrgs_id() < 900 && $pr->sinActualiacion()) {
                $guardar .= '
                    <button id="btnActualizar"  accion="1" prgs_id="' . $pr->getPrgs_id() . '" nombre_prgs="' . $pr->getNombre() . '" type="button" class="btn btn-outline-success me-1" title="Actualizar Programa">
                        <i class="fa-solid fa-check me-2"></i>
                        Actualizar
                    </button>';
            }


            if ($pr->getPrgs_id() > 99 && $pr->getPrgs_id() < 900 && !$pr->sinActualiacion()) {
                switch ($_SESSION[_S]['ssc_id']) {
                    case 2:
                        if ($pr->validaActualizacioPrograma() == 0 || $pr->validaActualizacioPrograma() == 1) { //Falta validacion de planeacion
                            $guardar .= '
                                <button id="btnValidaActualizacion" accion = "2" prgs_id= "' . $pr->getPrgs_id() . '" nombre_prgs= "' . $pr->getNombre() . '" type="buttom" class="btn btn-outline-success me-1" title="Validar Actualización de Programa">
                                    <i class="fa-solid fa-check me-2"></i>
                                    Validar Actualización de Programa
                                </button>';
                        }
                        break;
                    case 3:
                        if ($pr->validaActualizacioPrograma() == 0 || $pr->validaActualizacioPrograma() == 2) { //Falta validacion de presupuesto
                            $guardar .= '
                                <button id="btnValidaActualizacion" accion = "2" prgs_id= "' . $pr->getPrgs_id() . '" nombre_prgs= "' . $pr->getNombre() . '" type="buttom" class="btn btn-outline-success me-1" title="Validar Actualización de Programa">
                                    <i class="fa-solid fa-check me-2"></i>
                                    Validar Actualización de Programa
                                </button>';
                        }
                        break;
                }
            }
        }
        //El boton desvalidar solo estará disponible para el departamento de egresos
        if ($pr->getEpr_id() == 4 && $pr->sinRegistros() && isset($_SESSION[_S]['prm']['A']['2']['3']['3']) && ($_SESSION[_S]['ssc_id'] == 3 || $_SESSION[_S]['ssc_id'] == 2)) {
            $guardar .= '
                <button id="btnDesvalidar" edo="3" prgs_id= "' . $pr->getPrgs_id() . '" type="buttom" class="btn btn-outline-danger me-1" title="Desvalidar Programa">
                    <i class="fa-solid fa-xmark me-2"></i>
                    Desvalidar
                </button>';
        }

        $this->agregaTools($guardar);

        $form = new stdClass();
        $form->id = 'Programa';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcPrograma';

        $form->datosExtra = new stdClass();
        $form->datosExtra->arrayPI = '"{ArraysObjetivosPi}"';
        $form->datosExtra->abc = $abc;
        $form = new form($form);

        $tipo = new StdClass();
        $programa2 = ($programa == '') ? 1000 : $programa;
        $beneficiarios = '';

        switch (true) { //Revisar para subprogramas
            case in_array($programa2, range(0, 99)):
                $tipo->values = [2 => 'Actividades Centrales de Gestión'];
                $tipo->disabled = true;
                $tipo->selecciona = 2;
                break;
            case in_array($programa2, range(100, 899)):

                #si el programa ya esta validado le permitira a la ur capturar los beneficiarios
                $beneficiarios = '
                    <div class="row" id = "poblacion">
                        <div class="col-md-6 col-xs-12">
                            <div class="row">
                                <legend class="col-md-12 col-xs-12 mb-4">Población Potencial</legend>
                                <div class="col-md-9 col-xs-12">
                                    ' . $this->ponCombo($pop) . '
                                </div>
                                <div class="col-md-9 col-xs-12" id="divhombres">
                                    ' . $this->ponInput($hombres) . '
                                </div>
                                <div class="col-md-9 col-xs-12" id="divmujeres">
                                    ' . $this->ponInput($mujeres) . '
                                </div>
                                <div class="col-md-9  col-xs-12">
                                    ' . $this->ponInput($total) . '
                                </div>
                            </div>
                            <div class="col-md-9">
                                <legend class="col-md-12 col-xs-12 mb-2">Medio de verificación</legend>
                                <div class="col-md-12  col-xs-12">
                                ' . $this->ponTextarea($medVerificacion) . '
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div class="row">
                                <legend class="col-md-12 col-xs-12 mb-4">Población Objetivo</legend>
                                <div class="col-md-9 col-xs-12">
                                    ' . $this->ponCombo($popO) . '
                                </div>
                                <div class="col-md-9 col-xs-12" id="divhombreso">
                                    ' . $this->ponInput($hombresO) . '
                                </div>
                                <div class="col-md-9 col-xs-12"  id="divmujereso">
                                    ' . $this->ponInput($mujeresO) . '
                                </div>
                                <div class="col-md-9  col-xs-12">
                                    ' . $this->ponInput($totalO) . '
                                </div>
                            </div>
                        </div>
                    </div>
                ';

                $tipo->values = [1 => 'Programas Orientados a Resultados'];
                $tipo->selecciona = 1;
                $tipo->disabled = true;
                $this->prm->tpg = 1;
                break;
            case in_array($programa2, range(900, 999)):
                $tipo->values = [3 => 'Gastos no Asignables a Programas'];
                $tipo->selecciona = 3;
                $tipo->disabled = true;
                $this->prm->tpg = 3;

                break;
            default:
                $beneficiarios = '
                    <div class="row" id = "poblacion">
                        <div class="col-md-6 col-xs-12">
                            <div class="row">
                                <legend class="col-md-12 col-xs-12 mb-4">Población Potencial</legend>
                                <div class="col-md-9 col-xs-12">
                                    ' . $this->ponCombo($pop) . '
                                </div>
                                <div class="col-md-9 col-xs-12" id="divhombres">
                                    ' . $this->ponInput($hombres) . '
                                </div>
                                <div class="col-md-9 col-xs-12" id="divmujeres">
                                    ' . $this->ponInput($mujeres) . '
                                </div>
                                <div class="col-md-9  col-xs-12">
                                    ' . $this->ponInput($total) . '
                                </div>
                            </div>
                            <div class="col-md-9">
                                <legend class="col-md-12 col-xs-12 mb-2">Medio de verificación</legend>
                                <div class="col-md-12  col-xs-12">
                                ' . $this->ponTextarea($medVerificacion) . '
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div class="row">
                                <legend class="col-md-12 col-xs-12 mb-4">Población Objetivo</legend>
                                <div class="col-md-9 col-xs-12">
                                    ' . $this->ponCombo($popO) . '
                                </div>
                                <div class="col-md-9 col-xs-12" id="divhombreso">
                                    ' . $this->ponInput($hombresO) . '
                                </div>
                                <div class="col-md-9 col-xs-12"  id="divmujereso">
                                    ' . $this->ponInput($mujeresO) . '
                                </div>
                                <div class="col-md-9  col-xs-12">
                                    ' . $this->ponInput($totalO) . '
                                </div>
                            </div>
                        </div>
                    </div>
                ';
                $tipo->values = (
                    ($pp == 1) ? [3 => 'Gastos no Asignables a Programas'] :
                    [
                        1 => 'Programas Orientados a Resultados',
                        2 => 'Actividades Centrales de Gestión',
                        3 => 'Gastos no Asignables a Programas',
                    ]
                );

                $tipo->selecciona = (($pr->getPrgs_id() != '' && $pp == 0) ? $pr->getPrgs_id() : (($pp == 1) ? 3 : 1));
                break;
        }

        $tipo->class = 'radio tipoPrg';
        $tipo->titulo = 'Tipo de Programa';
        $tipo->name = 'tipo_Programa';

        if ($pr->getEpr_id() != 4) {
            $this->mixScript('
                $("[name=tipo_Programa]").click(function(){
                    if($("#nprg_id").val()!="") $("#nprg_id").blur();
                    if($("[name=tipo_Programa]:checked").val()==1){
                        comunica("' . $this->pantalla . '",{
                            metodo:"addModPrograma",
                            tpg:$("[name=tipo_Programa]:checked").val()
                        });
                    }else{
                        $("#complemento").empty();
                        $("#poblacion").empty();
                    }
                });
            ');
        }
        $tipo->required = true;
        $tipo = $this->ponRadioFlex($tipo);

        $html = <<<HTML
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    {$tipo}
                </div>
            </div>
            <br/>
            <div class="row mb-4">
                <legend class="col-md-12 col-xs-12" >Datos del programa</legend>
                <fieldset class="col-md-1 col-xs-12">
                    {$this->ponInput($prg_id)}
                </fieldset>
                <fieldset class="col-md-11 col-xs-12">
                    {$this->ponInput($prg)}
                </fieldset>
            </div>
            <div id="complemento" class="row">
            
            </div>
            {$beneficiarios}
            HTML
        ;
        $form->mixHtml($html);
        $this->mixHtml($form->ponForm());

        $this->mixScript('
            $("#nprg_id").bind({
                blur: function() {
                    if($(this).val()!="" ){
                        mensaje = "";
                        switch($("[name=tipo_Programa]:checked").val()){
                            case "2": if( $(this).val() * 1 > 99 ) mensaje = "El numero del programa debe de estar entre el 000 y 099"; break;
                            case "1": if( $(this).val() * 1 > 899 || $(this).val() * 1 < 100 ) mensaje = "El numero del programa debe de estar entre el 100 y 899"; break;
                            case "3": if( $(this).val() * 1 > 999 || $(this).val() * 1 < 900 ) mensaje = "El numero del programa debe de estar entre el 900 y 999"; break;
                        }
                        if(mensaje == "")
                            comunica("' . $this->pantalla . '",{
                                metodo:"validaPrograma",
                                nprg_id:$(this).val(),
                                tipo_Programa:$("[name=tipo_Programa]:checked").val(),
                                abc:"' . $abc . '"
                            });
                        else
                            notify(mensaje, "error");
                    } else {
                        notify("El número de programa no puede quedar vacío", "error");
                    }
                },
                keypress:function(event){
                    tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[0-9]/;
                    caracter = String.fromCharCode(tecla);
                    if( tecla == 8 || tecla == 13 || tecla == 9 ){
                        return true;
                    }
                    return patron.test(caracter);
                }
            });

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#regresarP").click(); e.stopPropagation(); });

            // Atajos de ésta pantalla
            Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#saveP").click();});

            $("#regresarP").click(function(){
                $("#toolsPantalla").html("");
                comunica("' . $pantallaRetorno . '",{
                        metodo:"ponTablaPrg"
                });
                return false;
            });

            $("#btnValidar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "validaDatosPrograma",
                    tarea:"autorizar",
                    prgs_id:$(this).attr("prgs_id"),
                    edo:$(this).attr("edo"),
                    total:$(this).attr("total"),
                });
            });

            $("#btnActualizar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"modalActualizacion",
                    prgs_id:$(this).attr("prgs_id"),
                    nombre_prgs:$(this).attr("nombre_prgs"),
                    accion:$(this).attr("accion"),
                });
            });

            $("#btnValidaActualizacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"modalValidacionModificacionPrograma",
                    prgs_id:$(this).attr("prgs_id"),
                    nombre_prgs:$(this).attr("nombre_prgs"),
                    accion:$(this).attr("accion"),
                });
            });

            $("#btnDesvalidar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "validaDatosPrograma",
                    tarea:"desvalidar",
                    prgs_id:$(this).attr("prgs_id")
                });
            });

            setTimeout( function() { $( "#eje_id" ).focus() }, 200 );

            $("#pbf_idO").change(function(){
                if($(this).find("option:selected").attr("pbf_registro")==2){
                    $("#divhombreso").hide();
                    $("#divmujereso").hide();
                }else{
                    $("#divhombreso").show();
                    $("#divmujereso").show();
                }
            });

            $("#pbf_id").change(function(){
                if($(this).find("option:selected").attr("pbf_registro")==2){
                    $("#divhombres").hide();
                    $("#divmujeres").hide();
                }else{
                    $("#divhombres").show();
                    $("#divmujeres").show();
                }
            });

            if($("#pbf_idO").find("option:selected").attr("pbf_registro")==2){
                $("#divhombreso").hide();
                $("#divmujereso").hide();
            }else{
                $("#divhombreso").show();
                $("#divmujereso").show();
            }
            if($("#pbf_id").find("option:selected").attr("pbf_registro")==2){
                $("#divhombres").hide();
                $("#divmujeres").hide();
            }else{
                $("#divhombres").show();
                $("#divmujeres").show();
            }

            function objetoEpt(){
                var stringObj = "[";
                for(var index in ept) {
                    if(index>0)
                        stringObj += ",";
                        stringObj += "{";
                        var obj = ept[index];
                        c = 0;
                        for(var i in obj) {
                            if( c > 0 )
                                stringObj += ",";
                            stringObj += \'"\' + i + \'"\' + \':"\' + obj[i] + \'"\';
                            c++;
                        }
                        console.log();
                        stringObj += "}";
                }
                stringObj += "]";
                return stringObj;
            }
        ');

        if ($programa != '') {
            $this->mixScript('
                $("#saveP").attr("op", "c" );
                setTimeout( function() { $( "#programa" ).focus() }, 200 );
            ');
            if (in_array($programa, range(100, 899))) {
                $this->datosComplementarios($pr);
            }
        } else {
            $this->mixScript('
                $("#saveP").attr("op", "a" );
                setTimeout( function() { $( "#programa" ).focus() }, 200 );
            ');
            if ($pp == 0) {
                $this->datosComplementarios($pr);
            }
        }
        if ($pr->getPrgs_id() != '' && in_array($pr->getPrgs_id(), range(000, 99)) && $_SESSION[_S]['nvj_id'] >= 3) {
            $this->tablaVinculacionUR($pr, false);
        }
    }

    /* --------------------------------------------------------------------------------------------------------
    Dibujamos la pantalla para adicionar, modificar, consultar y eliminar un subprograma
    ----------------------------------------------------------------------------------------------------- */

    private function addModSubprograma()
    {
        $subprograma = (isset($this->prm->sprs_id)) ? $this->prm->sprs_id : '';
        $prg = new Programas($this->prm);
        $nombreSubprograma = '';
        $muestraCatalogo = false;
        $this->prm->algomas5 = 7;

        $script = '';
        $ban = $pf = true;

        if (($subprograma != '')) {

            $catFun = new stdClass();
            $catFun->prgs_id = $prg->getPrgs_id();
            $catFun->sprs_id = $this->prm->sprs_id;

            $spr = new Subprogramas($this->prm);
            $abc = 'c';
            $nombreSubprograma = $spr->getSprs_nombre();


            $catFun->efn_id = $spr->getEfn_id();

            $pr = new Programas($this->prm);
            $catFun->finid = $pr->getFin_id();

            //CONSULTAMOS SI ESTE SUBPROGRAMA YA HA SIDO ASIGNADO
            $this->conectar();
            $this->setSql('SELECT * FROM enc_UrSubprogramas WHERE prgs_id= ? AND sprs_id= ?;');
            $params = [$spr->getPrgs_id(), $spr->getSprs_id()];
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $pf = false;
            } else {
                $pf = true;
            }
        } else { //Guardar un nuevo subprograma
            $muestraCatalogo = true;
            $spr = new Subprogramas();
            $abc = 'a';

            $catFun = new stdClass();
            $catFun->prgs_id = $prg->getPrgs_id();
        }   // Guardar un nuevo subprograma END
        $btnEliminar = '';
        $guardar = '';

        if (($spr->getEsr_id() != 4) && $ban && $_SESSION[_S]['nvj_id'] > 2 && isset($_SESSION[_S]['prm']['A']['2']['3']['2'])) {

            $this->prm->algomas1 = 7;

            $guardar = '
                <button id="regresarS" type="button" class="btn btn-outline-secondary me-1" title="Regresar al listado de los Subprogramas" >
                    <i class="fa-solid fa-circle-left me-2"></i>
                    Regresar
                </button>
                <button id="saveS" type="submit" form="Subprograma" class="btn btn-outline-success me-1" title="Guardar subprograma" ; >
                    <i class="fa-solid fa-floppy-disk me-2"></i>
                    Guardar
                </button>
            ';
        } else {

            $this->prm->algomas3 = 7;
            $guardar = '
                <button id="regresarS" type="button" class="btn btn-outline-secondary me-1" title="Regresar al listado de los Subprogramas" >
                    <i class="fa-solid fa-circle-left me-2"></i>
                    Regresar
                </button>';
        }
        if ($spr->getEsr_id() != '' && $spr->getEsr_id() != 4 && isset($_SESSION[_S]['prm']['A']['2']['3']['3']) && !$spr->sinRegistros()) {
            switch ($_SESSION[_S]['ssc_id']) {
                //planeacion
                case 2:
                    if ($spr->getEsr_id() != 6 && ($spr->getPrgs_id() > 99 && $spr->getPrgs_id() < 900)) {
                        $guardar .= '
                            <button id="btnValidar" pdf_id ="' . $spr->getPdf_id() . '" edo = 6 prgs_id= "' . $spr->getPrgs_id() . '" sprs_id= "' . $spr->getSprs_id() . '" type="buttom" class="btn btn-outline-success me-1" title="Validar Subprograma">
                                <i class="fa-solid fa-check me-2"></i>
                                Validar
                            </button>';
                    }
                    break;
                    //egresos
                case 3:
                    if ($spr->getEsr_id() != 5) {
                        $guardar .= '
                            <button id="btnValidar" pdf_id ="' . $spr->getPdf_id() . '" 
                            edo = "' . (($spr->getPrgs_id() > 99 && $spr->getPrgs_id() < 900) ? 5 : 6) . '"
                            prgs_id= "' . $spr->getPrgs_id() . '" sprs_id= "' . $spr->getSprs_id() . '" type="buttom" class="btn btn-outline-success me-1" title="Validar Subprograma">
                                <i class="fa-solid fa-check me-2"></i>
                                Validar
                            </button>';
                    }
                    break;
            }
        }

        if ($spr->getEsr_id() == 4 && $spr->sinRegistros() && isset($_SESSION[_S]['prm']['A']['2']['3']['3'])) {
            switch ($_SESSION[_S]['ssc_id']) {
                //planeacion
                case 2:
                    if ($spr->getPrgs_id() > 99 && $spr->getPrgs_id() < 900) {
                        $guardar .= '
                            <button id="btnDesvalidar" pdf_id ="' . $spr->getPdf_id() . '" prgs_id= "' . $spr->getPrgs_id() . '" sprs_id= "' . $spr->getSprs_id() . '" type="buttom" class="btn btn-outline-danger me-1" title="Desvalidar Subprograma Pla">
                                <i class="fa-solid fa-xmark me-2"></i>
                                Desvalidar
                            </button>
                        ';
                    }
                    break;
                    //presupuesto
                case 3:
                    $guardar .= '
                        <button id="btnDesvalidar" pdf_id ="' . $spr->getPdf_id() . '" prgs_id= "' . $spr->getPrgs_id() . '" sprs_id= "' . $spr->getSprs_id() . '" type="buttom" class="btn btn-outline-danger me-1" title="Desvalidar Subprograma Pre">
                            <i class="fa-solid fa-xmark me-2"></i>
                            Desvalidar
                        </button>';
                    break;
            }
        }
        $this->agregaTools($guardar);

        if (($subprograma != '')) {
            if (($spr->getEsr_id() == 1 || $spr->getEsr_id() == 3) && $spr->sinRegistros() && $_SESSION[_S]['nvj_id'] > 2 && isset($_SESSION[_S]['prm']['A']['2']['3']['2'])) {
                $btnEliminar = '
                    <button id="deleteSubprograma" pdf_id ="' . $spr->getPdf_id() . '" type="button" class="btn btn-outline-danger me-1" title="Eliminar Subprograma">
                        <i class="fa-solid fa-trash me-2"></i>
                        Eliminar
                    </button> ';
            }
            $this->agregaTools($btnEliminar);
        }

        $form = new stdClass();
        $form->id = 'Subprograma';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcSubprograma';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        //se catean los datos de programa y subprograma para que sean interpretadas como cadenas en el strpost de form.php
        $form->datosExtra->prgs_id = (string) $prg->getPrgs_id();
        $form->datosExtra->sprs_id = (string) $spr->getSprs_id();
        $form = new form($form);

        $form->mixHtml('
            <div class="row">   
                <legend>Datos del Programa</legend>
                <fieldset class="col-md-1 col-xs-1 mb-4">
                    ' . $prg->getPrgs_id() . '
                </fieldset>
                <fieldset class="col-md-11 col-xs-11 mb-4">
                    ' . $prg->getNombre() . '
                </fieldset>
            </div>
        ');
        $numspr = new stdClass();
        $numspr->id = 'spr_id';
        $numspr->label = 'Número';
        $numspr->enDiv = true;
        $numspr->value = $spr->getSprs_id();
        $numspr->readonly = true;

        $spr_id = new stdClass();
        $spr_id->id = 'subprograma';
        $spr_id->label = 'Nombre del Subprograma';
        $spr_id->required = true;
        $spr_id->maxlength = 600;
        $spr_id->value = $nombreSubprograma;
        $spr_id->width = '300px';
        $spr_id->height = '100px';
        $spr_id->enDiv = true;
        $spr_id->texto = $spr->getSprs_nombre();

        if (!$ban) {
            $spr_id->disabled = 'disabled';
        }

        if ($prg->getPrgs_id() > 99 && $prg->getPrgs_id() < 900) {

            if ($spr->getPdf_id() != '') {
                $sentencia = 'if(pdf_id = ' . $spr->getPdf_id() . ',null,prgs_id)';
            } else {
                $sentencia = 'if(ISNULL(pdf_id),null,prgs_id)';
            }

            $pdf = new stdClass();
            $pdf->id = 'pdf_id';
            $pdf->label = 'Producto Final';
            $pdf->enDiv = true;
            $pdf->required = true;
            $pdf->search = true;
            if (!$pf) {
                $pdf->disabled = true;
            }
            $pdf->lectura = $spr->getEsr_id() == 4 || $_SESSION[_S]['nvj_id'] < 3;
            $pdf->selecciona = $spr->getPdf_id();
            $pdf->comoAtr = 'pdf_plural';

            //Consulta en la que habría que realizar el cambio de pdf_plural por pdf_nombre, a petición de planeación
            $pdf->sql = '
                SELECT pdf_id,pdf_plural 
                FROM cat_ProductoFinal
                LEFT JOIN(
                    SELECT pdf_id,' . $sentencia . ' prgs_id FROM cat_Subprogramas ' . ((!$pf) ? 'WHERE pdf_id = ' . $spr->getPdf_id() . '' : '') . '
                )yatiene USING(pdf_id)
                WHERE pdf_final = 1 AND ISNULL(prgs_id);
            ';

            $form->mixHtml('
                <div class="row">
                    <legend>Datos del Sub Programa</legend>
                    <fieldset class="col-md-1 col-xs-1 mb-4">
                        ' . $this->ponInput($numspr) . '
                    </fieldset>
                    <fieldset class="col-md-11 col-xs-11 mb-4">
                        ' . $this->ponInput($spr_id) . '
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12 mb-4">
                        ' . $this->ponCombo($pdf) . '
                    </fieldset>
                </div>
            ');

            if (!$muestraCatalogo && $prg->getPrgs_id() > 99 && $_SESSION[_S]['nvj_id'] >= 3) {
                $spr = new Subprogramas($this->prm);

                $prpr = new stdClass();
                if (1 != 1) { //$prg->getEpr_id() == 4
                    $prpr->disabled = false; //Revisar condicionales
                }

                // PROGRAMA PRESUPUSTARIO
                $lectura = $_SESSION[_S]['nvj_id'] < 3;
                $prpr->epp_id = $spr->getEpp_id();
                if ($spr->getEsr_id() == 4 || $lectura || empty($_SESSION[_S]['prm']['A']['2']['3']['2'])) {
                    $prpr->lectura = true;
                } else {
                    $prpr->lectura = false;
                }
                $form->mixHtml('
                    <div class="row mb-4">
                        <div class="col-md-6">
                            ' . CatalogoFuncional::ponCatalogoFuncional($catFun) . '
                        </div>
                        <div class="col-md-6">' .
                            ProgramaPresupuestario::ponProgramaPres($prpr) .
                        '</div>
                    </div>
                ');
            }

            if (!is_null($this->comboValorUnico) && $abc == 'a') {
                $this->mixScript('
                    $("#subprograma").val($("#pdf_id").find("option:selected").attr("pdf_plural"));');
            }
        } else {
            $form->mixHtml('
                <div class="row">
                    <legend>Datos del Producto/Sub Programa</legend>
                    <fieldset class="col-md-1 col-xs-1 mb-4">
                        ' . $this->ponInput($numspr) . '
                    </fieldset>
                    <fieldset class="col-md-11 col-xs-11 mb-4">
                        ' . $this->ponInput($spr_id) . '
                    </fieldset>
                </div>
            ');
        }
        $this->mixHtml($form->ponForm());

        //Guardar un nuevo subprograma
        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#regresarS").click(); e.stopPropagation(); });
            // Atajos de ésta pantalla
            Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#saveS").click(); });
            
            $("#regresarS").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo : "ponTablaSpr",
                    p : "' . $this->prm->prgs_id . '"
                });
                return false;
            });

            $("#deleteSubprograma").click(function(){
                alertify.confirm("ELIMINAR SUBPROGRAMA","¿ Realmente desea eliminar el Subprograma ' . $subprograma . ' ?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcSubprograma",
                        abc:"b",
                        pdf_id:$("#pdf_id").val()' . (isset($this->prm->prgs_id) ? ',
                        prg:' . $this->prm->prgs_id . '' : '') . '
                        ' . (isset($this->prm->sprs_id) ? ',spr_id:' . $this->prm->sprs_id . '' : '') . '
                    });
                },
                function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $("#btnValidar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "validaSubprograma",
                    tarea:"autorizar",
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id"),
                    pdf_id:$(this).attr("pdf_id"),
                    edo:$(this).attr("edo")
                });
            });

            $("#btnDesvalidar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "validaSubprograma",
                    tarea:"desvalidar",
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id"),
                    pdf_id:$(this).attr("pdf_id")
                });
            });

            $("#pdf_id").change(function(){
                console.log("cambia");
                if($("#subprograma").val()!=""){
                    
                }else{
                    $("#subprograma").val($(this).find("option:selected").attr("pdf_plural"));
                }
            });
            ' . $script . '
        ');

        $this->mixScript('setTimeout( function() { $( "#subprograma" ).focus() }, 200 );');

        if ($subprograma != '' && $prg->getPrgs_id() > 99 && $_SESSION[_S]['nvj_id'] >= 3) {
            $this->prm->pdf_id = $spr->getPdf_id();
            $this->tablaVinculacionUR($spr, true);  //Revisar tabla
        }
    }

    /* --------------------------------------------------------------------------------------------------------
    Grabamos un nuevo programa
    ----------------------------------------------------------------------------------------------------*/

    private function abcPrograma()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $this->prm->epo_id = $_SESSION[_S]['ped']->epo;
        //$this->prm->spe_id = $_SESSION[_S]['pes']->spe_id;
        $this->prm->efn_id = $_SESSION[_S]['catfuncional']->efn;
        $this->prm->epp_id = $_SESSION[_S]['pp']->epp;
        //$this->prm->epc_id = $_SESSION[_S]['pep']->epc;
        $this->prm->pbf_id = $this->prm->pbf_id ?? 1;
        $this->prm->gpo_id = $_SESSION[_S]['resp']->gpo_id;
        $this->prm->ur_id = $_SESSION[_S]['resp']->ur_id;
        $this->prm->usr_id = $_SESSION[_S]['usr_id'];
        $this->prm->epods_id = $_SESSION[_S]['ped']->epods_id;
        $this->prm->epi_id = $_SESSION[_S]['pin']->epi;
        $this->prm->fin_id = $_SESSION[_S]['catfuncional']->fin;

        $this->prm->fun_id = 1;
        $this->prm->sfu_id = 1;
        //addPrograma
        //Definimos a qué pantalla regresaremos, dependiendo de cómo llegamos aquí
        if ($this->prm->pp == 1) {
            $this->pantalla = 'poa/ControladorEstructuraProgramatica.php';
        }
        if ($this->prm->totalO > $this->prm->total) {
            $this->mixScript('
                notify("El total del la poblacion objetivo no puede ser mayor al total de la poblacion potencial", "error");
            ');
            return false;
        }
        switch ($this->prm->abc) {

            case 'a':
                if (isset($this->prm->nprg_id) && $this->prm->nprg_id != '') {
                    $this->prm->prgs_id = $this->prm->nprg_id;
                    $this->prm->prgs_nombre = $this->prm->programa;
                    $this->prm->epr_id = 1;
                    $this->prm->pbf_id = ((isset($this->prm->pbf_id)) ? ($this->prm->pbf_id) : 1);
                    $this->prm->pbf_idO = ((isset($this->prm->pbf_idO)) ? ($this->prm->pbf_idO) : 1);
                    $this->prm->prgs_hombresO = $this->prm->hombresO ?? 0;
                    $this->prm->prgs_mujeresO = $this->prm->mujeresO ?? 0;
                    $this->prm->prgs_totalO = $this->prm->totalO ?? 0;
                    $this->prm->prgs_hombres = $this->prm->hombres ?? 0;
                    $this->prm->prgs_mujeres = $this->prm->mujeres ?? 0;
                    $this->prm->prgs_total = $this->prm->total ?? 0;
                    $this->prm->prgs_MedioVerificacion = $this->prm->medVerificacion ?? 0;

                    //validamos el rango del programa
                    $error = 0;
                    $msg = '';
                    $tipo = $this->prm->tipo_Programa;
                    $val = $this->prm->prgs_id;
                    switch (true) {
                        case !in_array($val, range(0, 99)) && $tipo == 2:
                            //Actividades Centrales de Gestión");
                            $error = 1;
                            $msg = 'El numero del programa debe de estar entre el 000 y 099';
                            break;
                        case !in_array($val, range(100, 899)) && $tipo == 1:
                            $error = 1;
                            $msg = 'El numero del programa debe de estar entre el 100 y 899';
                            //Programas Orientados a Resultados");
                            break;
                        case !in_array($val, range(900, 999)) && $tipo == 3:
                            $error = 1;
                            $msg = 'El numero del programa debe de estar entre el 900 y 999';
                            //Gastos no Asignables a Programas
                            break;
                    }

                    $bitPrg = new stdClass();
                    $bitPrg->bit_observacion = 'EL PROGRAMA FUE CREADO,VINCULACIONES:pedObjetivo:' . $this->prm->epo_id . ',pes:' . $this->prm->pes_id . ',funcional:' . $this->prm->efn_id . ',presupuestario:' . $this->prm->epp_id;
                    $bitPrg->prgs_id = $this->prm->prgs_id;
                    $bitPrg->epr_id = 1;
                    $bitPrg->usr_ip = ip2long($ip);
                    $bitPrg->abc = 'a';

                    if ($error == 0) {
                        //Busca el programa
                        $this->conectar();
                        $this->setSql('SELECT prgs_id FROM cat_Programas WHERE prgs_id = ?');
                        $this->consultar([$val]);
                        $this->desconectar();

                        if ($this->getNumeroRegistros() > 0) {
                            $this->mixScript('
                                notify("El Programa ' . $val . ' ya existe", "error");
                                $("#nprg_id").focus();
                            ');
                        } else {
                            Programas::abc($this->prm);
                            bit_Programas::abc($bitPrg);

                            $this->mixScript('notify("Programa ' . $this->prm->nprg_id . ' guardado exitosamente");
                                comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"' . (($this->prm->pp == 1) ? ',pp:1' : '') . '});
                            ');
                        }
                    } else {
                        $this->mixScript('
                            notify("' . $msg . '", "error");
                        ');
                    }
                }
                break;
            case 'b':
                //ELIMINAMOS EL SUBPROGRAMA 0
                $this->conectar();
                $this->setSql('DELETE FROM cat_Subprogramas WHERE prgs_id = ? AND sprs_id = 0;');
                $this->consultar([$this->prm->prgs_id]);
                $this->desconectar();
                //ELIMINAMOS LAS VINCULACIONES DEL PROGRAMA
                $this->conectar();
                $this->setSql('DELETE FROM vin_Programa WHERE prgs_id = ? ;');
                $this->consultar([$this->prm->prgs_id]);
                $this->desconectar();

                $bitPrg = new stdClass();
                $bitPrg->epr_id = 1;
                $bitPrg->prgs_id = $this->prm->prgs_id;
                $bitPrg->observacion = 'EL PROGRAMA FUE ELIMINADO';
                $bitPrg->usr_ip = ip2long($ip);
                $bitPrg->abc = 'a';

                //ELIMINAMOS EL PROGRAMA

                Programas::abc($this->prm);
                bit_Programas::abc($bitPrg);

                $this->mixScript('
                    notify("Programa ' . $this->prm->prgs_id . ' eliminado exitosamente");
                    comunica("' . $this->pantalla . '",{ metodo:"ponTablaPrg" });
                ');
                break;
            case 'c':

                $pr = new Programas();
                $pr->setPrgs_id($this->prm->nprg_id);
                $pr->select();

                $this->prm->prgs_id = $this->prm->nprg_id;
                if (!empty($this->prm->programa)) {
                    $this->prm->nombre = $this->prm->programa;
                }

                $error = 0;
                $msg = '';
                $tipo = $this->prm->tipo_Programa;
                $val = $this->prm->prgs_id;
                switch (true) {
                    case !in_array($val, range(0, 99)) && $tipo == 2:
                        //Actividades Centrales de Gestión");
                        $error = 1;
                        $msg = 'El numero del programa debe de estar entre el 000 y 099';
                        break;
                    case !in_array($val, range(100, 899)) && $tipo == 1:
                        $error = 1;
                        $msg = 'El numero del programa debe de estar entre el 100 y 899';
                        //Programas Orientados a Resultados");
                        break;
                    case !in_array($val, range(900, 999)) && $tipo == 3:
                        $error = 1;
                        $msg = 'El numero del programa debe de estar entre el 900 y 999';
                        //Gastos no Asignables a Programas
                        break;
                }

                $bitPrg = new stdClass();
                $bitPrg->epr_id = $pr->getEpr_id();
                $bitPrg->prgs_id = $pr->getPrgs_id();
                $bitPrg->bit_observacion = 'EL PROGRAMA FUE MODIFICADO,VINCULACIONES:pedObjetivo:' . $this->prm->epo_id . ',pes:' . $this->prm->pes_id . ',funcional:' . $this->prm->efn_id . ',presupuestario:' . $this->prm->epp_id;
                $bitPrg->usr_ip = ip2long($ip);
                $bitPrg->abc = 'a';

                $this->prm->fun_id = 1;
                $this->prm->sfu_id = 1;
                $this->prm->prgs_id = $this->prm->nprg_id;
                $this->prm->prgs_nombre = $this->prm->programa;
                $this->prm->epr_id = 1;
                $this->prm->pbf_id = ((isset($this->prm->pbf_id)) ? ($this->prm->pbf_id) : 1);
                $this->prm->pbf_idO = ((isset($this->prm->pbf_idO)) ? ($this->prm->pbf_idO) : 1);
                $this->prm->prgs_hombresO = $this->prm->hombresO ?? 0;
                $this->prm->prgs_mujeresO = $this->prm->mujeresO ?? 0;
                $this->prm->prgs_totalO = $this->prm->totalO ?? 0;
                $this->prm->prgs_hombres = $this->prm->hombres ?? 0;
                $this->prm->prgs_mujeres = $this->prm->mujeres ?? 0;
                $this->prm->prgs_total = $this->prm->total ?? 0;
                $this->prm->prgs_MedioVerificacion = $this->prm->medVerificacion;

                if ($error == 0) {
                    Programas::abc($this->prm);
                    bit_Programas::abc($bitPrg);

                    $this->mixScript('
                        notify("Programa ' . $this->prm->nprg_id . ' actualizado exitosamente");
                        comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
                    ');
                } else {
                    $this->mixScript('
                        notify("' . $msg . '", "error");
                    ');
                }
                break;
        }
    }

    private function abcSubprograma()
    {

        $this->prm->epo_id = $_SESSION[_S]['ped']->epo;
        $this->prm->eps_id = $_SESSION[_S]['pes']->eps;
        //$this->prm->epc_id = $_SESSION[_S]['pep']->epc;
        $this->prm->efn_id = (isset($_SESSION[_S]['catfuncional']->efn) ? ($_SESSION[_S]['catfuncional']->efn) : 1);
        $this->prm->epp_id = $_SESSION[_S]['pp']->epp;

        $this->prm->algomas8 = 7;
        switch ($this->prm->abc) {
            case 'a':
                if (isset($this->prm->prgs_id) && $this->prm->prgs_id != '') {
                    $this->prm->sprs_id = 100;
                    $this->prm->nombre = $this->prm->subprograma;
                    $this->prm->esr_id = 1;
                    $bitSpr = new stdClass();
                    $bitSpr->esr_id = 1;
                    $bitSpr->pdf_id = (isset($this->prm->pdf_id) && !empty($this->prm->pdf_id) ? $this->prm->pdf_id : '0');
                    $bitSpr->prgs_id = $this->prm->prgs_id;
                    $bitSpr->abc = 'a';

                    //Revisar que datos no están siendo extraídos
                    $this->prm->sprs_nombre = $this->prm->nombre;

                    $epp_id = 2;
                    $this->prm->epp_id = $epp_id;
                    $sprs_id = SubProgramas::abc($this->prm);
                    $bitSpr->bit_observacion = 'EL SUBPROGRAMA ' . $sprs_id . ' FUE CREADO';
                    $bitSpr->sprs_id = $sprs_id;
                    bit_SubProgramas::abc($bitSpr);

                    $this->mixScript('
                        notify("Subprograma ' . $sprs_id . ' agregado exitosamente");
                        comunica("' . $this->pantalla . '",{
                            metodo:"ponTablaSpr",
                            p : "' . $this->prm->prgs_id . '"
                        });
                    ');
                }
                break;

            case 'b':
                $bitSpr = new stdClass();
                $bitSpr->esr_id = 3;
                $bitSpr->pdf_id = $this->prm->pdf_id;
                $bitSpr->prgs_id = $this->prm->prg;
                $bitSpr->sprs_id = $this->prm->spr_id;
                $bitSpr->abc = 'a';
                $bitSpr->bit_observacion = 'EL SUBPROGRAMA ' . $this->prm->spr_id . ' FUE ELIMINADO';

                $this->prm->prgs_id = $this->prm->prg;
                $this->prm->sprs_id = $this->prm->spr_id;

                //Verifica que no tenga URs vinculadas
                $this->conectar();
                $this->setSql('SELECT sprs_id
                    FROM enc_UrSubprogramas
                    WHERE prgs_id = ?
                    AND sprs_id = ? ;
                ');
                $this->consultar([$this->prm->prg, $this->prm->spr_id]);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $this->mixScript('
                        notify("El subprograma ' . $this->prm->sprs_id . ' no puede ser borrado, tiene UR´s vinculadas");
                        comunica("' . $this->pantalla . '",{
                            metodo:"ponTablaSpr",
                            p : "' . $this->prm->prgs_id . '"
                        });
                    ');
                } else {
                    $subp = new Subprogramas();
                    $subp->setPrgs_id($this->prm->prgs_id);
                    $subp->setSprs_id($this->prm->sprs_id);
                    $subp->select();
                    $bitSpr->esr_id = $subp->getEsr_id();
                    $bitSpr->pdf_id = $subp->getPdf_id();

                    SubProgramas::abc($this->prm);
                    bit_SubProgramas::abc($bitSpr);

                    $this->mixScript('
                        notify("Subprograma ' . $this->prm->sprs_id . ' eliminado exitosamente");
                        comunica("' . $this->pantalla . '",{
                            metodo:"ponTablaSpr",
                            p : "' . $this->prm->prgs_id . '"
                        });
                    ');
                }
                break;
            case 'c':
                $spr = new Subprogramas();
                $spr->setPrgs_id($this->prm->prgs_id);
                $spr->setSprs_id($this->prm->sprs_id);
                $spr->select();
                $bitSpr = new stdClass();
                $bitSpr->esr_id = $spr->getEsr_id();
                $bitSpr->pdf_id = $spr->getPdf_id();
                $bitSpr->prgs_id = $spr->getPrgs_id();
                $bitSpr->sprs_id = $spr->getSprs_id();
                $bitSpr->bit_observacion = 'EL SUBPROGRAMA FUE MODIFICADO';
                $bitSpr->abc = 'a';

                $this->prm->sprs_nombre = $this->prm->subprograma;
                $this->prm->fin_id = $this->prm->fin_id;
                #Programa Presupuestario
                $this->prm->efn_id = $this->prm->efn_id;

                Subprogramas::abc($this->prm);
                bit_Subprogramas::abc($bitSpr);

                $this->mixScript('
                    notify("Subprograma actualizado exitosamente");
                    comunica("' . $this->pantalla . '",{
                        metodo:"ponTablaSpr",
                        p:"' . $spr->getPrgs_id() . '"
                    });
                ');

                break;
        }
    }

    private function validaPrograma()
    {
        $tipo = $this->prm->tipo_Programa;
        $val = $this->prm->nprg_id;
        $error = 0;
        $msg = '';
        if ($this->prm->abc == 'a') {
            switch (true) {
                case in_array($val, range(0, 99)) && $tipo != 2:
                    //Actividades Centrales de Gestión");
                    $error = 1;
                    $msg = 'El numero del programa debe de estar entre el 000 y 099';
                    break;
                case in_array($val, range(100, 899)) && $tipo != 1:
                    $error = 1;
                    $msg = 'El numero del programa debe de estar entre el 100 y 899';
                    //Programas Orientados a Resultados");
                    break;
                case in_array($val, range(900, 999)) && $tipo != 3:
                    $error = 1;
                    $msg = 'El numero del programa debe de estar entre el 900 y 999';
                    //Gastos no Asignables a Programas
                    break;
            }

            if (strlen($val) > 3) {
                $error = 1;
                $msg = 'Número de Programa no válido';
            }

            if ($error == 1) {
                $this->mixScript('
                    notify("' . $msg . '", "error");
                    $("#nprg_id").focus();
                ');
            } else {
                $this->conectar();
                $this->setSql('SELECT prgs_id FROM cat_Programas WHERE prgs_id = ?');
                $this->consultar([$val]);
                $this->desconectar();

                if ($this->getNumeroRegistros() > 0) {
                    $this->mixScript('
                        notify("El Programa ' . $val . ' ya existe", "error");
                        $("#nprg_id").focus();
                    ');
                }
            }
        }
    }

    private function ponTablaHistorial()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $titulo = (!empty($this->prm->historial) && $this->prm->historial == 'prg') ? 'Bitacora del programa' : 'Bitacora del subprograma';

        $tabla = new stdClass();
        $tabla->titulo = $titulo;
        $tabla->id = 'tbl-Historial';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaHistorial';
        $tabla->comunica->historial = (!empty($this->prm->historial)) ? $this->prm->historial : '';
        $tabla->comunica->prgs_id = (!empty($this->prm->prgs_id)) ? $this->prm->prgs_id : '';
        $tabla->comunica->sprs_id = (!empty($this->prm->sprs_id)) ? $this->prm->sprs_id : '';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '30px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Hora';
        $th->width = '30px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Situación';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Comentario';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '100px';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'fecha';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'hora';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'estado';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'comentario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $modal = '<div id="myModal" class="modal in modal-al80porciento in" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
            <div class="modal-dialog modal-xl">
                <br /><br /><br /><br />
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Bitacora</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">' . $this->wrPonTabla($tabla) . '</div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);
        $this->llenaTablaHistorial();

        // Si es un programa, regresamos el menú principal ... si es un subprograma regresamos al listado de programas
        if (!empty($this->prm->historial) && $this->prm->historial == 'prg') {
            $vinculaRegreso = 'homeMenu';
        } else {
            $vinculaRegreso = 'cancelS';
        }

        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { 
                e.preventDefault(); 
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#' . $vinculaRegreso . '").click(); e.stopPropagation(); });         
                $("#myModal").remove(); 
                e.stopPropagation(); 
            });
            $(".cerrar").click(function(){ $("#myModal").remove(); });
        ');
    }

    private function llenaTablaHistorial()
    {
        $sql = '';
        $params = [];
        switch ($this->prm->historial) {
            case 'prg':
                $sql .= 'select date(p.usr_fecha) as fecha, time(p.usr_fecha) as hora,epr_id as id_estado,epr_nombre as estado,
                    bit_observacion as comentario,p.usr_id as idusuario,usr_nombre, usr_paterno,usr_materno
                    from ' . BD_BITACORA . '.bit_Programas p
                    left join cat_Programas b using(prgs_id,epr_id,usr_id)
                    join ' . BD_USUARIO . '.cat_Usuario u using(usr_id)
                    join cat_EstadoPrograma using(epr_id)
                    where prgs_id = ?
                    order by p.usr_fecha;';
                array_push($params, $this->prm->prgs_id);
                break;
            case 'spr':
                $sql .= 'select date(sp.usr_fecha) as fecha, time(sp.usr_fecha) as hora,esr_id as id_estado,esr_nombre as estado,
                        bit_observacion as comentario,sp.usr_id as idusuario,usr_nombre,usr_paterno,usr_materno
                        from ' . BD_BITACORA . '.bit_Subprogramas sp
                        left join cat_Subprogramas b using(prgs_id,sprs_id,esr_id,usr_id)
                        join ' . BD_USUARIO . '.cat_Usuario u using(usr_id)
                        join cat_EstadoSubprograma using(esr_id)
                    where prgs_id = ? and sprs_id = ?
                    order by sp.usr_fecha;';
                array_push($params, $this->prm->prgs_id, $this->prm->sprs_id);
                break;
        }

        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Historial';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();'
        );
    }

    private function validaDatosPrograma()
    {
        // Variable auxiliar para realizar desvalidacion
        $desvalida = true;
        $mensaje = 'El programa no se puede desvalidar porque ';
        $prg_id = $this->prm->prgs_id;

        $programa = new Programas($this->prm);
        if (isset($this->prm->abc) && $this->prm->abc == 'b') {
            $this->prm->abc = 'b';
        } else {
            $this->prm->abc = 'c';
        }

        $observacion = '';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = $_SESSION[_S]['usr_id'];
        switch ($this->prm->tarea) {
            case 'desvalidar':
                $this->conectar();
                //Consulta para verificar que el programa no tenga obras, proyectos o subprogramas vinculados
                $this->setSql('
                    SELECT prgs_id 
                    FROM enc_UrSubprogramas
                    WHERE prgs_id = ?;
                ');

                $this->consultar([$prg_id]);
                if ($this->getNumeroRegistros() != 0) {
                    $desvalida = false;
                    $mensaje .= ' - aun hay URs vinculadas';
                }

                //Consulta para verificar que el programa no tenga obras validadas
                $this->setSql('
                    SELECT prg_id 
                    FROM cat_ObraActividad
                    WHERE prg_id = ? AND oa_id !=0 AND eoa_id=7;
                ');
                $this->consultar([$prg_id]);

                if ($this->getNumeroRegistros() != 0) {
                    $desvalida = false;
                    $mensaje .= ' - aun tiene obras/actividades validadas ';
                }

                //Para los programas 100 - 899 se valida que no tenga mir
                if ($prg_id > 99 && $prg_id < 900) {
                    //Consulta para verificar que el programa no tenga mir validada
                    $this->setSql('SELECT prgs_id FROM vin_ProgramasMir JOIN cat_Mir USING(mir_id) WHERE prgs_id = ? AND emi_id=11;
                    ');
                    $this->consultar([$prg_id]);

                    if ($this->getNumeroRegistros() != 0) {
                        $desvalida = false;
                        $mensaje .= ' - la MIR esta validada ';
                    }

                    /*verifica lo de MIR*/
                    $registrosMIR = 0;
                    //Obtiene vinculacion programa MIR
                    $sql = 'SELECT vpm_id  FROM vin_ProgramasMir WHERE prgs_id = ?;';

                    $this->setSql($sql);
                    $this->consultar([$prg_id]);
                    $vinculacionMir = $this->extraer();
                    $vpm_id = $vinculacionMir['vpm_id'];

                    if ($this->getNumeroRegistros() > 0) {
                        // Para los programas 100 - 899 se valida que no tenga registros en mir
                        $sql = '
                            SELECT count(vpm_id) AS reg
                            FROM (
                                
                                SELECT vpm_id FROM cat_SupuestoProgramaMir
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM cat_MedioVerificacionProgramaMir
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramaMirIndicador
                                JOIN cat_MetaFin USING(pmi_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramaMirIndicador
                                JOIN cat_MetaProposito USING(pmi_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_SupuestoComponente USING(prgs_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_MedioVerificacionComponente USING(prgs_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_MetaComponente USING(prgs_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_SupuestoActividad USING(prgs_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_MedioVerificacionActividad USING(prgs_id)
                                WHERE vpm_id= :vpm_id
                                UNION ALL
                                SELECT vpm_id FROM enc_Programatico ep
                                INNER JOIN vin_ProgramasMir vp ON ep.prg_id=vp.prgs_id
                                -- INNER JOIN cat_MetaActividad USING(epg_id)
                                WHERE vpm_id= :vpm_id
                            )Res
                            ;
                        ';
                        $this->setSql($sql);
                        $this->consultar([':vpm_id' => $vpm_id]);
                        $registros = $this->extraer();
                        $registrosMIR = $registros['reg'];
                        if ($registrosMIR > 0) {
                            $desvalida = false;
                            $mensaje .= ' - la MIR del programa tiene informacion capturada ';
                        }
                    }
                }

                // Verificará si se realiza la validación
                if (!$desvalida) {
                    $this->mixScript('
                        notify("' . $mensaje . '","error")
                        comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
                    ');
                } else {
                    $this->conectar();
                    $this->setSql('SET @actualizaNombreur = 2;');
                    $this->consultar();

                    $this->setSql('
                        UPDATE cat_Programas
                        SET epr_id = 3 
                        WHERE prgs_id = ?
                    ');
                    $this->consultar([$prg_id]);
                    $this->desconectar();
                    $observacion = 'EL PROGRAMA SE DESVALIDÓ POR USUARIO:' . $usr_id . ', IP:' . $ip . '';

                    $this->conectar();
                    $sqlBitacora = 'INSERT INTO ' . BD_BITACORA . '.bit_Programas (prgs_id,epr_id,bit_observacion,usr_ip,usr_id)
                                VALUES( ?, 3, ?, INET_ATON( ? ), ?)';
                    $params = [$prg_id, $observacion, $ip, $usr_id];
                    $this->setSql($sqlBitacora);
                    $this->consultar($params);
                    $this->desconectar();

                    $this->mixScript('
                        notify("El programa se ha desvalidado de manera correcta ","success")
                        comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
                    ');
                }
                break;
            case 'autorizar':
                $epr_id = 0;
                switch ($this->prm->edo) {
                    case 5:
                        switch ($programa->getEpr_id()) {
                            case 1:
                            case 3:
                                $epr_id = 5;
                                break;
                            case 6:
                                $epr_id = 4;
                                break;
                        }
                        break;
                    case 6:
                        switch ($programa->getEpr_id()) {
                            case 1:
                            case 3:
                                if ($programa->getPrgs_id() > 99 && $programa->getPrgs_id() < 900) {
                                    $epr_id = 6;
                                } else {
                                    $epr_id = 4;
                                }
                                break;
                            case 5:
                                $epr_id = 4;
                                break;
                        }
                        break;
                }

                if (($epr_id == 4 || $epr_id == 6) && ($prg_id > 99 && $prg_id < 900) && $this->prm->total == 0) {
                    $this->mixScript('notify("El campo Total de la sección Población Potencial debe ser mayor a 0","error")');
                } else {

                    $this->conectar();
                    $this->setSql('SET @actualizaNombreur = 2;');
                    $this->consultar();

                    $sql = 'UPDATE cat_Programas SET epr_id = ? WHERE prgs_id = ?';
                    $this->setSql($sql);
                    $this->consultar([$epr_id, $prg_id]);

                    $this->desconectar();


                    $observacion = 'EL PROGRAMA SE AUTORIZÓ CON ESTADO ' . $epr_id . ' POR USUARIO:' . $usr_id . ', IP:' . $ip . '';
                    //bitacora
                    $this->conectar();
                    $sqlBitacora = 'INSERT INTO ' . BD_BITACORA . '.bit_Programas (prgs_id,epr_id,bit_observacion,usr_ip,usr_id) VALUES(?, ?, ?, INET_ATON( ? ), ?)';
                    $params = [$prg_id, $epr_id, $observacion, $ip, $usr_id];
                    $this->setSql($sqlBitacora);
                    $this->consultar($params);
                    $this->desconectar();

                    $this->mixScript('
                        notify("Programa validado","success")
                        comunica("' . $this->pantalla . '",{metodo:"PonTablaPrg"});
                    ');
                }
                break;
        }
    }

    private function validaSubprograma()
    {
        $prg_id = $this->prm->prgs_id;
        $spr_id = $this->prm->sprs_id;
        $this->prm->pdf_id = $this->prm->pdf_id;
        $subprograma = new Subprogramas($this->prm);
        if (isset($this->prm->abc) && $this->prm->abc == 'b') {
            $this->prm->abc = 'b';
        } else {
            $this->prm->abc = 'c';
        }

        $observacion = '';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = $_SESSION[_S]['usr_id'];
        switch ($this->prm->tarea) {
            case 'desvalidar':
                $mensaje = '';
                $registrosURVinculadas = 0;
                $registrosMIR = 0;
                $this->conectar();
                $this->setSql('SELECT prgs_id FROM enc_UrSubprogramas WHERE prgs_id = ? AND sprs_id = ?');
                $this->consultar([$prg_id, $spr_id]);
                $registrosURVinculadas = $this->getNumeroRegistros();

                if ($registrosURVinculadas > 0) {
                    $mensaje .= 'El subprograma no se puede desvalidar porque aun hay URs vinculadas, por favor desvincule primero el subprograma de las URs asignadas';
                }

                if ($prg_id > 99 && $prg_id < 900) {
                    //Obtiene vinculacion programa MIR
                    $sql = 'SELECT vpm_id  FROM vin_ProgramasMir WHERE prgs_id = ?;';
                    $this->setSql($sql);
                    $this->consultar([$prg_id]);
                    $vinculacionMir = $this->extraer();
                    $vpm_id = $vinculacionMir['vpm_id'];

                    if ($this->getNumeroRegistros() > 0) {
                        // Para los programas 100 - 899 se valida que no tenga registros en mir
                        $sql = '
                            SELECT count(vpm_id)
                            FROM
                            (
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_SupuestoComponente USING(prgs_id)
                                WHERE vpm_id = :vpm_id AND prgs_id = :prgs_id AND sprs_id = :spr_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_MedioVerificacionComponente USING(prgs_id)
                                WHERE vpm_id = :vpm_id AND prgs_id = :prgs_id AND sprs_id = :spr_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_MetaComponente USING(prgs_id)
                                WHERE vpm_id = :vpm_id AND prgs_id = :prgs_id AND sprs_id = :spr_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_SupuestoActividad USING(prgs_id)
                                WHERE vpm_id = :vpm_id AND prgs_id = :prgs_id AND sprs_id = :spr_id
                                UNION ALL
                                SELECT vpm_id FROM vin_ProgramasMir
                                INNER JOIN cat_MedioVerificacionActividad USING(prgs_id)
                                WHERE vpm_id = :vpm_id AND prgs_id = :prgs_id AND sprs_id = :spr_id
                                UNION ALL
                                SELECT vpm_id FROM enc_Programatico ep
                                INNER JOIN vin_ProgramasMir vp ON ep.prg_id=vp.prgs_id
                                -- INNER JOIN cat_MetaActividad USING(epg_id)
                                WHERE vpm_id = :vpm_id AND prgs_id = :prgs_id AND spr_id = :spr_id
                            )Res
                            ;
                        ';
                        $params = [':vpm_id' => $vpm_id, ':prgs_id' => $prg_id, ':spr_id' => $spr_id];
                        $this->setSql($sql);
                        $this->consultar($params);
                        $registros = $this->extraer();
                        $registrosMIR = $registros['reg'];
                        if ($registrosMIR > 0) {
                            $mensaje .= ' - la Unidad Responsable ya ha capturado informacion de la MIR a nivel Componente o Actividad ';
                        }
                    }
                }

                if ($registrosURVinculadas > 0 || $registrosMIR > 0) {
                    $this->mixScript('
                        notify("' . $mensaje . ' ","error")
                        comunica("' . $this->pantalla . '",{metodo:"ponTablaSpr",p:' . $prg_id . '});
                    ');
                } else {
                    $this->setSql('UPDATE cat_Subprogramas SET esr_id = 3 WHERE prgs_id = ? AND sprs_id = ?');
                    $this->consultar([$prg_id, $spr_id]);
                    $this->desconectar();
                    $observacion = 'EL SUBPROGRAMA SE DESVALIDÓ POR USUARIO:' . $usr_id . ', IP:' . $ip . '';
                    // Estado rechazado
                    $edo = 3;
                    $sqlBitacora = 'INSERT INTO ' . BD_BITACORA . '.bit_Subprogramas (bit_observacion,prgs_id,sprs_id,esr_id,pdf_id,usr_id) VALUES (?, ?, ?, ?, ?, ?)';
                    $params = [$observacion, $prg_id, $spr_id, $edo, $this->prm->pdf_id, $usr_id];
                    $this->conectar();
                    $this->setSql($sqlBitacora);
                    $this->consultar($params);
                    $this->desconectar();

                    $this->mixScript('
                        notify("El subprograma se ha desvalidado de manera correcta ","success")
                        comunica("' . $this->pantalla . '",{metodo:"ponTablaSpr",p:' . $prg_id . '});
                    ');
                }
                break;
            case 'autorizar':
                $edo = 0;
                switch ($this->prm->edo) {
                    case 5:
                        switch ($subprograma->getEsr_id()) {
                            case 1:
                            case 3:
                                $edo = 5;
                                break;
                            case 6:
                                $edo = 4;
                                break;
                        }
                        break;
                    case 6:
                        switch ($subprograma->getEsr_id()) {
                            case 1:
                            case 3:
                                if ($prg_id > 99 && $prg_id < 900) {
                                    $edo = 6;
                                } else {
                                    $edo = 4;
                                }
                                break;
                            case 5:
                                $edo = 4;
                                break;
                        }
                        break;
                }
                $this->conectar();
                $sql = 'UPDATE cat_Subprogramas SET esr_id = ? WHERE prgs_id = ? AND sprs_id = ?';
                $this->setSql($sql);
                $this->consultar([$edo, $prg_id, $spr_id]);
                $this->desconectar();

                $observacion = 'EL SUBPROGRAMA SE AUTORIZÓ POR USUARIO:' . $usr_id . ', IP:' . $ip . '';

                $sqlBitacora = 'INSERT INTO ' . BD_BITACORA . '.bit_Subprogramas (bit_observacion, prgs_id, sprs_id, esr_id, pdf_id, usr_id) VALUES (?, ?, ?, ?, ?, ?)';
                $params = [$observacion, $prg_id, $spr_id, $edo, $this->prm->pdf_id, $usr_id];
                $this->conectar();
                $this->setSql($sqlBitacora);
                $this->consultar($params);
                $this->desconectar();

                $this->mixScript('
                    notify("Subprograma validado","success")
                    comunica("' . $this->pantalla . '",{metodo:"ponTablaSpr",p:' . $prg_id . '});
                ');
                break;
        }
    }

    private function datosComplementarios($prg = '')
    {
        $prg = (empty($prg)) ? new Programas() : $prg;
        $this->setContenedor('complemento');
        $lectura = $_SESSION[_S]['nvj_id'] < 3;
        #si es programa orientado a resultados mostrar encuadres
        #ped
        $ped = new stdClass();
        $ped->cargaEncuadres = true;
        #pes
        $pes = new stdClass();
        #programa presupuestario
        #pep
        $pep = new stdClass();
        #Plan Institucional
        $pin = new stdClass();
        #encuadre funcional
        $catFun = new stdClass();
        $catFun->prgs_id = $prg->getPrgs_id();
        #PLAN INSTITUCIONAL
        $botonPedt = new stdClass();
        $botonPedt->id = 'botonPedPlus';
        $botonPedt->color = 'azul';
        $botonPedt->icon = 'plus';
        $botonPedt->title = 'Agregar';
        $botonPedt->class = 'me-2';
        $prgs_id = $prg->getPrgs_id() ? ',prgs_id:' . $prg->getPrgs_id() . '' : ',prgs_id: 0';
        $botonPedt->click = 'comunica("poa/programacion/ControladorEstructuraProgramatica.php",{ metodo:"muestraPlanInstitucional"' . $prgs_id . ' });';
        $filasPI = '';
        if ($prg->getPrgs_id() != 0) {

            $this->setSql('SELECT epi_id,pi_nombre, Descripcion 
            FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id) 
            JOIN cat_PlanInstitucional USING (pi_id)
            JOIN vin_ProgramaPI USING (epi_id)
            WHERE prgs_id = ?;');
            $params = [$prg->getPrgs_id()];
            $this->conectar();
            $this->consultar($params);
            $this->desconectar();
            while ($r = $this->extraer()) {
                $boton = new stdClass();
                $boton->title = 'Eliminar Plan  Institucional';
                $boton->id = 'ept_' . $r['epi_id'];
                $boton->color = 'rojo';
                $boton->icon = 'trash-can';
                if ($prg->getEpr_id() == 4) {
                    $boton->disabled = true;
                }
                $boton->click = 'comunica("poa/programacion/ControladorEstructuraProgramatica.php",{metodo:"eliminaPi",prgs_id:' . $prg->getPrgs_id() . ',epi_id:"' . $r['epi_id'] . '"}); ';
                $botonEliminar = $this->ponBoton($boton);
                $filasPI .= '<tr class="registro" id="filas-' . $r['epi_id'] . '"><td>' . $r['pi_nombre'] . '</td><td>' . $r['Descripcion'] . '</td><td>' . $botonEliminar . '</td>';
            }
        }

        if ($prg->getPrgs_id() != 0) {
            #si el programa ya esta validado no se coloca el boton para agregar politicas transversales

            $this->conectar();
            $this->setSql('SELECT prg_id FROM cat_Programa WHERE prg_id = ?;');
            $this->consultar([$prg->getPrgs_id()]);
            $this->desconectar();

            if ($prg->getEpr_id() == 4 || !$prg->vinculacionModificable() || $lectura || empty($_SESSION[_S]['prm']['A']['2']['3']['2'])) {
                $lectura = true;
                $ped->disabled = true;
                $pes->disabled = true;
                //$prpr->disabled = true;
                $pep->disabled = true;
                $pin->disabled = true;
                $catFun->disabled = true;
                $botonPedt->disabled = true;
                $botonPedTrans = '';
            }
            // PED
            $ped->epo_id = $prg->getEpo_id();
            $ped->epods_id = $prg->getEpods_id();
            $ped->prg_id = $prg->getPrgs_id();
            // PES
            $pes->spe_id = $prg->getSpe_id();
            $pes->obp_id = $prg->getObp_id();
            // PEP
            $pep->prg_id = $prg->getPrgs_id();
            $pin->epi_id = $prg->getEpi_id();
            //$prpr->lectura = $ped->lectura = $catFun->lectura = $pep->lectura = $pes->lectura = $lectura;
        }
        #ur responsable del programa

        $_SESSION[_S]['resp'] = new stdClass();
        // Si pasaron el idPrograma y no está vacío ... es una consulta solamente
        $urs = new stdClass();
        $urs->id = 'ueres';
        $urs->comoAtr = 'gpo_id,ur_id';
        $urs->lectura = $lectura;
        $urs->required = true;
        $urs->search = true;
        //SELECCIONAR TODAS LAS UR PARA MOSTRAR EN EL COMBO
        $urs->sql = 'SELECT ur_clave,ur_nombre,gpo_id,ur_id  FROM cat_UnidadResponsable WHERE (gpo_id,ur_id) not in ((1,15)) AND gpo_id  not in (0);';

        if ($prg->getPrgs_id() != '') {
            $this->conectar();
            $this->setSql('SELECT CONCAT(gpo_id,ur_id) AS ur_clave,gpo_id,ur_id,prgs_id FROM vin_ProgramasMir WHERE prgs_id = ?;');
            $this->consultar([$prg->getPrgs_id()]);
            $this->desconectar();
            $reg = $this->extraer();
            if ($this->getNumeroRegistros() > 0) {
                $urs->selecciona = $reg['ur_clave'];
            }
        }


        $urResponsable = '
            <legend class="col-md-9">
            <legend>Ejecutores de gasto coordinador</legend>
            ' . $this->ponCombo($urs) . ' ' . (($_SESSION[_S]['nvj_id'] > 2) ?
            '<label class="mt-2" style="color:red;">Nota: La asignación de UR responsable es un campo obligatorio. ' : '') . '
        ';
        $this->mixHtml(
            '<div class="row">' .
                '<div class="col-md-6">' .
                PedObjetivo::ponPed($ped) .
                '</div>
                <div class="col-md-6">' .
                Pep::ponPep($pep) .
                '</div>
                <div class="col-md-6">' .
                Pes::ponPes($pes) .
                '</div>
                <div class="col-md-6">' .
                CatalogoFuncional::ponCatalogoFuncional($catFun) .
                '</div>
                <div class="col-md-6">' .
                $urResponsable .
                '</div>

                <!-- PI, Plan Institucional -->
                <div class="col-md-6">
                    <legend>' . $this->ponBoton($botonPedt) . 'Plan Institucional</legend>
                    <div id="modalPedTransversal"></div>
                        <table class="table table-striped"><thead><tr><th>Plan</th><th>Objetivo PI</th><th>Eliminar</th></thead><tbody id="filas">' . $filasPI . '</tbody></table>
                        </div>
                </div>
            </div>
        '
        );
        $this->mixScript(' 
            var ArraysObjetivosPi = [];
            var ept = [];');
    }

    private function tablaVinculacionUR($prg, $porSpr)
    {
        $prm = '';
        $tabla = new stdClass();
        $tabla->id = 'listaUrsVinculadas';
        $tabla->titulo = 'Lista de UR\'S Vinculadas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaUrs';
        $tabla->comunica->prgs_id = $prg->getPrgs_id();
        if ($porSpr) {
            $tabla->comunica->sprs_id = $prg->getSprs_id();
            $tabla->comunica->porspr = true;
            $prm = ',sprs_id:' . $prg->getSprs_id() . ',porspr:' . $porSpr . '';
        }

        $tabla->comunica->idTabla = 'listaUrsVinculadas';
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['gpo_id', 'ur_id'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'noTiene';
        $tabla->casoSeleccionable->valor = '1';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(255, 0, 0, 0.20)';
        $tabla->filasAmostrar = [5, 10, 20, 50, 100, 120];
        $tabla->numFilas = 5;

        $tr = [];
        $trbody = [];
        $th = new stdClass();
        $th->texto = 'Gpo';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Ur';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UR Nombre';
        array_push($tr, $th);

        $td = new stdClass();
        $td->campoTxt = 'gpo_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $ponBoton = '';
        if ($_SESSION[_S]['nvj_id'] >= 3 && (isset($_SESSION[_S]['prm']['A']['2']['3']['2']))) {
            $boton = new stdClass();
            $boton->icon = 'chevron-left';
            $boton->color = 'verde';
            $boton->click = '
                if( seleccionados = $("#listaUrsNoVinculadas").tabla("getFilaSeleccion") ){
                    comunica("' . $this->pantalla . '",{metodo:"vincular",ueres:seleccionados,prgs_id:"' . $prg->getPrgs_id() . '"' . $prm . '},false);
                }
            ';
            $boton->id = 'vincular';
            $ponBoton = $this->ponBoton($boton);
        }
        $ponBoton2 = '';
        if ($_SESSION[_S]['nvj_id'] >= 3 && (isset($_SESSION[_S]['prm']['A']['2']['3']['2']))) {
            $boton2 = new stdClass();
            $boton2->icon = 'chevron-right';
            $boton2->color = 'verde';
            $boton2->click = '
                if( seleccionados = $("#listaUrsVinculadas").tabla("getFilaSeleccion") ){
                    comunica("' . $this->pantalla . '",{ metodo:"desvincular",ueres:seleccionados,prgs_id:"' . $prg->getPrgs_id() . '"' . $prm . ' },true);
                }
            ';
            $boton2->id = 'desvincular';
            $ponBoton2 = $this->ponBoton($boton2);
        }

        $programa = (isset($this->prm->prgs_id)) ? $this->prm->prgs_id : '';

        if ($programa != '') {
            $pr = new Programas($this->prm);
        } else {
            $pr = new Programas();
        }

        $prg_id = new stdClass();
        $prg_id->id = 'nprg_id';
        $prg_id->label = 'Número';
        $prg_id->required = true;
        $prg_id->enDiv = true;
        $prg_id->maxlength = 3;
        $prg_id->value = $pr->getPrgs_id();

        $prg = new stdClass();
        $prg->id = 'programa';
        $prg->label = 'Nombre del programa:';
        $prg->required = true;
        $prg->enDiv = true;
        $prg->maxlength = 200;
        $prg->width = '100px';
        $prg->height = '100px';
        $prg->value = $pr->getNombre();

        if (isset($this->prm->sprs_id) != 1) {
            #población potencial
            $pop = new stdClass();
            $pop->id = 'pbf_id';
            $pop->sql = 'SELECT pbf_id,pbf_nombre FROM cat_PoblacionBeneficiada';
            $pop->selecciona = $pr->getPbf_id();
            $pop->label = 'Población Potencial';
            #hombres
            $sprPoblacion = new Subprogramas($this->prm);
            $sprPoblacion->sql = 'SELECT sprs_MedioVerificacion FROM cat_Subprogramas';

            $hombres = new stdClass();
            $hombres->id = 'hombres';
            $hombres->formato = 'entero';
            $hombres->required = true;
            $hombres->label = 'Hombres';
            $hombres->maxlength = 10;
            $hombres->enDiv = true;
            $hombres->blur = '
                $("#total").val( ($("#mujeres").val() *1 ) + ( $(this).val() * 1) );
                if($("#total").val() * 1 == 0 )
                $("#total").removeAttr("readonly");
                else
                $("#total").attr("readonly",true);
            ';
            //$hombres->value = $sprPoblacion->getSprs_hombres();
            #mujeres
            $mujeres = new stdClass();
            $mujeres->id = 'mujeres';
            $mujeres->formato = 'entero';
            $mujeres->required = true;
            $mujeres->label = 'Mujeres';
            $mujeres->enDiv = true;
            $mujeres->blur = '
                $("#total").val( ( $("#hombres").val() *1 ) + ( $(this).val() * 1 ) );
                if($("#total").val() * 1 == 0 )
                $("#total").removeAttr("readonly");
                else
                $("#total").attr("readonly",true);
            ';
            //$mujeres->value = $sprPoblacion->getSprs_mujeres();

            #total
            $total = new stdClass();
            $total->id = 'total';
            $total->formato = 'entero';
            $total->label = 'Total';
            $total->required = true;
            $total->enDiv = true;
            //$total->value = $sprPoblacion->getSprs_total();

            $total->blur = '
                if( $(this).val() * 1 == 0 ){
                    $("#mujeres,#hombres").removeAttr("readonly");
                }else if($("#mujeres").val()!=0 || $("#hombres").val()!=0){ 
                    if($("#total").val()!= ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1)){
                        $("#mujeres").val(0);
                        $("#hombres").val(0);
                        $("#mujeres,#hombres").attr("readonly",true);
                    }else{
                        $("#total").val( ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1) );
                        $("#total").attr("readonly",true);
                        $("#mujeres,#hombres").removeAttr("readonly");
                    }
                }else{
                    $("#mujeres").val(0);
                    $("#hombres").val(0);
                    $("#mujeres,#hombres").attr("readonly",true);
                }
            ';
        }

        #Medio de Verificación
        $medVerificacion = new stdClass();
        $medVerificacion->id = 'medVerificacion';
        $medVerificacion->label = '';
        $medVerificacion->formato = 'numfolio';
        $medVerificacion->required = true;
        $medVerificacion->maxlength = 2000; //Máximo300
        $medVerificacion->style = 'text-transform: none;!important';
        $medVerificacion->enDiv = true;

        $spr = new Subprogramas($this->prm);
        $spr->sql = 'SELECT sprs_MedioVerificacion FROM cat_Subprogramas';

        $catFun = new stdClass();
        $catFun->prgs_id = $tabla->comunica->prgs_id;
        $catFun->sprs_id = $tabla->comunica->sprs_id; //sprs_id ayuda a identificar que se utiliza el catálogo para subprogramas

        #programa presupuestario
        $prpr = new stdClass();
        if (1 != 1) { //$prg->getEpr_id() == 4
            $prpr->disabled = false; //Revisar condicionales
        }

        /* PROGRAMA PRESUPUSTARIO */
        $prpr->epp_id = $spr->getEpp_id(); //Consultar epp_id para subprograma
        $prpr->lectura = true; // $ped->lectura = $catFun->lectura = $pep->lectura = $pes->lectura = $lectura;

        $this->mixHtml('
            <div class="row">
                <div class="col-md-5">' . $this->wrPonTabla($tabla) . '
                </div>
                <div class="col-md-1">' . $ponBoton . ' ' . $ponBoton2 . '</div>
                <div class="col-md-6">
        ');
        $tabla->titulo = 'Lista de UR\'S no vinculadas';
        $tabla->id = 'listaUrsNoVinculadas';
        $tabla->comunica->idTabla = 'listaUrsNoVinculadas';
        $tabla->filasAmostrar = [20, 50, 100, 150, 200, 300, 500];
        $tabla->numFilas = 100;
        $this->mixHtml($this->wrPonTabla($tabla) . '</div></div> ');

        $this->mixScript('
            ' . $this->wrllamaLlenaTabla('listaUrsNoVinculadas') . ';
            ' . $this->wrllamaLlenaTabla('listaUrsVinculadas') . ';
        ');
    }
    private function muestraPlanInstitucional()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Plan Institucional</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="PoliticaTransversal">
                        <div class="row">
                        ' . Pin::ponPin($this->prm) . '
                        </form>
                    </div>
                    <div class="modal-footer">';

        $modal .= '<button type="submit" class="btn btn-outline-success" id="btnGuardarPolitica" form="">Aceptar</button>';


        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPt" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modal);

        $prgs_id = (isset($this->prm->prgs_id)) ? ',prgs_id:' . $this->prm->prgs_id . '' : '';

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {$("#myModal").modal().remove();});
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPt").click(function(){ $("#myModal").modal().remove(); });

            $("#btnGuardarPolitica").click(function(){
                $("#modalPedTransversal").empty();
                comunica("poa/programacion/ControladorEstructuraProgramatica.php",{metodo: "agregaPi"' . $prgs_id . '});
                $("#myModal").modal().remove();
            });
        ');
    }
    public function agregaPi()
    {
        $this->prm->epi_id = $_SESSION[_S]['pin']->epi;
        $a = new AccesoDatos();
        $a->conectar();
        $a->setSql('SELECT epi_id,pi_nombre, Descripcion 
            FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id) 
            JOIN cat_PlanInstitucional USING (pi_id) WHERE epi_id = ?;');
        $params = [$this->prm->epi_id];
        $a->consultar($params);
        $a->desconectar();
        $datos = $a->extraer();

        $boton = new stdClass();
        $boton->title = 'Eliminar Plan Institucional';
        $boton->id = 'ept_' . $this->prm->epi_id;
        $boton->color = 'rojo';
        $boton->icon = 'trash-can';

        if ($this->prm->prgs_id) {
            $boton->click = '';
        }
        $fila = '
            $("#filas").append(
                $("<tr>").attr("id","filas-' . $this->prm->epi_id . '")
                .attr("class","registro")
                .append("<td>' . $datos['pi_nombre'] . '</td>")
                .append("<td>' . $datos['Descripcion'] . '</td>")
                .append("<td>' . addslashes($this->ponBoton($boton)) . '</td>")
            );';
        $this->mixScript('
            if(ArraysObjetivosPi.indexOf("05") < 0){
                if(($("#filas-01").length ==1 || $("#filas-02").length ==1 || $("#filas-03").length ==1 || $("#filas-04").length ==1)){
                    $("#botonPedPlus").attr("disabled","disabled");
                    for(var i = ArraysObjetivosPi.length - 1; i >= 0; i--) {
                        $(".registro").remove();
                        ArraysObjetivosPi.splice(i, 1);
                        ept.splice(i, 1);
                    }
                    ArraysObjetivosPi.push("' . $this->prm->epi_id . '");
                    ept.push({"epi_id":"' . $this->prm->epi_id . '","plan":"' . $datos['pi_nombre'] . '","objetivo":"' . $datos['Descripcion'] . '"});
                    ' . $fila . '
                }else{
                    if(ArraysObjetivosPi.indexOf("' . $this->prm->epi_id . '") < 0 ){
                            ArraysObjetivosPi.push("' . $this->prm->epi_id . '");
                            ept.push({"epi_id":"' . $this->prm->epi_id . '","plan":"' . $datos['pi_nombre'] . '","objetivo":"' . $datos['Descripcion'] . '"});
                                ' . $fila . '
                            $("#ept_' . $this->prm->epi_id . '").click(function(){
                                $("#filas-' . $this->prm->epi_id . '").remove();
                                ept.splice( ArraysObjetivosPi.indexOf("' . $this->prm->epi_id . '"),1);
                                ArraysObjetivosPi.splice( ArraysObjetivosPi.indexOf("' . $this->prm->epi_id . '"),1);
                                comunica("poa/programacion/ControladorEstructuraProgramatica.php",{metodo:"eliminaPi",prgs_id:' . $this->prm->prgs_id . ',epi_id:"' . $this->prm->epi_id . '"}); 
                            });

                    }else{
                        alertify.alert("El Plan ya está agregada");
                        }
                }
            }else {$("#botonPedPlus").attr("disabled","disabled");}
        ');
        if ($this->prm->prgs_id) {
            $sql = 'INSERT INTO vin_ProgramaPI (prgs_id,epi_id, usr_id) VALUES(?, ?, ?);';
            $params = [$this->prm->prgs_id, $this->prm->epi_id, $_SESSION[_S]['usr_id']];
            $a->conectar();
            $a->setSql($sql);
            $a->consultar($params);
        }
    }
    private function eliminaPi()
    {
        if ($this->prm->prgs_id) {
            $this->setSql('DELETE FROM vin_ProgramaPI WHERE prgs_id = ? and epi_id = ?;');
            $this->mixScript('
            $("#filas-' . $this->prm->epi_id . '").remove();');
            $this->conectar();
            $this->consultar([$this->prm->prgs_id, $this->prm->epi_id]);
            $this->desconectar();
        }
    }

    private function llenaTablaUrs()
    {
        $this->conectar();
        $params = [];
        $sql = '';
        switch ($this->prm->idTabla) {
            case 'listaUrsVinculadas':
                $sql = '
                    SELECT  
                    DISTINCT SP.gpo_id,SP.ur_id,ur_nombre,ur_clave,sum(if(oa_id !=0,1,0)) + sum(if(PY.py_id !=0,1,0)) =0 noTiene 
                    FROM cat_UnidadResponsable 
                    JOIN enc_UrSubprogramas  SP USING(gpo_id,ur_id) 
                    INNER JOIN cat_Programa  PR  ON SP.gpo_id  = PR.gpo_id AND SP.ur_id = PR.ur_id AND SP.prgs_id = PR.prg_id
                    INNER JOIN cat_Subprograma SPR2 ON SPR2.gpo_id  = PR.gpo_id AND SPR2.ur_id = PR.ur_id AND SPR2.prg_id = PR.prg_id
                    INNER JOIN cat_Proyecto PY ON SPR2.gpo_id  = PY.gpo_id AND SPR2.ur_id = PY.ur_id AND SPR2.prg_id = PY.prg_id AND SPR2.spr_id = PY.spr_id
                    INNER JOIN cat_ObraActividad OA ON OA.gpo_id  = PY.gpo_id AND OA.ur_id = PY.ur_id AND OA.prg_id = PY.prg_id AND OA.spr_id = PY.spr_id AND PY.py_id = OA.py_id
                    WHERE OA.prg_id = ' . $this->prm->prgs_id . (($this->prm->porspr) ? ' AND OA.spr_id = ' . $this->prm->sprs_id . '' : '') . '
                ';
                $sql .= ' GROUP BY gpo_id,ur_id ORDER BY gpo_id,ur_id ';
                break;
            case 'listaUrsNoVinculadas':
                $sql = '    
                    SELECT gpo_id,ur_id,ur_nombre,ur_clave,1 noTiene,prgs_id FROM (
                    SELECT gpo_id,ur_id,1 noTiene,prgs_id FROM cat_Subprogramas
                    LEFT JOIN enc_UrSubprogramas USING ( prgs_id,sprs_id)
                    WHERE prgs_id = ' . $this->prm->prgs_id . (($this->prm->porspr) ? ' AND sprs_id = ' . $this->prm->sprs_id . '' : '') . '
                    GROUP BY gpo_id,ur_id,prgs_id,sprs_id
                    )B
                    RIGHT JOIN cat_UnidadResponsable USING (gpo_id,ur_id )
                    WHERE gpo_id!=0 AND prgs_id IS NULL
                ';
                $sql .= ' GROUP BY gpo_id,ur_id,ur_nombre,ur_clave,noTiene,prgs_id
                    ORDER BY gpo_id,ur_id ';
                break;
        }
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = $this->prm->idTabla;
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
    }

    private function vincular()
    {
        $this->conectar();
        $bandera = 1;
        //variable que determina si la UR es responsable del programa para MIR
        $responsable = 0;
        //PARAMETRO PARA DEFINIR SI EL PROGRAMA/SUBPROGRAMA DEBERÁ ESTAR VALIDADO ANTES DE SER ASIGNADO 1 - SI, 2 - NO

        $valida = 2;
        //PARAMETRO QUE DEFINE QUE MOVIMIENTO REALIZARÁ, EN ESTE CASO VINCULAR
        $mov = 'V';

        $this->setContenedor('divContenedorGeneralModal');

        $html = '<table class="table table table-bordered table-hover" >
            <tr class="warning">
                    <th class="warning" style="text-align:center" align="left" colspan="1"> UR</th>
                    <th class="warning" style="text-align:center" align="left" colspan="5">Mensaje</th>
            </tr>
        ';

        $filasmesajes = '';
        $urvinculadas = '';
        foreach ($this->prm->ueres as $ur) {

            $prg_id = $this->prm->prgs_id;
            if (!empty($this->prm->sprs_id)) {
                $spr_id = $this->prm->sprs_id;
            } else {
                $spr_id = 0;
            }

            $con = new sp_VinculaSubprogramaUr();
            $con->setGpo_id($ur->gpo_id);
            $con->setUr_id($ur->ur_id);
            $con->setPrgs_id($prg_id);
            $con->setSprs_id($spr_id);
            $con->setValidaEstado($valida);
            $con->setSis_id('POA');
            $con->setMov($mov);
            $con->setEjercicio(APP_EJERCICIO);

            $con->setBit_observacion('SE VINCULA DESDE LA PANTALLA');
            $r = $con->execute();

            if ($r->bandera != 1) {
                $bandera = 0;
                $filasmesajes .= '<tr style="background-color:rgb(255,0,0,.20);">';
                $filasmesajes .= '<td >' . $ur->gpo_id . $ur->ur_id . '</td>';
                $filasmesajes .= '<td>' . $r->mensaje . '</td>';
                $filasmesajes .= '</tr>';
            } else {
                if ($urvinculadas == '') {
                    $urvinculadas .= $ur->gpo_id . $ur->ur_id;
                } else {
                    $urvinculadas .= ', ' . $ur->gpo_id . $ur->ur_id;
                }
                $filasmesajes .= '<tr>';
                $filasmesajes .= '<td>' . $ur->gpo_id . $ur->ur_id . '</td>';
                $filasmesajes .= '<td>Ha sido vinculada </td>';
                $filasmesajes .= '</tr>';
                $bandera = 1;
            }
        }
        $urvinculadas = '(' . $urvinculadas . ')';
        $this->desconectar();
        if ($filasmesajes != '') {
            $html .= $filasmesajes . '</table>';
            $this->mixHtml('
                <div id="myModal" class="modal " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xs">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Resultado de la Vinculación</h4>
                                <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                    <i class="fa fa-xmark"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").modal().show();
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();             
                });
                $(".cerrar").click(function(){ $("#myModal").remove(); });
            ');
        }

        if ($urvinculadas != '()') {
            $this->prm->idTabla = 'listaUrsVinculadas';
            $this->llenaTablaUrs(!empty($this->prm->sprs_id));
            $this->prm->idTabla = 'listaUrsNoVinculadas';
            $this->llenaTablaUrs(!empty($this->prm->sprs_id));
        }
    }

    private function desvincular()
    {
        $this->conectar();

        $bandera = 1;
        //variable que determina si la UR es responsable del programa para MIR
        $responsable = 0;
        //PARAMETRO PARA DEFINIR SI EL PROGRAMA/SUBPROGRAMA DEBERÁ ESTAR VALIDADO ANTES DE SER ASIGNADO 1 - SI, 2 - NO
        $valida = 2;
        //PARAMETRO QUE DEFINE QUE MOVIMIENTO REALIZARÁ, EN ESTE CASO VINCULAR
        $mov = 'D';

        $this->setContenedor('divContenedorGeneralModal');
        $html = '<table class="table table table-bordered table-hover" >
            <tr class="warning">
                <th class="warning" style="text-align:center" align="left" colspan="1"> UR</th>
                <th class="warning" style="text-align:center" align="left" colspan="5">Mensaje</th>
            </tr>
        ';

        $filasmesajes = '';
        $desvinculados = '';
        foreach ($this->prm->ueres as $ur) {

            $prg_id = $this->prm->prgs_id;
            if (!empty($this->prm->sprs_id)) {
                $spr_id = $this->prm->sprs_id;
            } else {
                $spr_id = 0;
            }

            $con = new sp_VinculaSubprogramaUr();
            $con->setGpo_id($ur->gpo_id);
            $con->setUr_id($ur->ur_id);
            $con->setPrgs_id($prg_id);
            $con->setSprs_id($spr_id);
            $con->setValidaEstado($valida);
            $con->setSis_id('POA');
            $con->setMov($mov);
            $con->setEjercicio(APP_EJERCICIO);
            $con->setBit_observacion(' SE DESVINCULA DESDE LA PANTALLA ');
            $r = $con->execute();

            if ($r->bandera != 1) {
                $bandera = 0;
                $filasmesajes .= '<tr style="background-color:rgb(255,0,0,.20);">';
                $filasmesajes .= '<td >' . $ur->gpo_id . $ur->ur_id . '</td>';
                $filasmesajes .= '<td>' . $r->mensaje . '</td>';
                $filasmesajes .= '</tr>';
            } else {
                $bandera = 1;
                $filasmesajes .= '<tr>';
                $filasmesajes .= '<td >' . $ur->gpo_id . $ur->ur_id . '</td>';
                $filasmesajes .= '<td>Ha sido desvinculado</td>';
                $filasmesajes .= '</tr>';
                if ($desvinculados == '') {
                    $desvinculados .= $ur->gpo_id . $ur->ur_id;
                } else {
                    $desvinculados .= ', ' . $ur->gpo_id . $ur->ur_id;
                }
            }
        }

        $this->desconectar();
        $desvinculados = '(' . $desvinculados . ')';

        $this->conectar();
        $this->setSql(' SELECT * FROM enc_UrSubprogramas WHERE prgs_id=' . $prg_id . ' ' . (!empty($this->prm->sprs_id) ? ' AND sprs_id = ' . $this->prm->sprs_id . '' : ' AND sprs_id=0 ') . '');
        $this->consultar();
        $this->desconectar();
        $pdf = '';
        if ($this->getNumeroRegistros() == 0) {
            $pdf = '
                $("#pdf_id").removeAttr("disabled");
                $(".selectpicker").each(function(i,x){
                    if($(this).attr("data-id") == "pdf_id"){
                    $(this).removeClass("disabled");    
                    
                    }
                });
            ';
        }

        $html .= $filasmesajes . '</table>';
        $this->mixHtml('
                <div id="myModal2" class="modal " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xs">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Resultado de la Desvinculación</h4>
                                <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                    <i class="fa fa-xmark"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
                ');
        $this->mixScript('
                $("#myModal2").modal().show();
                $("#myModal2").on("hidden.bs.modal", function (e) {
                    $("#myModal2").remove();             
                });
                $(".cerrar").click(function(){ $("#myModal2").remove(); });
        ');

        if ($desvinculados != '()') {
            $this->prm->idTabla = 'listaUrsVinculadas';
            $this->llenaTablaUrs(!empty($this->prm->sprs_id));
            $this->prm->idTabla = 'listaUrsNoVinculadas';
            $this->llenaTablaUrs(!empty($this->prm->sprs_id));
        }
    }

    private function productoFinal()
    {
        $this->setContenedor('divContenedorGeneralModal');
        if (isset($this->prm->prgs_id) && !empty($this->prm->prgs_id)) {

            $pdf = new stdClass();
            $pdf->id = 'pdf_id';
            $pdf->label = 'Seleccionar Producto Final';
            $pdf->enDiv = true;
            $pdf->comoAtr = 'pdf_plural';
            $pdf->
                /*Consulta en la que habría que realizar el cambio de pdf_plural por pdf_nombre, a petición de planeación */$pdf->sql = '
                SELECT pdf_id,pdf_nombre 
                FROM cat_ProductoFinal
                LEFT JOIN(
                    SELECT pdf_id,if(ISNULL(pdf_id),null,prgs_id) prgs_id FROM cat_Subprogramas
                )yatiene USING(pdf_id)
                WHERE pdf_final = 0 AND ISNULL(prgs_id);
            ';
            $this->mixHtml('
                <div id="myModalPdf" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                    <div class="modal-dialog" style="width:50%;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">x</button>
                                <h4 class="modal-title" id="myModalLabel">Vinculación del Producto Final a Subprograma cero</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <fieldset class="col-md-1 col-xs-1">
                                        ' . $this->ponCombo($pdf) . '
                                    </fieldset>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn cerrar btn-sm" data-dismiss="modal">Cerrar</button>
                                <button type="button" class="btn vincularPdf btn-sm" data-dismiss="modal">Aceptar</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
        }
        $this->mixScript('
            $(".cerrar").click(function(){ $("#myModalPdf").remove(); }); 
            $(".vincularPdf").click(function(){ 
                comunica("' . $this->pantalla . '",{
                    metodo:"vinProductoFinal",
                    pdf_id: $("#pdf_id option:selected").val(),
                    prgs_id:' . $this->prm->prgs_id . '
                });
            });
        ');
    }

    private function vinProductoFinal()
    {
        if (isset($this->prm->pdf_id) && !empty($this->prm->pdf_id)) {
            $this->conectar();
            $this->setSql('SELECT prg_id FROM cat_Programa WHERE prg_id = ?;');
            $this->consultar([$this->prm->prgs_id]);
            $this->desconectar();
            $prg = $this->extraer();

            if (empty($prg['prg_id'])) {
                $this->conectar();
                $this->setSql('UPDATE cat_Subprogramas SET pdf_id = ? WHERE prgs_id = ? AND sprs_id = 0;');
                $this->consultar([$this->prm->pdf_id, $this->prm->prgs_id]);
                $this->desconectar();

                $this->mixScript('
                    notify("Vinculación exitosa");
                    $("#myModalPdf").remove();
                ');
            } else {
                $this->mixScript('
                    notify("El programa ya esta asignado a la UR ya no es posible realizar la vinculación","error");
                ');
            }
        }
    }

    private function modalActualizacion()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $grupo = '';
        $ur = ''; //Ur
        $gpoUR = ''; //Grupo
        $nombreNuevo = $this->prm->nombre_prgs;
        $prg = new stdClass();
        $prg->id = 'programa';
        $prg->required = true;
        $prg->enDiv = true;
        $prg->maxlength = 100;
        $prg->width = '1000px';
        $prg->height = '100px';
        $prg->value = $this->prm->nombre_prgs;
        $prg->change = ' $("#btnEnvValidar").attr("nombreNuevo",$(this).val());';

        // Si pasaron el idPrograma y no está vacío ... es una consulta solamente
        $urs = new stdClass();
        $urs->id = 'ueres';
        $urs->width = '1000px';
        $urs->comoAtr = 'gpo_id,ur_id';
        $urs->required = true;
        //Selecciona todas las UR´s diferentes de cero
        $urs->sql = '
            SELECT ur_clave,ur_nombre,gpo_id,ur_id 
            FROM cat_UnidadResponsable 
            WHERE (gpo_id,ur_id) not in ((1,15))
            AND gpo_id  not in (0)
            ;
        ';
        $this->conectar();
        $this->setSql('
            SELECT CONCAT(gpo_id,ur_id) AS ur_clave,gpo_id,ur_id,prgs_id 
            FROM vin_ProgramasMir 
            WHERE prgs_id = ?;
        ');

        $this->consultar([$this->prm->prgs_id]);
        $this->desconectar();
        $reg = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            $urs->selecciona = $reg['ur_clave'];
            $grupo = $reg['gpo_id'];
            $ur = $reg['ur_id'];
            $gpoUR = $reg['ur_clave'];
        }
        $urs->change = ' $("#btnEnvValidar").attr("urNuevo",$(this).val());';
        $pr = new Programas($this->prm);
        $pes = new stdClass();
        $pes->spe_id = $pr->getSpe_id();
        $pes->obp_id = $pr->getObp_id();
        $pes->modal = true;



        $this->prm->fun_id = 1;
        $this->prm->sfu_id = 1;

        $guardar = '';
        $programa = (isset($this->prm->prgs_id)) ? $this->prm->prgs_id : '';

        $ban = true;
        if ($programa != '') {
            $pr = new Programas($this->prm);
            $abc = 'c';
        } else {
            $pr = new Programas();
            $abc = 'a';
        }

        $prg_id = new stdClass();
        $prg_id->id = 'nprg_id';
        $prg_id->label = 'Número';
        $prg_id->required = true;
        $prg_id->enDiv = true;
        $prg_id->maxlength = 3;
        $prg_id->value = $pr->getPrgs_id();

        $prg = new stdClass();
        $prg->id = 'programa';
        $prg->label = 'Nombre del programa:';
        $prg->required = true;
        $prg->enDiv = true;
        $prg->maxlength = 200;
        $prg->width = '100px';
        $prg->height = '100px';
        $prg->value = $pr->getNombre();

        #población potencial
        $pop = new stdClass();
        $pop->id = 'pbf_id';
        $pop->sql = 'SELECT pbf_id,pbf_nombre,pbf_Registro FROM cat_PoblacionBeneficiada';
        $pop->selecciona = $pr->getPbf_id();
        $pop->label = 'Población Potencial';
        $pop->comoAtr = 'pbf_Registro';

        #hombres
        $hombres = new stdClass();
        $hombres->id = 'hombres';
        $hombres->formato = 'entero';
        $hombres->required = true;
        $hombres->label = 'Hombres';
        $hombres->maxlength = 10;
        $hombres->enDiv = true;
        $hombres->blur = '
            $("#total").val( ($("#mujeres").val() *1 ) + ( $(this).val() * 1) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $hombres->value = $pr->getHombres();
        #mujeres
        $mujeres = new stdClass();
        $mujeres->id = 'mujeres';
        $mujeres->formato = 'entero';
        $mujeres->required = true;
        $mujeres->label = 'Mujeres';
        $mujeres->enDiv = true;
        $mujeres->blur = '
            $("#total").val( ( $("#hombres").val() *1 ) + ( $(this).val() * 1 ) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $mujeres->value = $pr->getMujeres();

        #total
        $total = new stdClass();
        $total->id = 'total';
        $total->formato = 'entero';
        $total->label = 'Total';
        $total->required = true;
        $total->enDiv = true;
        $total->value = $pr->getTotal();

        $total->blur = '
            if( $(this).val() * 1 == 0 ){
                $("#mujeres,#hombres").removeAttr("readonly");
            }else if($("#mujeres").val()!=0 || $("#hombres").val()!=0){ 
                if($("#total").val()!= ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1)){
                    $("#mujeres").val(0);
                    $("#hombres").val(0);
                    $("#mujeres,#hombres").attr("readonly",true);
                }else{
                    $("#total").val( ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1) );
                  
                    $("#mujeres,#hombres").removeAttr("readonly");
                }
            }else{
                $("#mujeres").val(0);
                $("#hombres").val(0);
                $("#mujeres,#hombres").attr("readonly",true);
            }
        ';
        /*---------------------------------------------------población potencial Objetivo-----------------------------------------------*/
        #población potencial Objetivo
        $popO = new stdClass();
        $popO->id = 'pbf_idO';
        $popO->sql = 'SELECT pbf_id,pbf_nombre,pbf_Registro FROM cat_PoblacionBeneficiada';
        $popO->selecciona = $pr->getPbf_idO();
        $popO->label = 'Población Potencial Objetivo';
        $popO->required = true;
        $popO->comoAtr = 'pbf_Registro';


        #hombres Objetivo
        $hombresO = new stdClass();
        $hombresO->id = 'hombresO';
        $hombresO->formato = 'entero';
        $hombresO->required = true;
        $hombresO->label = 'Hombres';
        $hombresO->maxlength = 10;
        $hombresO->enDiv = true;
        $hombresO->blur = '
             $("#totalO").val( ($("#mujeresO").val() *1 ) + ( $(this).val() * 1) );
             if($("#totalO").val() * 1 == 0 )
             $("#totalO").removeAttr("readonly");
             else
             $("#totalO").attr("readonly",true);
              
         ';
        $hombresO->value = $pr->getHombresO();
        #mujeres Objetivo
        $mujeresO = new stdClass();
        $mujeresO->id = 'mujeresO';
        $mujeresO->formato = 'entero';
        $mujeresO->required = true;
        $mujeresO->label = 'Mujeres';
        $mujeresO->enDiv = true;
        $mujeresO->blur = '
             $("#totalO").val( ( $("#hombresO").val() *1 ) + ( $(this).val() * 1 ) );
             if($("#totalO").val() * 1 == 0 )
             $("#totalO").removeAttr("readonly");
             else
             $("#totalO").attr("readonly",true);
         ';
        $mujeresO->value = $pr->getPrgsMujerO();

        #total Objetivo
        $totalO = new stdClass();
        $totalO->id = 'totalO';
        $totalO->formato = 'entero';
        $totalO->label = 'Total';
        $totalO->required = true;
        $totalO->enDiv = true;
        $totalO->value = $pr->getTotalO();
        $totalO->blur = '
             if( $(this).val() * 1 == 0 ){
                 $("#mujeresO,#hombresO").removeAttr("readonly");
             }else if($("#mujeresO").val()!=0 || $("#hombresO").val()!=0){ 
                        if($("#totalO").val()!= ($("#mujeresO").val() *1 ) + ( $("#hombresO").val() * 1)){
                            $("#mujeresO").val(0);
                            $("#hombresO").val(0);
                            $("#mujeresO,#hombresO").attr("readonly",true);
                        }else{
                     $("#totalO").val( ($("#mujeresO").val() *1 ) + ( $("#hombresO").val() * 1) );
                     $("#mujeresO,#hombresO").removeAttr("readonly");
                 }
             }else{
                 $("#mujeresO").val(0);
                 $("#hombresO").val(0);
                 $("#mujeresO,#hombresO").attr("readonly",true);
             }
         ';
        $totalO->value = $pr->getTotalO();
        $modalActualizacion = '
            <div id="myModalRegistro" class="modal " data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog shadow-lg modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Actualización de Programa</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body row">
                            <fieldset class="col-md-12 col-xs-12 mb-4">
                                ' . $this->ponInput($prg) . '
                            </fieldset>
                            <fieldset class="col-md-12 col-xs-12 mb-4">
                                UR Responsable
                                ' . $this->ponCombo($urs) . '
                            </fieldset>
                             <fieldset class="col-md-12 col-xs-12 mb-4">
                                ' . Pes::ponPes($pes) . '
                            </fieldset>
                            <fieldset class="col-md-6 col-xs-6 mb-4">
                            <legend class="col-md-12 col-xs-12 mb-4">Población Potencial</legend>
                                <div id="divhombres">
                                    ' . $this->ponInput($hombres) . '
                                </div>
                                <div id="divmujeres">
                                    ' . $this->ponInput($mujeres) . '
                                </div>
                                <div>
                                    ' . $this->ponInput($total) . '
                                </div>
                            </fieldset>
                              
                            <fieldset class="col-md-6 col-xs-6 mb-4">
                            <legend class="col-md-12 col-xs-12 mb-4">Población Objetivo</legend>
                                <div id="divhombreso">
                                    ' . $this->ponInput($hombresO) . '
                                </div>
                                <div id="divmujereso">
                                    ' . $this->ponInput($mujeresO) . '
                                </div>
                                <div>
                                    ' . $this->ponInput($totalO) . '
                                </div>
                            </fieldset>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-success" id="btnEnvValidar" urNuevo="' . $gpoUR . '" nombreNuevo="' . $nombreNuevo . '" form="">Actualizar</button>
                            <button type="button" class="btn btn-outline-danger cerrar" form="">Cancelar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ';
        $this->mixHtml($modalActualizacion);

        $this->mixScript('

            if($("#pbf_idO").find("option:selected").attr("pbf_registro")==2){
                $("#divhombreso").hide();
                $("#divmujereso").hide();
            }else{
                $("#divhombreso").show();
                $("#divmujereso").show();
            }
            if($("#pbf_id").find("option:selected").attr("pbf_registro")==2){
                $("#divhombres").hide();
                $("#divmujeres").hide();
            }else{
                $("#divhombres").show();
                $("#divmujeres").show();
            }
            var nuevoSpe_id = $(this).attr("spe_id").value;
            $("#spe_id").on("change", function() {
             nuevoSpe_id = this.value;
            });
            var actualSpe_id = $(this).attr("spe_id").value;

            var nuevoObp_id = $(this).attr("obp_id").value;
            $("#obp_id").on("change", function() {
             nuevoObp_id = this.value;
            });
            var actualObp_id = $(this).attr("obp_id").value;

            $("#myModalRegistro").modal().show();
            $("#myModalRegistro").on("hidden.bs.modal", function (e) {
                $("#myModalRegistro").remove();
            });


            $(".cerrar").click(function(){ $("#myModalRegistro").remove(); });
            $("#btnEnvValidar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaActualizacion",
                    prgs_id:"' . $this->prm->prgs_id . '",
                    grupo:"' . $grupo . '",
                    ur:"' . $ur . '",
                    nombreActual:"' . $this->prm->nombre_prgs . '",
                    nombreNuevo:$(this).attr("nombreNuevo"),
                    gpoUR:$(this).attr("urNuevo"),
                    accion:1,
                    actualSpe_id:actualSpe_id,
                    nuevoSpe_id:nuevoSpe_id,
                    hombres: $("#hombres").val(),
                    mujeres: $("#mujeres").val(),
                    total: $("#total").val(),
                    hombresO: $("#hombresO").val(),
                    mujeresO: $("#mujeresO").val(),
                    totalO: $("#totalO").val(),
                    actualObp_id:actualObp_id,
                    nuevoObp_id:nuevoObp_id,
                });
                $("#myModalRegistro").remove();
            });
        ');
    }

    private function modalValidacionModificacionPrograma()
    {
        $this->setContenedor('divContenedorGeneralModal');

        //Se agrea la información módificada
        $urActual = '';
        $nombreModificado = '';
        $nombreActual = '';
        $ursMod = new stdClass();
        $ursMod->id = 'uerMod';
        $ursMod->width = '1000px';
        $ursMod->comoAtr = 'gpo_id,ur_id';
        $ursMod->disabled = true;
        //Selecciona todas las UR´s diferentes de cero
        $ursMod->sql = '
            SELECT ur_clave,ur_nombre,gpo_id,ur_id 
            FROM cat_UnidadResponsable 
            WHERE (gpo_id,ur_id) not in ((1,15))
            AND gpo_id  not in (0)
            ;
        ';
        $this->conectar();
        $this->setSql('
            SELECT CONCAT(gpo_id_nuevo,ur_id_nuevo) AS ur_clave,
            gpo_id_nuevo,ur_id_nuevo,CONCAT(gpo_id_actual,ur_id_actual) AS ur_actual,
            prgs_id,prgs_nombreActual,prgs_nombreNuevo 
            FROM ' . BD_TEMPORAL . '.tem_ActualizacionPrograma
            WHERE prgs_id = ? AND tap_estado IN(0,1,2);
        ');

        $this->consultar([$this->prm->prgs_id]);
        $this->desconectar();
        $reg = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            $ursMod->selecciona = $reg['ur_clave'];
            $urModificado = $reg['ur_clave'];
            $urActual = $reg['ur_actual'];
            $nombreModificado = $reg['prgs_nombreNuevo'];
            $nombreActual = $reg['prgs_nombreActual'];
        }
        $ursMod->readonly = true;

        $prgMod = new stdClass();
        $prgMod->id = 'programaMod';
        $prgMod->required = true;
        $prgMod->enDiv = true;
        $prgMod->maxlength = 100;
        $prgMod->width = '1000px';
        $prgMod->height = '100px';
        $prgMod->value = $nombreModificado;
        $prgMod->disabled = true;

        $modalValidacionActualizacion = '   
            <div id="myModal" class="modal " data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">    
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Validar Actualización de Programa</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <fieldset class="col-md-12 col-xs-12 mb-4">';
        if ($nombreActual != $nombreModificado) {
            $modalValidacionActualizacion .= '
                                    Nombre del Programa
                                        ' . $this->ponInput($prgMod) . '
                            </fieldset>
                            <fieldset class="col-md-12 col-xs-12 mb-4">';
        }
        if ($urActual != $urModificado) {
            $modalValidacionActualizacion .= '
                                    UR Responsable
                                        ' . $this->ponCombo($ursMod) . '
                            </fieldset>';
        }
        $modalValidacionActualizacion .= '
                            </fieldset>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-success" id="btnEnviarAvalidar" accion="2" form="">
                                Validar
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="rechazar" accion= "3" form="">
                                Rechazar
                            </button>
                            
                        </div>  
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div> 
        ';

        $this->mixHtml($modalValidacionActualizacion);

        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").remove(); });

            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });

            $("#rechazar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaActualizacionPrograma",
                    prgs_id:"' . $this->prm->prgs_id . '",
                    accion:$(this).attr("accion")
                });
                $("#myModal").remove();
            });

            $("#btnEnviarAvalidar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaActualizacionPrograma",
                    prgs_id:"' . $this->prm->prgs_id . '",
                    accion:$(this).attr("accion")
                });
                $("#myModal").remove();
            });
        ');
    }

    private function validaActualizacion()
    {
        $usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $mensaje = 'La información es requerida';
        $accion = $this->prm->accion;
        $prgs_id = $this->prm->prgs_id;
        $valida = ($_SESSION[_S]['ssc_id'] == 2) ? 2 : 1; // 2=Planeacion 1=Presupuesto
        $grupoActual = $this->prm->grupo;
        $urActual = $this->prm->ur;
        $grupoNuevo = substr($this->prm->gpoUR, 0, 1);
        $urNuevo = substr($this->prm->gpoUR, 1, 2);
        $nombreNuevo = strtoupper($this->prm->nombreNuevo);
        $nombreActual = strtoupper($this->prm->nombreActual);
        $actualizaPrograma = false;
        $actualizaPES = false;

        if ($grupoNuevo != $grupoActual || $urActual != $urNuevo || $nombreNuevo != $nombreActual) {
            $actualizaPrograma = true;
        }

        if (($this->prm->actualSpe_id != $this->prm->nuevoSpe_id) || ($this->prm->actualObp_id != $this->prm->nuevoObp_id)) {
            $actualizaPES = true;
        }

        $this->conectar();
        $this->setSql('SET @actualizaNombreur = 2;');
        $this->consultar();

        $this->setSql('
            UPDATE cat_Programas SET prgs_hombres = ?, prgs_mujeres = ?, prgs_total = ?, prgs_hombresO = ?,	prgs_mujeresO = ?, prgs_totalO = ?
            WHERE prgs_id = ?
        ');
        $params = [$this->prm->hombres, $this->prm->mujeres, $this->prm->total, $this->prm->hombresO, $this->prm->mujeresO, $this->prm->totalO, $this->prm->prgs_id];
        $this->consultar($params);
        $this->desconectar();

        $observacion = 'EL PROGRAMA FUE MODIFICADO LOS SIGUENTES CAMPOS prgs_hombres = ' . $this->prm->hombres . ' prgs_mujeres = ' . $this->prm->mujeres . ' prgs_total = ' . $this->prm->total . ' prgs_hombresO = ' . $this->prm->hombresO . '	prgs_mujeresO = ' . $this->prm->mujeresO . ' prgs_totalO = ' . $this->prm->prgs_id . ', POR EL USUARIO:' . $usr_id . ', IP:' . $ip . '';
        $this->conectar();
        $sqlBitacora = 'INSERT INTO ' . BD_BITACORA . '.bit_Programas (prgs_id,epr_id,bit_observacion,usr_ip,usr_id) VALUES ( ?, 4, ?, INET_ATON( ? ), ?)';
        $params = [$this->prm->prgs_id, $observacion, $ip, $usr_id];
        $this->setSql($sqlBitacora);
        $this->consultar($params);
        $this->desconectar();

        if (!$actualizaPrograma && !$actualizaPES) {
            $this->mixScript('
                notify("Datos Actualizados","success");
                comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
                $("#myModalRegistro").remove();
            ');
        } elseif (!is_numeric($this->prm->gpoUR) || empty($nombreNuevo)) {
            $mensaje = '';
            if (!is_numeric($this->prm->gpoUR)) {
                $mensaje .= '- UR Responsable';
            }
            if (empty($nombreNuevo)) {
                $mensaje .= '';
            }
            $this->mixScript('
                notify("-Nombre del programa","error")
            ');
        } else {
            if ($actualizaPrograma) {
                $actualizacionUrNombrePrograma = new sp_ActualizarUrNombrePrograma();
                $actualizacionUrNombrePrograma->setAccion($accion);
                $actualizacionUrNombrePrograma->setPrgs_id($prgs_id);
                $actualizacionUrNombrePrograma->setGpo_id_actual($grupoActual);
                $actualizacionUrNombrePrograma->setUr_id_actual($urActual);
                $actualizacionUrNombrePrograma->setGpo_id_nueva($grupoNuevo);
                $actualizacionUrNombrePrograma->setUr_id_nueva($urNuevo);
                $actualizacionUrNombrePrograma->setPrgs_nombre_actual($nombreActual);
                $actualizacionUrNombrePrograma->setPrgs_nombre_nuevo($nombreNuevo);
                $actualizacionUrNombrePrograma->setQuien_valida($valida);
                $actualizacionUrNombrePrograma->setSis_id('POA');
                $actualizacionUrNombrePrograma->setBit_observacion('Actualización correcta');
                $actualizacionUrNombrePrograma->setEjercicio(APP_EJERCICIO);
                $actualizacionUrNombrePrograma->execute();
                $this->mixScript('
                notify("' . $actualizacionUrNombrePrograma->getMensaje() . '","'
                    . ($actualizacionUrNombrePrograma->getBandera() ? 'success' : 'error') . '")
                comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
                $("#myModalRegistro").remove();
            ');
            }
            if ($actualizaPES) {
                $usr_id = $_SESSION[_S]['usr_id'];
                $observacion = 'Actulizacion de PES';

                $this->conectar();
                $this->setSql('SET @bit_observacion = ?, @usr_id = ? , @usr_ip=sinpres.fn_get_ip();');
                ;
                $this->consultar([$observacion, $usr_id]);
                $this->setSql('CALL sp_ActualizaPES' . APP_EJERCICIO . '(?, ?, ?, "S", @usr_ip, @usr_id);');
                $this->consultar([$prgs_id, $this->prm->nuevoSpe_id,  $this->prm->nuevoObp_id]);
                $this->setSql('SELECT @banderaPoa, @mensajePoa;');
                $this->consultar();
                $res = $this->extraer();
                $this->mixScript('
                    notify("' . $res['@mensajePoa'] . '","'
                        . ($res['@banderaPoa'] ? 'success' : 'error') . '")
                    comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
                    $("#myModalRegistro").remove();
                ');
            }
        }
    }

    private function validaActualizacionPrograma()
    {
        $accion = $this->prm->accion;
        $prgs_id = $this->prm->prgs_id;
        $valida = ($_SESSION[_S]['ssc_id'] == 2) ? 2 : 1; // 2=Planeacion 1=Presupuesto

        //Quien valida
        switch ($accion) {
            case 2: //Validacion Visto Bueno
                $valida = ($_SESSION[_S]['ssc_id'] == 2) ? 2 : 1; // 2=Planeacion 1=Presupuesto
                break;
            case 3: //Validacion Rechazao
                $valida = ($_SESSION[_S]['ssc_id'] == 2) ? 5 : 4; // 5=Planeacion 4=Presupuesto
                break;
        }

        $actualizacionUrNombrePrograma = new sp_ActualizarUrNombrePrograma();
        $actualizacionUrNombrePrograma->setAccion(2);
        $actualizacionUrNombrePrograma->setPrgs_id($prgs_id);
        $actualizacionUrNombrePrograma->setGpo_id_actual(0);
        $actualizacionUrNombrePrograma->setUr_id_actual(0);
        $actualizacionUrNombrePrograma->setGpo_id_nueva(0);
        $actualizacionUrNombrePrograma->setUr_id_nueva(0);
        $actualizacionUrNombrePrograma->setPrgs_nombre_actual('');
        $actualizacionUrNombrePrograma->setPrgs_nombre_nuevo('');
        $actualizacionUrNombrePrograma->setQuien_valida($valida);
        $actualizacionUrNombrePrograma->setSis_id('POA');
        $actualizacionUrNombrePrograma->setBit_observacion('Actualización correcta');
        $actualizacionUrNombrePrograma->setEjercicio(APP_EJERCICIO);
        $actualizacionUrNombrePrograma->execute();

        $this->mixScript('
            notify("' . $actualizacionUrNombrePrograma->getMensaje() . '","'
            . ($actualizacionUrNombrePrograma->getBandera() ? 'success' : 'error') . '")
            comunica("' . $this->pantalla . '",{metodo:"ponTablaPrg"});
            $("#myModal").remove();
        ');
    }
}

//se verifica que tenga el permiso para el modulo
if (isset($_SESSION[_S]['prm']['A']['2']['3'])) {
    new ControladorEstructuraProgramatica();
} else {
    WebResponse::sinPermisos();
}
