<?php

class DescuentosModel extends Model
{
    function obtenerDescuentos()
    {
	    global $sqlca;

	    $sql = "SELECT
		            des.ch_correlativo,
		            tab.tab_elemento,
		            tab.tab_descripcion,
		            des.ch_almacen,
		            des.ch_codigo_articulo,
		            art.art_descripcion,
		            des.dt_fecha_inicio,
		            des.dt_fecha_fin,
		            des.nu_impo_descuento_unidad
		        FROM
		            pos_descuentos_especiales des,
		            int_articulos art,
		            int_tabla_general tab
		        WHERE
			            art.art_codigo=des.ch_codigo_articulo
		            AND tab.tab_tabla='CLUB'
		            AND tab.tab_elemento=des.ch_codigo_club
                ORDER BY
                    des.ch_correlativo
		        ;
		        ";

	    if ($sqlca->query($sql) < 0) return false;

	    $result = Array();

	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
	        $a = $sqlca->fetchRow();

            $result[$a[0]]['ch_codigo_club'] = $a[1] . " - " . $a[2];
	        $result[$a[0]]['ch_almacen'] = $a[3];
	        $result[$a[0]]['art_codigo'] = $a[4];
	        $result[$a[0]]['art_descripcion'] = $a[5];
	        $result[$a[0]]['dt_fecha_inicio'] = $a[6];
	        $result[$a[0]]['dt_fecha_fin'] = $a[7];
	        $result[$a[0]]['nu_impo_descuento_unidad'] = $a[8];

	    }

