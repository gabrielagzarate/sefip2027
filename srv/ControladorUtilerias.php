<?php
    
ini_set("display_errors", "0");
require_once dirname(__FILE__).'/../lib/constantes.php';
if (!isset($_SESSION[_S])) { session_start(); }

require_once dirname(__FILE__).'/../lib/WebResponse.php';
    
class ControladorUtilerias extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->main();
        $this->returnJson();
    }
    
    public function main()
    {
        $boton = new stdClass();
        $boton->id='btnregresarHome';
        $boton->title='Regresar a inicio';
        $boton->value='Regresar';
        $boton->color='gris';
        $boton->icon='fa fa-circle-left me-2';
        $boton->click='$("#utilerias").click();';
        $this->agregaTools( $this->ponBoton( $boton ) );
        $conexion =  new AccesoDatos();
        $conexion->conectar();
        //se agrego AND opc_id = 2 para que solo se muestre la opción de documentos normativos
        $conexion->setSql('
            SELECT DISTINCT opc_id, opc_orden,opc_imagen, opc_url, opc_titulo
            FROM cat_Opcion
            WHERE sis_id="X" AND men_id=4 AND sts_id = 1
            ORDER BY opc_orden
        '); 
        
        $conexion->consultar();
        $conexion->desconectar();
        $cards = [];
        $colocar = true;
        while ($w = $conexion->extraer()) {
            // si la opcion es CONSULTA DE AVANCES POA (3) o EXPOSICION DE MOTIVOS(4) preguntar por el permiso y si nos encontramos en el Sistema MGA
            if ($w['opc_id'] == 3 || $w['opc_id'] == 4) {
                $colocar = isset($_SESSION[_S]['prm']['X'][4][$w['opc_id']][1]) && $_SESSION[_S]['mod_id'] == 'M';
            }
            if ($colocar) {
                $card = new stdClass();
                $card->id = 'X4' . $w['opc_id'];
                $card->titulo = '';
                $card->img = 'img/' . $w['opc_imagen'] . '.png';
                $card->label = $w['opc_titulo'];
                $card->title = $w['opc_titulo'];
                $cards[] = $card;

                $this->mixScript('
                    $("#X4' . $w['opc_id'] . '").click(function(){
                        $("#btnregresarHome").show();
                        callbackComunica = function(){ comunica( "srv/' . $w['opc_url'] . '.php"); };
                        comunica("srv/Controlador.php",{idPantalla:$(this).attr("id") });   /* ayuda */
                    });
                ');
            }
            $colocar = true;
        }

        if (!empty($cards)) {
            $this->mixHtml($this->ponCards($cards));
        }
        
        $this->mixScript('
            $("#btnregresarHome").hide();
        ');
    }
}
new ControladorUtilerias();
