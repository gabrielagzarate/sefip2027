<?php
//inicio de sesion y llamada a las librerias
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

set_time_limit(0);

class ControladorGestorSubprograma0 extends WebResponse
{
    public $lectura;
    public $escritura;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->pantalla = 'mga/complementos/ControladorGestorSubprograma0.php';

        #si tiene el permiso de lectura y escritura se accede al controlador
        $this->lectura = isset($_SESSION[_S]['prm']['M']['4']['6']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['M']['4']['6']['2']) ? true : false;

        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'llenaTablaPrograma':
                $this->llenaTablaPrograma();
                break;
            case 'TablaSubProgramas':
                $this->TablaSubProgramas();
                break;
            case 'llenaTablaSubprograma':
                $this->llenaTablaSubprograma();
                break;
            case 'actualizarSubprograma':
                $this->actualizarSubprograma();
                break;
        }
        $this->returnJson();
    }
    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function main()
    {
        $botones = '';
        $botones .= <<<HTML
            <input type="search" width=50
                id="filtroTabla"
                style="display: inline; width:300px; height: 30px;"
                class="form-control m-r-10" 
                placeholder="Filtrar" />
            HTML;
        $this->agregaTools($botones);

        $tabla = new stdClass();
        $tabla->id = 'tbl_Programa';
        $tabla->esCompacta = true;
        $tabla->titulo = 'Lista de Programas';
        $tabla->prmSeleccion = ['prg_id'];
        $tabla->seleccionable = true;


        $tr = [];

        $th = new stdClass();
        $th->texto = 'ID_PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UR´s';
        $th->align = 'center';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaPrograma';

        $tabla->cuerpo = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'prg_nombre';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->id = 'operacion';
        $td->campoTxt = 'ur';
        $td->align = 'center';
        $td->tipo = 'boton';
        $td->icon = 'solid fa-plus';
        $td->class = 'operacion me-1';
        $td->color = 'verde';
        $td->attr = new stdClass();
        $td->attr->prg_id = 'prg_id';
        array_push($tabla->cuerpo, $td);

        $this->mixHtml(
            '<div class="tab-content">
                <div class="tab-pane active" id="prg1">
                    <div class="row">
                        <fieldset class="col-md-12">
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                    </div>
                </div>
            </div>'
        );
        $this->llenaTablaPrograma();

        $this->mixScript('
               $(document).on("click", ".operacion",  function(){
                    var prg_id = $(this).attr("prg_id");
                    comunica("' . $this->pantalla . '",{
                    metodo: "TablaSubProgramas",
                    prg_id: prg_id
                    });
                });
        ');
    }

    private function llenaTablaPrograma()
    {

        $this->conectar();
        $sql = '
        SELECT
            prg_id,
            prg_nombre,
            COUNT(DISTINCT CONCAT(gpo_id, LPAD(ur_id, 2,""))) AS ur,
            GROUP_CONCAT(DISTINCT CONCAT(gpo_id, LPAD(ur_id, 2, "")) ORDER BY gpo_id SEPARATOR ",") AS codigos
        FROM cat_Programa prog
        INNER JOIN cat_Programas pgrs ON pgrs.prgs_id = prog.prg_id
        INNER JOIN cat_Subprograma subp USING (gpo_id, ur_id, prg_id)
        INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
        WHERE spr_id = ? AND epr_id = ?
        GROUP BY prg_id
            ';

        $parametros = [0,4];
        $this->setParams($parametros);
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = 'tbl_Programa';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        $this->mixScript('
            $(document).ready(function() { 
                $("#tabla-tbl_Programa").filterTable({ 
                    inputSelector: "#filtroTabla",
                    minRows: 1
                }); 
            });
            ');
    }

    private function TablaSubProgramas()
    {
        $nuevo = '
            <button id="regresar" type="button" class="btn btn-outline-secondary me-1" title="Regresar">
                <i class="fa-solid fa-circle-left me-2"></i>
                Regresar
            </button>';

        $nuevo .= '
            <input type="search" width=60
                id="filtroTabla"
                style="display: inline; width:300px; height: 30px;"
                class="form-control me-2" 
                placeholder="Filtrar"/>
        ';

        $this->agregaTools($nuevo);

        $tabla = new stdClass();
        $tabla->id = 'tbl_Supr0';
        $tabla->esCompacta = true;
        $tabla->titulo = 'Lista subprogramas 0';
        $tabla->prmSeleccion = ['prg_id'];
        $tabla->seleccionable = true;


        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'HABILITAR/DESHABILITAR';
        $th->width = '15px';
        $th->align = 'center';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaSubprograma';

        $tabla->cuerpo = []; // cuerpo de la tabla en un arreglo de tr y tds
        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($tabla->cuerpo, $td);

        if ($this->escritura == true) {
            $td = new stdClass();
            $td->align = 'center';
            $td->casos = new stdClass();
            $td->casos->campo = 'spr_status';
            $td->casos->cases = [];
            $caso = new stdClass();
            $caso->valor = '1';
            $caso->tipo = 'boton';
            $caso->id = 'habilitar';
            $caso->color = 'verde';
            $caso->class = 'habilitar';
            $caso->icono = 'power-off';
            $caso->attr = new stdClass();
            $caso->attr->prg_id = 'prg_id';
            $caso->attr->spr_status = '1';
            $caso->attr->gpo_id = 'gpo_id';
            $caso->attr->ur_id = 'ur_id';
            array_push($td->casos->cases, $caso);
            $caso = new stdClass();
            $caso->valor = '2';
            $caso->tipo = 'boton';
            $caso->id = 'habilitar';
            $caso->color = 'rojo';
            $caso->class = 'habilitar';
            $caso->icono = 'power-off';
            $caso->attr = new stdClass();
            $caso->attr->prg_id = 'prg_id';
            $caso->attr->spr_status = '2';
            $caso->attr->gpo_id = 'gpo_id';
            $caso->attr->ur_id = 'ur_id';
            array_push($td->casos->cases, $caso);
        } else {
            $td = new stdClass();
            $td->align = 'center';
            $td->casos = new stdClass();
            $td->casos->campo = 'spr_status';
            $td->casos->cases = [];
            $caso = new stdClass();
            $caso->valor = '1';
            $caso->tipo = 'txtu';
            $caso->color = 'verde';
            $caso->class = 'btn-user btn-user-success';
            $caso->campoTxt = 'estatus';
            array_push($td->casos->cases, $caso);
            $caso = new stdClass();
            $caso->valor = '2';
            $caso->tipo = 'txtu';
            $caso->color = 'rojo';
            $caso->class = 'btn-user btn-user-danger';
            $caso->campoTxt = 'estatus';
            array_push($td->casos->cases, $caso);
        }
        array_push($tabla->cuerpo, $td);

        $this->mixHtml(
            '<div class="tab-content">
                <div class="tab-pane active" id="prg1">
                    <div class="row">
                        <fieldset class="col-md-12">
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                    </div>
                </div>
            </div>'
        );
        $this->llenaTablaSubprograma();

        $this->mixScript('

                $("#regresar").click(function(){
                    comunica("' . $this->pantalla . '",{ 
                    metodo: "main"
                    });
                });

               $(document).on("click", ".habilitar",  function(){
                    var prg_id = $(this).attr("prg_id");
                    var spr_status= $(this).attr("spr_status");
                    var gpo_id = $(this).attr("gpo_id");
                    var ur_id= $(this).attr("ur_id");
                    var mensaje = "HABILITAR"
                    if (spr_status == 1)
                        {
                         mensaje = "DESHABILITAR";
                        }

                    alertify.confirm(mensaje + " SUBPROGRAMA 0", "¿Desea <strong>" + mensaje + "</strong> el suprograma 0 del programa <strong> " + prg_id + "</strong>?",
                    function(){ 
                            comunica("' . $this->pantalla . '",{
                            metodo: "actualizarSubprograma",
                            prg_id: prg_id,
                            status: spr_status,
                            gpo_id: gpo_id,
                            ur_id: ur_id
                            });
                        }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:""+mensaje, cancel:"Cancelar"});
                });
                setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );
        ');
    }

    private function llenaTablaSubprograma()
    {

        $this->conectar();
        $sql = '
            SELECT gpo_id, ur_id, CONCAT_WS(" ", CONCAT(prog.gpo_id, prog.ur_id, "-") ,ur.ur_nombre) AS ur,
                prg_id,
                prg_nombre,
                epr_id,
                spr_id,
                spr_status,
                CASE 
                    WHEN spr_status = 1 THEN "HABILITADO"
                    WHEN spr_status = 2 THEN "DESHABILITADO"
                END as estatus
            FROM cat_Programa prog
            INNER JOIN cat_Programas pgrs ON pgrs.prgs_id = prog.prg_id
            INNER JOIN cat_Subprograma subp USING (gpo_id, ur_id, prg_id)
            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                WHERE spr_id = ? AND epr_id = ? AND prg_id = ? 
            ORDER BY prg_id;';

        $parametros = [0,4,$this->prm->prg_id];  
        $this->setParams($parametros); 
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = 'tbl_Supr0';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        $this->mixScript('
            $(document).ready(function() { 
                $("#tabla-tbl_Supr0").filterTable({ 
                    inputSelector: "#filtroTabla",
                    minRows: 1
                }); 
            });
            ');
    }

    private function actualizarSubprograma()
    {
        $prg_id = isset($this->prm->prg_id) ? $this->prm->prg_id : '';
        $status = isset($this->prm->status) ? $this->prm->status : '';
        $gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : '';
        $ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : '';

        if ($prg_id != '' && $status != '' && $gpo_id != '' && $ur_id != '') {
            //bit_observacion
            $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
            $sql = '
            SET 
                @usr_id = :usr_id,
                @bit_observacion = :observacion,
                @usr_ip = INET_ATON( :ip );
            ';
            $parametros = [
                ':usr_id'      => $_SESSION[_S]['usr_id'],
                ':observacion' => 'ACTUALIZACIÓN DE SUBPROGRAMA0',
                ':ip'          => $ip,
            ];

            if ($status == 1) {
                $mir = $this->validarMir($prg_id, $gpo_id, $ur_id);
                $presupuesto = $this->validarPresupuesto($prg_id, $gpo_id, $ur_id);
                if ($mir == false && $presupuesto == false) {

                    $this->setSql($sql);
                    $this->conectar();
                    $this->consultar($parametros);

                    $status = 2;
                    $sql = '
                    UPDATE cat_Subprograma
                    SET spr_status = :status
                    WHERE spr_id = 0
                        AND prg_id = :prg_id
                        AND gpo_id = :gpo_id
                        AND ur_id = :ur_id;
                    ';
                    $parametros = [
                    ':status' => $status,
                    ':prg_id' => $prg_id,
                    ':gpo_id' => $gpo_id,
                    ':ur_id'  => $ur_id,
                    ];

                    $this->setSql($sql);
                    $this->consultar($parametros);
                    $this->desconectar();
                    $this->mixScript('notify("SE HA DESHABILITADO EL SUBPROGRAMA 0","alert alert-success");');
                } else {
                    if ($mir) {
                        $this->mixScript('notify("No se puede deshabilitar este subprograma porque ya cuenta con información registrada en MIR","alert alert-warning");');
                    }
                    if ($presupuesto) {
                        $this->mixScript('notify("No se puede Deshabilitar este Subprograma porque ya cuenta con información presupuestal","alert alert-warning");');
                    }

                }
            } elseif ($status = 2) {
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($parametros);
                $status = 1;
                $sql = '
                        UPDATE cat_Subprograma
                        SET spr_status = :status
                        WHERE spr_id = 0
                            AND prg_id = :prg_id
                            AND gpo_id = :gpo_id
                            AND ur_id = :ur_id;
                        ';
                $parametros = [
                    ':status' => $status,
                    ':prg_id' => $prg_id,
                    ':gpo_id' => $gpo_id,
                    ':ur_id'  => $ur_id,
                ];
                $this->setSql($sql);

                $this->consultar($parametros);
                $this->desconectar();
                $this->mixScript('notify("SE HA HABILITADO EL SUBPROGRAMA 0","alert alert-success");');
            }
            $this->mixScript('
                
                comunica("' . $this->pantalla . '",{
                metodo: "TablaSubProgramas",
                prg_id:' . $prg_id . '
                });
                ');
        } else {
            $this->mixScript('
                notify("ERROR AL PROCESAR SU PETICIÓN","alert alert-danger");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                ');
        }
    }

    private function validarMir($prg_id, $gpo_id, $ur_id): bool
    {
        if ($prg_id != '') {
            $sql = '
                SELECT
                prgs_id,prgs_nombre,sprs_id,pdf_id
                FROM cat_MetaComponente 
                INNER JOIN cat_Programas USING (prgs_id)
                INNER JOIN cat_Subprogramas USING (prgs_id, sprs_id)
                INNER JOIN cat_ProductoFinal USING (pdf_id)
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND prgs_id = ? 
                    AND prgs_id > 100 AND prgs_id < 899
                    AND sprs_id = ?
                    AND pdf_id != ?;'
            ;
            $parametros = [$gpo_id, $ur_id, $prg_id, 0, 0];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($parametros);

            $numeroRegistros = $this->getNumeroRegistros();

            return $numeroRegistros > 0;
        } else {
            $this->mixScript('notify ("No se ha seleccionado un subprogama válido", "alert alert-danger");');
            return false;
        }
    }

    private function validarPresupuesto($prg_id, $gpo_id, $ur_id): bool
    {
        if ($prg_id != '') {
            $sql = '
            select * from enc_Programatico
                where gpo_id = ? 
                and ur_id = ?
                and prg_id = ?
                and spr_id = ? 
                and oa_id > ?;
            '
            ;
            $parametros = [$gpo_id, $ur_id, $prg_id, 0, 0];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($parametros);

            $numeroRegistros = $this->getNumeroRegistros();

            return $numeroRegistros > 0;
        } else {
            $this->mixScript('notify ("No se ha seleccionado un subprogama válido", "alert alert-danger");');
            return false;
        }
    }


}
if (isset($_SESSION[_S]['prm']['M']['4']['6'])) {
    new ControladorGestorSubprograma0();
} else {
    WebResponse::sinPermisos();
}