	    return $result;
    }

    function obtenerClubes()
    {
        global $sqlca;

        $sql = "SELECT
                    tab_elemento,
                    tab_descripcion
                FROM
                    int_tabla_general
                WHERE
                        tab_tabla='CLUB'
                    AND tab_elemento!='000000'
                ORDER BY
                    tab_elemento
                ;
                ";
        if ($sqlca->query($sql) < 0) return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[$a[0]] = $a[1];
        }

        return $result;
    }

    function obtenerAlmacenes()
    {
            global $sqlca;

            $sql = "SELECT
                        ch_almacen,
                        ch_nombre_almacen
                    FROM
                        inv_ta_almacenes
                    WHERE
                        ch_clase_almacen='1'
                    ORDER BY
                        ch_almacen
                    ;
                    ";
            if ($sqlca->query($sql) < 0) return false;

            $result = Array();

            for($i = 0; $i < $sqlca->numrows(); $i++) {
                $a = $sqlca->fetchRow();

                $result[$a[0]] = $a[1];
            }

            return $result;
    }

    function obtenerCombustibles()
    {
            global $sqlca;

            $sql = "SELECT
                        ch_codigocombustible,
                        ch_nombrecombustible
                    FROM
                        comb_ta_combustibles
                    ORDER BY
                        ch_codigocombustible
                    ;
                    ";
            if ($sqlca->query($sql) < 0) return false;

            $result = Array();

            for ($i = 0; $i < $sqlca->numrows(); $i++) {
                $a = $sqlca->fetchRow();

                $result[$a[0]] = $a[1];
            }

            return $result;
    }

    function agregarDescuento($ch_almacen, $art_codigo, $dt_inicio, $hr_inicio, $dt_fin, $hr_fin, $nu_descuento, $ch_club)
    {
        global $sqlca;

        if ($_SESSION['almacen'] != "001") return;
        list($dia,$mes,$ano) = sscanf($dt_inicio, "%2s/%2s/%4s");
        list($hora, $minutos) = sscanf($hr_inicio, "%2s:%2s");
        $dt_fecha_inicio = mktime($hora, $minutos, 0, $mes, $dia, $ano);
        if ($dt_fecha_inicio == FALSE || $dt_fecha_inicio == -1) return false;
        $dt_fecha_inicio = date("Y-m-d H:i:00", $dt_fecha_inicio);

        list($dia,$mes,$ano) = sscanf($dt_fin, "%2s/%2s/%4s");
        list($hora, $minutos) = sscanf($hr_fin, "%2s:%2s");
        $dt_fecha_fin = mktime($hora, $minutos, 0, $mes, $dia, $ano);
        if ($dt_fecha_fin == FALSE || $dt_fecha_fin == -1) return false;
        $dt_fecha_fin = date("Y-m-d H:i:59", $dt_fecha_fin);

        $sql = "INSERT INTO
                    pos_descuentos_especiales
                    (
                        ch_correlativo,
                        dt_fecha_proceso,
                        ch_sucursal,
                        ch_almacen,
                        ch_codigo_articulo,
                        ch_codigo_ubicacion,
                        ch_tipo_punto,
                        ch_codigo_club,
                        dt_fecha_inicio,
                        dt_fecha_fin,
                        nu_impo_descuento_unidad,
                        nu_porc_descuento_unidad,
                        ch_usuario,
                        ch_ip_modificacion
                    )
                    VALUES
                    (
                        to_char(nextval('pos_descuentos_seq'),'0000000'),
                        now(),
                        '" . pg_escape_string($ch_almacen) . "',
                        '" . pg_escape_string($ch_almacen) . "',
                        '" . pg_escape_string($art_codigo) . "',
                        ' ',
                        ' ',
                        '" . pg_escape_string($ch_club) . "',
                        '" . pg_escape_string($dt_fecha_inicio) . "',
                        '" . pg_escape_string($dt_fecha_fin) . "',
                        '" . pg_escape_string($nu_descuento) . "',
                        0,
                        '" . pg_escape_string($_SESSION['txtusuario']) . "',
                        '" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "'
                    )
                ;
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0) return false;

        return true;
    }

    function borrar($key)
    {
        global $sqlca;
        if ($_SESSION['almacen'] != "001") return;
        $sql = "DELETE FROM
                    pos_descuentos_especiales
                WHERE
                    ch_correlativo='" . pg_escape_string($key) . "'
                ;
                ";
        if ($sqlca->query($sql) < 0) return false;
        return true;
    }

    function obtenerDescuento($key)
    {
        global $sqlca;

	    $sql = "SELECT
		            des.ch_codigo_club,
		            des.ch_almacen,
		            des.ch_codigo_articulo,
		            des.dt_fecha_inicio,
		            des.dt_fecha_fin,
		            des.nu_impo_descuento_unidad,
                    des.ch_correlativo
		        FROM
		            pos_descuentos_especiales des
		        WHERE
                    ch_correlativo='" . pg_escape_string($key) . "'
		        ;
		        ";
        if ($sqlca->query($sql) < 0) return false;

        $a = $sqlca->fetchRow();

        $result['ch_codigo_club'] = $a[0];
	    $result['ch_almacen'] = $a[1];
	    $result['art_codigo'] = $a[2];
	    $result['dt_fecha_inicio'] = $a[3];
	    $result['dt_fecha_fin'] = $a[4];
	    $result['nu_impo_descuento_unidad'] = $a[5];
        $result['ch_correlativo'] = $a[6];

        return $result;
    }

    function modificarDescuento($ch_correlativo, $ch_almacen, $art_codigo, $dt_inicio, $hr_inicio, $dt_fin, $hr_fin, $nu_descuento, $ch_club)
    {
        global $sqlca;

//        if ($_SESSION['almacen'] != "001") return;

        list($dia,$mes,$ano) = sscanf($dt_inicio, "%2s/%2s/%4s");
        list($hora, $minutos) = sscanf($hr_inicio, "%2s:%2s");
        $dt_fecha_inicio = mktime($hora, $minutos, 0, $mes, $dia, $ano);
        if ($dt_fecha_inicio == FALSE || $dt_fecha_inicio == -1) return false;
        $dt_fecha_inicio = date("Y-m-d H:i:00", $dt_fecha_inicio);

        list($dia,$mes,$ano) = sscanf($dt_fin, "%2s/%2s/%4s");
        list($hora, $minutos) = sscanf($hr_fin, "%2s:%2s");
        $dt_fecha_fin = mktime($hora, $minutos, 0, $mes, $dia, $ano);
        if ($dt_fecha_fin == FALSE || $dt_fecha_fin == -1) return false;
        $dt_fecha_fin = date("Y-m-d H:i:59", $dt_fecha_fin);

        $sql = "UPDATE
                    pos_descuentos_especiales
                SET
                    ch_sucursal='" . pg_escape_string($ch_almacen) . "',
                    ch_almacen='" . pg_escape_string($ch_almacen) . "',
                    ch_codigo_articulo='" . pg_escape_string($art_codigo) . "',
                    ch_codigo_club='" . pg_escape_string($ch_club) . "',
                    dt_fecha_inicio='" . pg_escape_string($dt_fecha_inicio) . "',
                    dt_fecha_fin='" . pg_escape_string($dt_fecha_fin) . "',
                    nu_impo_descuento_unidad='" . pg_escape_string($nu_descuento) . "',
                    ch_usuario='" . pg_escape_string($_SESSION['txtusuario']) . "',
                    ch_ip_modificacion='" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "',
                    flg_replicacion=0
                WHERE
                    ch_correlativo='" . pg_escape_string($ch_correlativo) . "'
                ;
                ";
        if ($sqlca->query($sql) < 0) return false;
        return true;
    }

    function copiar($descuentos)
    {
        if ($_SESSION['almacen'] != "001") return;
        foreach($descuentos as $i => $ch_correlativo)
        {
            $descuento = DescuentosModel::obtenerDescuento($ch_correlativo);

            list($ano,$mes,$dia,$hora,$minutos,$segundos) = sscanf($descuento['dt_fecha_inicio'], "%4s-%2s-%2s %2s:%2s:%2s");
            $dt_inicio = $dia . "/" . $mes . "/" . $ano;
            $ht_inicio = $hora . ":" . $minutos;

            list($ano,$mes,$dia,$hora,$minutos,$segundos) = sscanf($descuento['dt_fecha_fin'], "%4s-%2s-%2s %2s:%2s:%2s");
            $dt_fin = $dia . "/" . $mes . "/" . $ano;
            $ht_fin = $hora . ":" . $minutos;

            DescuentosModel::agregarDescuento($descuento['ch_almacen'], $descuento['art_codigo'], $dt_inicio, $hr_inicio, $dt_fin, $hr_fin, $descuento['nu_impo_descuento_unidad'], $descuento['ch_codigo_club']);
        }
    }
}


