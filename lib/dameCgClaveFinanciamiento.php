<?php

// Marcos: enero/2018
// Regresa un JSON con Claves de Financiamiento, su fin es para alimentar la pantalla modal que se llama con F1
// y permitir al usuario filtrar y seleccionar una clave de financiamiento
// Los usuarios del sistema de tesorería tendrán TODAS las claves a su disposición. 
//      ( Se especifica que es de tesorería en el código al dibujar la pantalla DibujaPantallaBuscarCgClaveFinaciamientoHTML(true)
// Los de nivel UR estarán limitados a su UR, si es de nivel UE también a su UE... todos los demás a la UR de la clasificación administrativa
// --------------------------------------------------------------------------------------------------------------------------------------------------------

// Si no llegaron aquí por AJAX ...
if (!( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' )) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);

require_once dirname(__FILE__) . "/constantes.php";
require_once dirname(__FILE__) . '/AccesoDatos.php';

if (!isset($_SESSION)) {
    session_start();
}

class dameCgClaveFinanciamiento extends AccesoDatos
{

    public $prm;

    public function __construct()
    {
        parent::__construct();

        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if (!empty($_POST))
            $this->prm = json_decode(json_encode($_POST));
        else
            $this->prm = new stdClass();

        
        $fed = (isset($this->prm->fed) && $this->prm->fed == "true") ? 'S' : 'N';  // ter_id = 'B' -> FEDERALES
        $est = (isset($this->prm->est) && $this->prm->est == "true") ? 'S' : 'N';  // ter_id = 'A' -> ESTATALES
        $ur116 = (isset($this->prm->ur116) && $this->prm->ur116 == "true") ? 'S' : 'N';  // ter_id = 'A' -> ESTATALES

        $paraTesoreria = ( isset($this->prm->t) ) ? $this->prm->t : '0';  // ter_id = 'A' -> ESTATALES

        $anio = ( ( isset($this->prm->a) ) ? trim($this->prm->a) : "" );
        $con  = strtoupper( ( ( isset($this->prm->c) ) ? trim($this->prm->c) : "" ) );

        $paginaActual = ( ( isset($this->prm->p) && $this->prm->p != "0" ) ? $this->prm->p : "" );

        // FILTROS
        // DEFINICIÓN: ISRAEL, ENERO 2018
        // SI ES PARA UN USUARIO DE TESORERIA (EL PROGRAMADOR LO ESPECIFICA EN EL CÓDIGO), NO APLICARA
        // NINGÚN WHERE, YA QUE TESORERÍA ES LA DUEÑA DE LAS CLAVES DE FINANCIAMIENTO Y PUEDE VERLAS TODAS
        IF ( $paraTesoreria == "1" ) {
            // No aplicaremos ningún WHERE de UR o carga de trabajo... simplemente los que pidan para filtrar la pantalla
            $where = "WHERE 1 = 1 "; // Para que funcione, ya que los filtros parten de AND... no de WHERE :P
        }
        elseif ( $_SESSION[_S]['clasAdm-gpo']==1 && $_SESSION[_S]['clasAdm-ur']==16 && $ur116  == "N") {
            // No aplicaremos ningún WHERE de UR o carga de trabajo... simplemente los que pidan para filtrar la pantalla
            $where = "WHERE 1 = 1 "; // Para que funcione, ya que los filtros parten de AND... no de WHERE :P
        } else {
            // Si es de analista, podrá ver todas las URs de su carga de trabajo
            if ( $_SESSION[_S]['nvj_id'] >= 3 ) {
                $where = "INNER JOIN cat_CargaTrabajo t ON t.gpo_id = p.gpo_id AND t.ur_id = p.ur_id
                    WHERE t.usr_id = " . $_SESSION[_S]['usr_id'] . " ";
            // Si el nivel jerárquico del usuario es UR, limitamos a las claves de financiamiento de su UR
            } else if ( $_SESSION[_S]['nvj_id'] == 2 ) {
                $where = "WHERE p.gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND p.ur_id = " . $_SESSION[_S]['clasAdm-ur'] . " ";
            // Si es de UE, además sólo podrá ver las claves de su UE
            } else if ( $_SESSION[_S]['nvj_id'] == 1 ) {
                $where = "WHERE p.gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND p.ur_id = " . $_SESSION[_S]['clasAdm-ur'] . " AND p.ue_id = " . $_SESSION[_S]['clasAdm-ue'] . " ";
            // Si llegamos hasta aquí, limitaremos a la UR de la clasificación administrativa actual
            } else {
                $where = "WHERE p.gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND p.ur_id = " . $_SESSION[_S]['clasAdm-ur'] . " ";
            }


        }

        // Tipo de Recurso
        if ($fed != "" && $est != "") {
            // Si ambos están marcados, es como si ninguno estuviera marcado... ergo, no hacemos nada
            if ( $fed == "S" && $est == "S") {
                // no hacemos nada
            } else if ( $fed == "S") {
                $where .= " AND ter_id = 'B' ";
            } else if ( $est == "S") {
                $where .= " AND ter_id = 'A' ";
            }
        }

        // bdo_id
        if ($anio != "") {
            $where .= " AND cfi_anio = " . $anio . " ";
        }

        // Concepto: OJO, también usaremos LIKE... mismas observaciones que la Referencia
        if ($con != "") {
            $where .= " AND cfi_nombre LIKE '%" . $con . "%' ";
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = ( ( $paginaActual - 1 ) * LINEAS_POR_PAGINA );
        }

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $this->conectar();
        $this->setSql("
            SELECT ecf_id as ecf, p.gpo_id as gpo, p.ur_id as ur, MIN(p.ue_id) as ue, ter_id as ter, cfi_anio as anio, cfi_clave as cve, cfi_nombre as nom, sts_id as sts
            FROM presupuesto p
            JOIN enc_ClaveFinanciamiento USING(ecf_id,cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." cf USING(cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso".APP_EJERCICIO." c USING( cff_id, fte_id, sft_id, ori_id, sor_id ) " .
            $where . "
            GROUP BY p.gpo_id, p.ur_id,ter_id, cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio,cfi_clave,cfi_nombre
            LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";"
        );
        $this->consultar();

        $this->desconectar();
        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
         $rows = $this->getNumeroRegistros();
        if ($paginaActual == "") {
            
            $nTotalPages = ceil($rows/ LINEAS_POR_PAGINA);
        } else {

            $nTotalPages = 0;
        }

        if ($this->getNumeroRegistros() == 0) {
            echo json_encode(
                array(
                    array('ecf' => '0', 'gpo' => '', 'ur' => '', 'ue' => '', 'ter' => '', 'anio' => '', 'cve' => '', 'nom' => '', 'sts' => ''),
                    array('ecf' => '0', 'gpo' => 'NO SE ENCONTRÓ INFORMACIÓN', 'ur' => '', 'ue' => '', 'ter' => '', 'anio' => '', 'cve' => '', 'nom' => '', 'sts' => '')
                )
            );
        } else {
            $data = array(array('ecf' => $nTotalPages, 'gpo' => '', 'ur' => '', 'ue' => '', 'ter' => '', 'anio' => '', 'cve' => '', 'nom' => '', 'sts' => '') );
            while ($row = $this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    }
}
new dameCgClaveFinanciamiento();