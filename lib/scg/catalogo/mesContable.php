<?php
declare (strict_types = 1);
function mesContable($gpo_id, $ur_id, $nivel, $usr_id)
{

    $wh = $where = $order = $group = $join = $select = '';

    switch ($nivel) {
        case 1:case 2:
            $group = ' GROUP BY msc_periodo';
            $order = ' ORDER BY msc_periodo';
            $where = ' AND  e.gpo_id = ' . $gpo_id . ' AND e.ur_id = ' . $ur_id . ' ';
            $wh = ' WHERE  gpo_id = ' . $gpo_id . ' AND ur_id = ' . $ur_id . ' ';
            break;
        case 3:case 4:case 5:
            $join = ' INNER JOIN cat_CargaTrabajo using(gpo_id,ur_id) ';
            $group = ' GROUP BY gpo_id,ur_id ';
            $order = ' ORDER BY gpo_id,ur_id ';
            $where = ' AND  cat_CargaTrabajo.usr_id=' . $usr_id . ' ';
            $wh = ' WHERE cat_CargaTrabajo.usr_id=' . $usr_id . ' ';
            break;
        default:
            $group = ' GROUP BY gpo_id,ur_id  ';
            $order = ' ORDER BY gpo_id,ur_id ';
            $wh = ' WHERE gpo_id!=0 AND ur_id!= 0 AND ur_vigente !="NO" ';

            break;
    }

    $miconexion = new AccesoDatos();
    $miconexion->conectar();

    if ($nivel > 2) {

        $miconexion->setSql('
                SELECT concat( gpo_id,ur_id) AS ID, gpo_id,ur_id,
                    z.msc_id as msc_periodo0, z.msc_nombre,z.ems_id as ems_id0,z.ems_nombre as ems_nombre0,z.fecha as fecha0,z.msc_reabrir as msc_reabrir0,
                    a.msc_periodo as msc_periodo1 ,a.msc_nombre,a.ems_id as ems_id1,a.ems_nombre as ems_nombre1,a.fecha as fecha1,a.msc_reabrir as msc_reabrir1,
                    b.msc_periodo as msc_periodo2 ,b.msc_nombre,b.ems_id as ems_id2,b.ems_nombre as ems_nombre2,b.fecha as fecha2,b.msc_reabrir as msc_reabrir2,
                    c.msc_periodo as msc_periodo3 ,c.msc_nombre,c.ems_id as ems_id3,c.ems_nombre as ems_nombre3,c.fecha as fecha3,c.msc_reabrir as msc_reabrir3,
                    d.msc_periodo as msc_periodo4 ,d.msc_nombre,d.ems_id as ems_id4,d.ems_nombre as ems_nombre4,d.fecha as fecha4,d.msc_reabrir as msc_reabrir4,
                    e.msc_periodo as msc_periodo5 ,e.msc_nombre,e.ems_id as ems_id5,e.ems_nombre as ems_nombre5,e.fecha as fecha5,e.msc_reabrir as msc_reabrir5,
                    f.msc_periodo as msc_periodo6 ,f.msc_nombre,f.ems_id as ems_id6,f.ems_nombre as ems_nombre6,f.fecha as fecha6,f.msc_reabrir as msc_reabrir6,
                    g.msc_id as msc_periodo7 ,g.msc_nombre,g.ems_id as ems_id7,g.ems_nombre as ems_nombre7,g.fecha as fecha7,g.msc_reabrir as msc_reabrir7,
                    h.msc_id as msc_periodo8 ,h.msc_nombre,h.ems_id as ems_id8,h.ems_nombre as ems_nombre8,h.fecha as fecha8,h.msc_reabrir as msc_reabrir8,
                    i.msc_id as msc_periodo9 ,i.msc_nombre,i.ems_id as ems_id9,i.ems_nombre as ems_nombre9,i.fecha as fecha9,i.msc_reabrir as msc_reabrir9,
                    j.msc_id as msc_periodo10 ,j.msc_nombre,j.ems_id as ems_id10,j.ems_nombre as ems_nombre10,j.fecha as fecha10,j.msc_reabrir as msc_reabrir10,
                    k.msc_id as msc_periodo11 ,k.msc_nombre,k.ems_id as ems_id11,k.ems_nombre as ems_nombre11,k.fecha as fecha11,k.msc_reabrir as msc_reabrir11,
                    l.msc_id as msc_periodo12 ,l.msc_nombre,l.ems_id as ems_id12,l.ems_nombre as ems_nombre12,l.fecha as fecha12,l.msc_reabrir as msc_reabrir12,
                    m.msc_id as msc_periodo13 ,m.msc_nombre,m.ems_id as ems_id13,m.ems_nombre as ems_nombre13,m.fecha as fecha13,m.msc_reabrir as msc_reabrir13
                FROM(
                    SELECT gpo_id,ur_id
                    FROM cat_UnidadResponsable
                    ' . $join . '
                    ' . $wh . '
                )x
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo,msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=00 ' . $where . '
                    ' . $group . '
                    ' . $order . '
                )z USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=1 ' . $where . '
                    ' . $group . '
                    ' . $order . '
                )a USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo,msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=2 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )b USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=3 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )c USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo,  msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=4 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )d USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo,msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,min(msc_reabrir) as msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=5 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )e USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=6 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )f USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=7 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )g USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo,msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=8 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )h USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo,msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=9 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )i USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=10 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )j USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=11 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )k USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=12 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )l USING(gpo_id,ur_id)
                LEFT JOIN(
                    SELECT e.gpo_id,e.ur_id,msc_id,msc_periodo, msc_nombre,IF( ISNULL(ems_id), 0, ems_id) AS ems_id,msc_reabrir,
                    IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as Fecha
                    FROM  cat_MesContable
                    INNER JOIN cg_MesContable e USING(msc_id)
                    INNER JOIN cat_EstadoMesContable USING (ems_id)
                    ' . $join . '
                    WHERE  msc_id=13 ' . $where . '
                    ' . $group . '
                    ' . $order . '

                )m USING(gpo_id,ur_id)
                ORDER BY gpo_id,ur_id
            ');
    } else {
        $miconexion->setSql('
                SELECT msc_periodo, msc_nombre,
                IF( ISNULL(ems_id), 0, ems_id) ems_id,(msc_fechaAbierto) AS abierto  ,(msc_fechaCerrado) AS cerrado,
                IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre ,IF(ISNULL(usr_usuario), "", usr_usuario) AS usr_usuario
                FROM  cat_MesContable
                LEFT JOIN cg_MesContable e USING(msc_id)
                LEFT JOIN cat_Usuario u ON e.usr_id=u.usr_id
                LEFT JOIN cat_EstadoMesContable USING (ems_id)
                WHERE e.gpo_id = ' . $gpo_id . ' AND e.ur_id = ' . $ur_id . ' OR ISNULL(e.msc_id)
                ORDER BY msc_id
            ');

    }

    $miconexion->consultar();
    $miconexion->desconectar();

    $datos = array();
    if ($nivel > 2) {
        while ($rowCatalogo = $miconexion->extraer()) {
            $datos[$rowCatalogo['ID']] = array(
                'gpour_id' => $rowCatalogo['ID'],
                'gpo_id' => $rowCatalogo['gpo_id'],
                'ur_id' => $rowCatalogo['ur_id'],
                'ems_nombre0' => $rowCatalogo['ems_nombre0'],
                'ems_nombre1' => $rowCatalogo['ems_nombre1'],
                'ems_nombre2' => $rowCatalogo['ems_nombre2'],
                'ems_nombre3' => $rowCatalogo['ems_nombre3'],
                'ems_nombre4' => $rowCatalogo['ems_nombre4'],
                'ems_nombre5' => $rowCatalogo['ems_nombre5'],
                'ems_nombre6' => $rowCatalogo['ems_nombre6'],
                'ems_nombre7' => $rowCatalogo['ems_nombre7'],
                'ems_nombre8' => $rowCatalogo['ems_nombre8'],
                'ems_nombre9' => $rowCatalogo['ems_nombre9'],
                'ems_nombre10' => $rowCatalogo['ems_nombre10'],
                'ems_nombre11' => $rowCatalogo['ems_nombre11'],
                'ems_nombre12' => $rowCatalogo['ems_nombre12'],
                'ems_nombre13' => $rowCatalogo['ems_nombre13'],
            );
        }
    } else {
        while ($rowCatalogo = $miconexion->extraer()) {
            $datos[$rowCatalogo['msc_nombre']] = array(
                'msc_nombre' => $rowCatalogo['msc_nombre'],
                'abierto' => $rowCatalogo['abierto'],
                'cerrado' => $rowCatalogo['cerrado'],
                'ems_nombre' => $rowCatalogo['ems_nombre'],
                'usr_usuario' => $rowCatalogo['usr_usuario'],
            );
        }
    }

    return $datos;

}
