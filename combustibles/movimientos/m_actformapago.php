<?php

class ActFormaPagoModel extends Model {

    function obtenerAlmacenes(){
        global $sqlca;

        $sql = "
        	SELECT
                ch_almacen,
                ch_almacen||' - '||ch_nombre_almacen
            FROM
                inv_ta_almacenes
            WHERE
                ch_clase_almacen = '1'
            ORDER BY
            	1;
		";
        if ($sqlca->query($sql) < 0)
        	return false;

        $result = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $ch_almacen 		= $a[0];
            $ch_nombre_almacen	= $a[1];
            $result[$ch_almacen] = $ch_nombre_almacen;
        }
        return $result;
    }

	function busqueda($almacen, $fecha, $turno, $caja) {
        	global $sqlca;

		$fecha_dc = explode('/', $fecha, 3);
		settype($fecha_dc[0], "int");
		settype($fecha_dc[1], "int");
		settype($fecha_dc[2], "int");
		settype($turno, "int");

		if ($fecha_dc[0] < 10)
		    $fecha_dc[0] = "0" . $fecha_dc[0];
		if ($fecha_dc[1] < 10)
		    $fecha_dc[1] = "0" . $fecha_dc[1];

		$diabuscado = $fecha_dc[2] . "-" . $fecha_dc[1] . "-" . $fecha_dc[0];

		$es = $_SESSION['almacen'];

		//VALIDAR CONSOLIDACION
		if(empty($almacen))
			$sql = "SELECT validar_consolidacion('" . $diabuscado . "', " . $turno . ",'" . $es . "')";
		else
			$sql = "SELECT validar_consolidacion('" . $diabuscado . "'," . $turno . ",'" . $almacen . "')";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if ($estado[0] == 1)
			return "CONSOLIDADO";

		$query = "
			SELECT
				da_fecha,
				ch_posturno				
			FROM
		       	pos_aprosys
		    WHERE
		       	ch_poscd = 'A';
		";

		if ($sqlca->query($query) < 0)
			return false;

		$a 				= $sqlca->fetchRow();
		$dia_actual 	= $a['da_fecha'];
		$turno_actual 	= $a['ch_posturno'];

/*
		if ($turno != ""){
			if ($diabuscado == $dia_actual && $turno_actual == $turno)
				$tabla = "pos_transtmp";
			else
				$tabla = pg_escape_string("pos_trans" . $fecha_dc[2] . $fecha_dc[1]);
		} else {
			*/
			//$tabla = "pos_transtmp";
		//}
			
		if ($diabuscado == $dia_actual && $turno_actual == $turno)
			$tabla = "pos_transtmp";
		else {
			$tabla = pg_escape_string("pos_trans" . $fecha_dc[2] . $fecha_dc[1]);

			//Verificamos que exista pos_trans
			$sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = '" . pg_escape_string("pos_trans" . $fecha_dc[2] . $fecha_dc[1]) . "'";

			$tabla = "pos_transtmp";
			if($sqlca->query($sql) === 1)
				$tabla = pg_escape_string("pos_trans" . $fecha_dc[2] . $fecha_dc[1]);
		}

		$sql = "BEGIN";
		$sqlca->query($sql);

		$sql = "
			SELECT
				trans.trans as oid,
				trans.tm as tm,
				trans.td as td,
				trans.trans as trans,
				to_char(trans.fecha, 'DD/MM/YYYY') || ' ' || to_char(trans.fecha, 'HH24:MI:SS') as fecha,
				to_char(trans.dia, 'DD/MM/YYYY') as dia,
				art.art_descripcion as art_descripcion,
				trans.cantidad as cantidad,
				trans.precio as precio,
				trans.importe as importe,
				trans.turno as turno,
				trans.caja as caja,
				trans.pump as pump,
				trans.fpago as fpago,
				trans.at as at,
				trans.text1 as text1,
				trans.nombre,
				trans.documento
			FROM
		      	" . $tabla . " trans
		      	LEFT JOIN int_articulos art ON (art.art_codigo = trans.codigo)
		    WHERE
			";

			$arrSearchDate = explode('/', $fecha);
			$dSearchDate = $arrSearchDate[2].'-'.$arrSearchDate[1].'-'.$arrSearchDate[0];

			if(empty($almacen))
				$sql .="trans.es = '" . pg_escape_string($_SESSION['almacen']) . "' AND trans.td IN('B','F')";
			else
				$sql .="trans.es = '" . pg_escape_string($almacen) . "' AND trans.td IN('B','F')";

			$sql .="
		    AND trans.dia::DATE = '" . pg_escape_string($dSearchDate) . "'";

			if ($turno != "")
			    $sql .= " AND turno = '" . pg_escape_string($turno) . "' ";

			if ($caja != "" && $caja != "TODAS")
			    $sql .= " AND caja = '" . pg_escape_string($caja) . "' ";

			$sql .= "
ORDER BY
 trans.turno,
 trans.caja,
 trans.trans,
 trans.fecha
			";

		echo "<pre>";
       	print_r($sql);
       	echo "</pre>";

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

	    	$resultado[$i]['oid']			= $a[0];
	    	$resultado[$i]['tm']			= $a[1];
	    	$resultado[$i]['td'] 			= $a[2];
	    	$resultado[$i]['trans'] 		= $a[3];
	    	$resultado[$i]['fecha'] 		= $a[4];
	    	$resultado[$i]['dia'] 			= $a[5];
		   	$resultado[$i]['art_descripcion'] 	= $a[6];
	    	$resultado[$i]['cantidad'] 		= $a[7];
	    	$resultado[$i]['precio'] 		= $a[8];
	    	$resultado[$i]['importe'] 		= $a[9];
	    	$resultado[$i]['turno'] 		= $a[10];
	    	$resultado[$i]['caja'] 			= $a[11];
	    	$resultado[$i]['pump'] 			= $a[12];
	    	$resultado[$i]['fpago'] 		= $a[13];
	    	$resultado[$i]['at'] 			= $a[14];
	    	$resultado[$i]['text1'] 		= $a[15];
	    	$resultado[$i]['usuario'] 		= $a[16];
	    	$resultado[$i]['documento'] 		= $a[17];
		}
		return $resultado;
	}

	function obtenerFila($oid, $caja, $fecha, $td, $turno) {
        	global $sqlca;

		$fecha_dc = explode('/', $fecha, 3);
		settype($fecha_dc[0], "int");
		settype($fecha_dc[1], "int");
		settype($fecha_dc[2], "int");
		settype($turno, "int");

		if ($fecha_dc[0] < 10)
		    $fecha_dc[0] = "0" . $fecha_dc[0];
		if ($fecha_dc[1] < 10)
		    $fecha_dc[1] = "0" . $fecha_dc[1];

		$diabuscado = $fecha_dc[2] . "-" . $fecha_dc[1] . "-" . $fecha_dc[0];


	        $query = "
			SELECT
				da_fecha,
				ch_posturno				
		       	FROM
		            	pos_aprosys
		        WHERE
		            	ch_poscd='A';
		";

		if ($sqlca->query($query) < 0)
			return false;

		$a 		= $sqlca->fetchRow();
		$dia_actual 	= $a['da_fecha'];
		$turno_actual 	= $a['ch_posturno'];

		if ($diabuscado == $dia_actual && $turno_actual == $turno)
			$tabla = "pos_transtmp";
		else
			$tabla = pg_escape_string("pos_trans" . $fecha_dc[2] . $fecha_dc[1]);

       		$sql = "SELECT
				trans.trans as oid,
				trans.tm as tm,
				trans.td as td,
				trans.trans as trans,
				to_char(trans.fecha, 'DD/MM/YYYY') || ' ' || to_char(trans.fecha, 'HH24:MI:SS') as fecha,
				trans.dia as dia,
				art.art_descripcion as art_descripcion,
				trans.cantidad as cantidad,
				trans.precio as precio,
				trans.importe as importe,
				trans.turno as turno,
				trans.caja as caja,
				trans.pump as pump,
				trans.fpago as fpago,
				trans.at as at,
				trans.text1 as text1,
				trans.nombre,
                                trans.documento
			FROM 
				" . pg_escape_string($tabla) . " trans,
				int_articulos art
			WHERE
				art.art_codigo=trans.codigo
				AND trans.trans = " . pg_escape_string($oid) . "
				AND trans.caja = '" . pg_escape_string($caja) . "'
                                AND trans.td = '" . pg_escape_string($td) . "'
			";

        	if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();
		$a = $sqlca->fetchRow();
		$resultado['oid'] = $a[0];
		$resultado['tm'] = $a[1];
		$resultado['td'] = $a[2];
		$resultado['trans'] = $a[3];
		$resultado['fecha'] = $a[4];
		$resultado['dia'] = $a[5];
		$resultado['art_descripcion'] = $a[6];
		$resultado['cantidad'] = $a[7];
		$resultado['precio'] = $a[8];
		$resultado['importe'] = $a[9];
		$resultado['turno'] = $a[10];
		$resultado['caja'] = $a[11];
		$resultado['pump'] = $a[12];
		$resultado['fpago'] = $a[13];
		$resultado['at'] = $a[14];
		$resultado['text1'] = $a[15];
		$resultado['nombre'] = $a[16];
		$resultado['cajero'] = $a[17];
		$resultado['tabla'] = $tabla;

		return $resultado;

	}

	function actualizarFila($oid, $caja, $tabla, $fpago, $at, $text1, $usuario, $td, $ntrabajador, $turno) {
        	global $sqlca;

		if ($fpago == '000000')
			$fpago = '';

		if ($at == '000000')
			$at = '';

		if (strlen($fpago) > 1)
			$fpago = substr($fpago, (strlen($fpago) - 1));

		if (strlen($at) > 1)
			$at = substr($at, (strlen($at) - 1));

	        /* Verificacion del trabajor */

	        $ntrabajador = trim($ntrabajador);

	        if(strlen($ntrabajador)>0){

		        $sql_veri = "select count(*) as exixte from pla_ta_trabajadores where ch_codigo_trabajador='$ntrabajador';";

			if ($sqlca->query($sql_veri) == -1)
           			return false;

			$a = $sqlca->fetchRow();
	
			if ($a['exixte'] < 1)
            			return false;

		}

        	$sql = "
			UPDATE 
				" . pg_escape_string($tabla) . " 
			SET
				fpago		= '" . pg_escape_string($fpago) . "',
				at		= '" . pg_escape_string($at) . "',
				nombre		= '" . pg_escape_string($usuario) . "',
				text1		= '" . pg_escape_string($text1) . "',
                                documento	= '" . pg_escape_string($ntrabajador) . "'
			WHERE
				trans		= " . pg_escape_string($oid) . "
				AND caja	= '" . pg_escape_string($caja) . "'
                                AND td		= '" . pg_escape_string($td) . "'
                                AND turno		= '" . pg_escape_string($turno) . "'
			";

		//var_dump($sql);

		if ($sqlca->query($sql) == -1)
			return false;

		return true;

	}

	function obtenerFormasDePago() {
        global $sqlca;

        $sql = "SELECT tab_elemento, tab_descripcion FROM int_tabla_general WHERE tab_tabla = '05' AND tab_elemento <> '000000';";

        if ($sqlca->query($sql) < 0)
            return false;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $id = $a[0];
            $desc = $a[1];
            $resultado[$id] = $desc;
        }

        return $resultado;
    }

    function obtenerTarjetas() {
        global $sqlca;

        $sql = "SELECT tab_elemento, tab_descripcion FROM int_tabla_general WHERE tab_tabla = '95' AND tab_elemento <> '000000';";
        if ($sqlca->query($sql) < 0)
            return false;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $id = $a[0];
            $desc = $a[1];

            $resultado[$id] = $desc;
        }

        return $resultado;
    }

}

