<?php

/*
  Fecha de creacion     : Feb 24, 2012, 4:24:25 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase modelo del mantenimiento de la tabla s_pos
 */

class SPosModel extends Model {


    function __construct() {
        
    }

    /*
     * Actualiza un registro en la tabla s_pos
     * Devuelve >=1 si la operacion tuvo exito
     */

    function actualizarRegistro($s_pos_id, $printerserial, $taxauthorization, $warehouse, $pricelist, $ejectconfig, $ejectlines) {
        global $sqlca;
        $query = "update s_pos set printerserial = '$printerserial', taxauthorization = '$taxauthorization', 
                warehouse = '$warehouse', pricelist = '$pricelist', ejectconfig = $ejectconfig, 
                ejectlines = $ejectlines where s_pos_id  = $s_pos_id";
        $f = 0;
        $sqlca->query($query);
        $result = $sqlca->cursors['_default'];
        $f = pg_affected_rows($result);
        echo "Im here " . $f;
        return $f;
    }

    /*
     * Obtiene un registro de la tabla s_pos
     * Devuelve un array con los datos de la consulta
     */

    function obtenerRegistro($registroid) {
        global $sqlca;
        $query = "SELECT s_pos_id, p.name, numerator_tax, numerator_internal, 
                numerator_z, printerserial, taxauthorization, warehouse, 
                pricelist, ejectconfig, ejectlines, terminaldata, 
                p.s_postype_id, s.name as name_postype
                FROM s_pos p
                JOIN s_postype s ON p.s_postype_id = s.s_postype_id
                WHERE s_pos_id= $registroid";

        $sqlca->query($query);
        $registro = $sqlca->fetchRow();

        return $registro;
    }

    /*
     * Devuelve un arreglo con los almacenes registrados
     */

    function obtenerAlmacenes() {
        global $sqlca;
        $sql = "select ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes
        where ch_clase_almacen = '1'";
        $sqlca->query($sql);
        $array = $sqlca->fetchAll();
        return $array;
    }

    /**
     * Devuelve un arreglo con las listas de precios 
     */
    function listasPrecios() {
        global $sqlca;
        $sql = "select tab_elemento, tab_descripcion from int_tabla_general 
            where tab_tabla = 'LPRE' and
            tab_elemento != '000000'";
        $sqlca->query($sql);
        $array = $sqlca->fetchAll();

        return $array;
    }

    /*
     * Genera un listado de datos para realizar paginacion
     * Devuelve un array con los datos de la consulta
     */

    function listado($filtro = array(), $pp, $pagina) {
        global $sqlca;
        $query = "SELECT  s_pos_id, p.name, numerator_tax, numerator_internal, 
                numerator_z, printerserial, taxauthorization, 
                warehouse, ch_nombre_almacen,
                pricelist, i.tab_descripcion as name_pricelist, 
                ejectconfig, ejectlines, terminaldata, p.s_postype_id, 
                s.name as name_postype
                FROM s_pos p
                JOIN s_postype s ON p.s_postype_id = s.s_postype_id
                JOIN int_tabla_general i ON trim(i.tab_elemento) = p.pricelist
                JOIN inv_ta_almacenes a ON ch_almacen = warehouse
                WHERE i.tab_tabla = 'LPRE'
                AND i.tab_elemento != '000000'
                AND a.ch_clase_almacen = '1' 
                ORDER BY s_pos_id ";

        $sqlca->query($query);
        $array = $sqlca->fetchAll();
        $numrows = sizeof($array);

        if ($pp && $pagina) {
            $paginador = new paginador($numrows, $pp, $pagina);
        } else {
            $paginador = new paginador($numrows, 100, 0);
        }

        $listado2['partir'] = $paginador->partir();
        $listado2['fin'] = $paginador->fin();
        $listado2['numero_paginas'] = $paginador->numero_paginas();
        $listado2['pagina_previa'] = $paginador->pagina_previa();
        $listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
        $listado2['pp'] = $paginador->pp;
        $listado2['paginas'] = $paginador->paginas();
        $listado2['primera_pagina'] = $paginador->primera_pagina();
        $listado2['ultima_pagina'] = $paginador->ultima_pagina();

        if ($pp > 0) {
            $query .= "LIMIT $pp";
        }
        if ($pagina > 0) {
            $query .= "OFFSET " . $paginador->partir();
        }

        $sqlca->query($query);
        $datos = $sqlca->fetchAll();

        $listado[] = array();

        $listado["datos"] = $datos;

        $listado['paginacion'] = $listado2;

        return $listado;
    }

}
?>
 

