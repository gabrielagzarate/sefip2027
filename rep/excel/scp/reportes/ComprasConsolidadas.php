<?php
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/AccesoDatos.php';
class ComprasConsolidadas extends generaPhpspreadsheet
{
    private $encabezadoLin1;
    private $encabezadoLin2;
    private $encabezadoLin3;
    private $encabezadoLin4;
    private $encabezadoLin5;

    public function __construct()
    {
        parent::__construct();
        $this->header();
        $this->content();
    }

    private function header()
    {
        $this->encabezadoLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
        $this->encabezadoLin2 = "SECRETARÍA DE FINANZAS";
        $this->encabezadoLin3 = "";
        $this->encabezadoLin4 = "Consulta de Compras Consolidadas";
        $this->encabezadoLin5 = "";
    }

    private function content()
    {
        $encabezado = array(
            'encabLin1' => $this->encabezadoLin1,
            'encabLin2' => $this->encabezadoLin2,
            'encabLin3' => $this->encabezadoLin3,
            'encabLin4' => $this->encabezadoLin4,
            'encabLin5' => $this->encabezadoLin5,
        );

        $tabla = array();
        $nombreColumnas = array(
            "Entidad",
            "Nombre Entidad",
            "Unidad",
            "Nombre Unidad",
            "Capitulo",
            "Nombre Capitulo",
            "Programa",
            "Nombre Programa",
            "SubPrograma",
            "Nombre SubPrograma",
            "Proyecto",
            "Nombre Proyecto",
            "Obra/Actividad",
            "Nombre Obra/Actividad",
            "Partida Genérica",
            "Nombre Partida Genérica",
            "Partida Especifica",
            "Nombre Partida Especifica",
            "Id. Partida",
            "Clave de Financiamiento",
            "Nombre Clave",
            "Tipo de Compra",
            "Clave Completa",
            "Aprobado",
            "Modificado",
            "Comprometido",
            "Saldo (modif.-Comprom)",
            "vig_ene",
            "vig_feb",
            "vig_mar",
            "vig_abr",
            "vig_may",
            "vig_jun",
            "vig_jul",
            "vig_ago",
            "vig_sep",
            "vig_oct",
            "vig_nov",
            "vig_dic",
            "com_ene",
            "com_feb",
            "com_mar",
            "com_abr",
            "com_may",
            "com_jun",
            "com_jul",
            "com_ago",
            "com_sep",
            "com_oct",
            "com_nov",
            "com_dic",
        );

        $incluirColumnas = array(
            "Entidad",
            "Nombre Entidad",
            "Unidad",
            "Nombre Unidad",
            "Capitulo",
            "Nombre Capitulo",
            "Programa",
            "Nombre Programa",
            "SubPrograma",
            "Nombre SubPrograma",
            "Proyecto",
            "Nombre Proyecto",
            "Obra/Actividad",
            "Nombre Obra/Actividad",
            "Partida Genérica",
            "Nombre Partida Genérica",
            "Partida Especifica",
            "Nombre Partida Especifica",
            "Id. Partida",
            "Clave de Financiamiento",
            "Nombre Clave",
            "Tipo de Compra",
            "Clave Completa",
            "Aprobado",
            "Modificado",
            "Comprometido",
            "Saldo (modif.-Comprom)",
            "vig_ene",
            "vig_feb",
            "vig_mar",
            "vig_abr",
            "vig_may",
            "vig_jun",
            "vig_jul",
            "vig_ago",
            "vig_sep",
            "vig_oct",
            "vig_nov",
            "vig_dic",
            "com_ene",
            "com_feb",
            "com_mar",
            "com_abr",
            "com_may",
            "com_jun",
            "com_jul",
            "com_ago",
            "com_sep",
            "com_oct",
            "com_nov",
            "com_dic",
        );
        $tipoColumnas = [12, 'Cc'];
        $AnchoColumnas = [
            1 => 40,
            3 => 50,
            7 => 50,
            9 => 50,
            13 => 60,
            15 => 100,
            17 => 100,
            18 => 50,
            20 => 50,
            23 => 50,
        ];

        foreach ($incluirColumnas as $contador => $valor) {
            $tabla['nombre'][$valor] = $nombreColumnas[$contador];
            $tabla['campo'][$valor] = $contador;
            isset($tipoColumnas[$contador]) ? $tabla['tipo'][$valor] = $tipoColumnas[$contador] : '';
            $tabla['tipoTabla'] = "A";
            $tabla['ancho'][$valor] = isset($AnchoColumnas[$contador]) ? $AnchoColumnas[$contador] : 20;
        }

        $datos = $this->dameInformacion();
        $this->generaExcel($datos, $encabezado, $tabla, array("nombre" => $this->encabezadoLin4));
    }

