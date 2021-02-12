<?php

/*
  Fecha de creacion     : Marzo 6, 2012, 11:04 AM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase modelo del mantenimiento de la tabla f_pump_pos
 */

class FPumpPosModel extends Model {

    function __construct() {
        
    }

    /*
     * Actualiza un registro en la tabla f_pump_pos
     * Devuelve >=1 si la operacion tuvo exito
     */

    function actualizarRegistro($f_pump_pos_id, $s_pos_id) {

        global $sqlca;
        $query = "update f_pump_pos set s_pos_id = '$s_pos_id' 
                   where f_pump_pos_id = $f_pump_pos_id";
        $f = 0;
        $sqlca->query($query);
        $result = $sqlca->cursors['_default'];
        $f = pg_affected_rows($result);

        return $f;
    }

    /*
     * Obtiene un registro de la tabla f_pump_pos
     * Devuelve un array con los datos de la consulta
     */

    function obtenerRegistro($registroid) {
        global $sqlca;
        $query = "SELECT f_pump_pos_id,f.f_pump_id, f.s_pos_id, s.name as name_pos,
                fp.name as name_pump
                FROM f_pump_pos f 
                JOIN f_pump fp ON f.f_pump_id = fp.f_pump_id 
                JOIN s_pos s ON f.s_pos_id = s.s_pos_id
                WHERE f_pump_pos_id= $registroid";
        $sqlca->query($query);
        $registro = $sqlca->fetchRow();

        return $registro;
    }

    /**
     * Retorna los productos por lado
     * @param type $pump_id 
     */
    function productosPorLado($pump_id) {
        global $sqlca;

        $query = "select a.art_descbreve from 
                f_grade f join int_articulos a
                on f.product = a.art_codigo 
                where f.f_pump_id = $pump_id";
        $sqlca->query($query);
        $array = $sqlca->fetchAll();

        return $array;
    }

    /**
     * Retorna los puntos de venta a paritr de la tabla s_pos 
     */
    function obtenerPuntosVenta() {
        global $sqlca;
        $sql = "select s_pos_id from s_pos";
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
        $cond = '';

        $query = "SELECT f_pump_pos_id,f.f_pump_id, f.s_pos_id, s.name as name_pos,
                fp.name as name_pump
                FROM f_pump_pos f 
                JOIN f_pump fp ON f.f_pump_id = fp.f_pump_id 
                JOIN s_pos s ON f.s_pos_id = s.s_pos_id
                $cond
                ORDER BY f_pump_pos_id ";

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
 