    private function dameInformacion()
    {
        $comprasConsolidadas = array();
        $sql = 'SELECT CONCAT(pre.gpo_id,pre.ur_id) AS entidad, cat_UnidadResponsable.ur_nombre,CAST(pre.ue_id AS CHAR) AS ue_id,cat_UnidadEjecutora.ue_nombre,
        pre.cap_id AS capitulo,cap_nombre,CAST(prg_id AS CHAR) AS Programa, cat_Programa.prg_nombre AS desPrograma,CAST(spr_id AS CHAR) AS Subprograma,cat_Subprograma.spr_nombre AS desSubprograma,
            CAST(pre.py_id AS CHAR) AS proyecto,cat_Proyecto.py_nombre AS desproyecto,CONCAT(pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,oa_id) AS ObraoAccion,
            cat_ObraActividad.oa_nombre AS obra_nombre,pre.pg_id AS p_generica,cat_PartidaGenerica.pg_nombre,pre.pe_id AS p_especifica,cat_PartidaEspecifica.pe_nombre,
            cat_PartidaEspecifica.idp_id,cat_ClaveFinanciamiento' . APP_EJERCICIO . '.cfi_clave AS cvefin,cat_ClaveFinanciamiento' . APP_EJERCICIO . '.cfi_nombre,pre.tcp_id,pre.pre_clavecompleta,
            pre.pre_aprobado,pre.pre_vigente,pre.pre_comprometido,pre.pre_vigente-pre.pre_comprometido AS saldo,
            vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
            com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic
        FROM (SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
            tcp_id,pre_clavecompleta,SUBSTRING(pg_id,1,1) AS cap_id,
            pre_aprobado,pre_vigente,pre_comprometido,
            vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
            com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic
        FROM presupuesto
        JOIN presupuestoAprobado USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoVigente USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoComprometido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE (pre_aprobado<>0 OR pre_vigente<>0) AND tcp_id="C"  -- condici�n fija
        GROUP BY pre_clavecompleta,tcp_id) AS pre
        LEFT JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        LEFT JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
        LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
        LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
        LEFT JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
        LEFT JOIN cat_ObraActividad USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        LEFT JOIN cat_Capitulo USING (cap_id)
        LEFT JOIN cat_PartidaGenerica USING(pg_id)
        LEFT JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        LEFT JOIN (SELECT * FROM sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' WHERE cfi_ejercicio2 = ?) AS cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        ORDER BY pre.pre_clavecompleta,pre.tcp_id';

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([APP_EJERCICIO]);

        while ($row = $conexion->extraer()) {
            $dato = array(
                "Entidad" => $row['entidad'],
                "Nombre Entidad" => $row['ur_nombre'],
                "Unidad" => $row['ue_id'],
                "Nombre Unidad" => $row['ue_nombre'],
                "Capitulo" => $row['capitulo'],
                "Nombre Capitulo" => $row['cap_nombre'],
                "Programa" => $row['Programa'],
                "Nombre Programa" => $row['desPrograma'],
                "SubPrograma" => $row['Subprograma'],
                "Nombre SubPrograma" => $row['desSubprograma'],
                "Proyecto" => $row['proyecto'],
                "Nombre Proyecto" => $row['desproyecto'],
                "Obra/Actividad" => $row['ObraoAccion'] . ' ',
                "Nombre Obra/Actividad" => $row['obra_nombre'],
                "Partida Genérica" => $row['p_generica'],
                "Nombre Partida Genérica" => $row['pg_nombre'],
                "Partida Especifica" => $row['p_especifica'],
                "Nombre Partida Especifica" => $row['pe_nombre'],
                "Id. Partida" => $row['idp_id'],
                "Clave de Financiamiento" => $row['cvefin'],
                "Nombre Clave" => $row['cfi_nombre'],
                "Tipo de Compra" => $row['tcp_id'],
                "Clave Completa" => $row['pre_clavecompleta'],
                "Aprobado" => $row['pre_aprobado'],
                "Modificado" => $row['pre_vigente'],
                "Comprometido" => $row['pre_comprometido'],
                "Saldo (modif.-Comprom)" => $row['saldo'],
                "vig_ene" => $row['vig_ene'],
                "vig_feb" => $row['vig_feb'],
                "vig_mar" => $row['vig_mar'],
                "vig_abr" => $row['vig_abr'],
                "vig_may" => $row['vig_may'],
                "vig_jun" => $row['vig_jun'],
                "vig_jul" => $row['vig_jul'],
                "vig_ago" => $row['vig_ago'],
                "vig_sep" => $row['vig_sep'],
                "vig_oct" => $row['vig_oct'],
                "vig_nov" => $row['vig_nov'],
                "vig_dic" => $row['vig_dic'],
                "com_ene" => $row['com_ene'],
                "com_feb" => $row['com_feb'],
                "com_mar" => $row['com_mar'],
                "com_abr" => $row['com_abr'],
                "com_may" => $row['com_may'],
                "com_jun" => $row['com_jun'],
                "com_jul" => $row['com_jul'],
                "com_ago" => $row['com_ago'],
                "com_sep" => $row['com_sep'],
                "com_oct" => $row['com_oct'],
                "com_nov" => $row['com_nov'],
                "com_dic" => $row['com_dic'],
            );
            array_push($comprasConsolidadas, $dato);
        }
        $conexion->desconectar();
        return $comprasConsolidadas;
    }
}
$exp = new ComprasConsolidadas();
