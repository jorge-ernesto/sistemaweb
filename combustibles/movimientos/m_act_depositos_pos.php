<?php

class ActDepositosPosModel extends Model{
	
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

	function busqueda($almacen, $dia, $turno){
        	global $sqlca;

		$sql = "BEGIN";
		$sqlca->query($sql);

		$fecha_dc = explode('/', $dia, 3);
		settype($fecha_dc[0],"int");
		settype($fecha_dc[1],"int");
		settype($fecha_dc[2],"int");
		settype($turno,"int");

		if ($turno == "TODOS")
			$turno = 0;

		if ($fecha_dc[0] < 10)
		    $fecha_dc[0] = "0" . $fecha_dc[0];
		if ($fecha_dc[1] < 10)
		    $fecha_dc[1] = "0" . $fecha_dc[1];

		$diabuscado = $fecha_dc[2] . "-" . $fecha_dc[1] . "-" . $fecha_dc[0];

		$es = $_SESSION['almacen'];

		//VALIDAR CONSOLIDACION

		if(empty($almacen))
			$sql = "SELECT validar_consolidacion('$diabuscado',$turno,'$es') ";
		else
			$sql = "SELECT validar_consolidacion('$diabuscado',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if ($estado[0] == 1)
		    return "CONSOLIDADO";

        	$sql = "
			SELECT 	
				pdd.ch_almacen as almacen,
				pdd.ch_tipo_deposito as tipo,
				pdd.ch_valida as valida,
				pdd.dt_dia as dia,
				pdd.ch_posturno as turno,
				pdd.ch_codigo_trabajador as codtrab,
				TRIM(pdd.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
				to_char(pdd.dt_fecha,'DD/MM/YYYY HH24:MI:SS') as fecha,
				to_char(pdd.ch_fecha_actualizo,'DD/MM/YYYY HH24:MI:SS') as fechaact,
				pdd.ch_numero_correl as seq,
				pdd.ch_numero_documento as num,
				pdd.ch_moneda as moneda,
				pdd.nu_tipo_cambio as cambio,
				CASE
					WHEN pdd.ch_moneda='01' THEN pdd.nu_importe
					ELSE 0
				END as importesoles,
				CASE
					WHEN pdd.ch_moneda='02' THEN pdd.nu_importe
					ELSE 0
				END as importedolares,
				pdd.ch_usuario as usuario,
				pdd.ch_ip as ip
			FROM
				pos_depositos_diarios pdd
				LEFT JOIN pla_ta_trabajadores ptt ON (pdd.ch_codigo_trabajador = ptt.ch_codigo_trabajador)
		        WHERE
				";

			if(empty($almacen))
				$sql .= "pdd.ch_almacen = '" . pg_escape_string($es) . "'";
			else
				$sql .= "pdd.ch_almacen = '" . pg_escape_string($almacen) . "'";

			$sql .="
				AND to_date(to_char(pdd.dt_dia,'dd/mm/yyyy'),'dd/mm/yyyy') = to_date('" . pg_escape_string($dia) . "', 'dd/mm/yyyy') ";

			if ($turno != "TODOS")
				$sql .= " AND pdd.ch_posturno = " . pg_escape_string($turno) . " ";

		$sql .= "ORDER BY  pdd.ch_numero_correl";

		//echo $sql;

		if ($sqlca->query($sql) < 0) return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

			$resultado[$i]['almacen'] = $a[0];
			$resultado[$i]['tipo'] = $a[1];
			$resultado[$i]['valida'] = $a[2];
			$resultado[$i]['dia'] = $a[3];
			$resultado[$i]['turno'] = $a[4];
			$resultado[$i]['codtrab'] = $a[5];
			$resultado[$i]['trabajador'] = $a[6];
			$resultado[$i]['fecha'] = $a[7];
			$resultado[$i]['fechaact'] = $a[8];
			$resultado[$i]['seq'] = $a[9];
			$resultado[$i]['num'] = $a[10];
			$resultado[$i]['moneda'] = $a[11];
			$resultado[$i]['cambio'] = $a[12];
			$resultado[$i]['importesoles'] = $a[13];
			$resultado[$i]['importedolares'] = $a[14];
			$resultado[$i]['usuario'] = $a[15];
			$resultado[$i]['ip'] = $a[16];

		}

		$sql = "COMMIT";
		$sqlca->query($sql);

		return $resultado;

	}

	function obtenerFila($ch_almacen,$dt_dia,$ch_posturno,$ch_codigo_trabajador,$ch_numero_documento, $ch_numero_correl) {
		global $sqlca;

		$sql = "
		SELECT
			pdd.ch_almacen as almacen,
			pdd.ch_tipo_deposito as tipo,
			pdd.ch_valida as valida,
			pdd.dt_dia as dia,
			pdd.ch_posturno as turno,
			pdd.ch_codigo_trabajador as codtrab,
			TRIM(pdd.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
			pdd.dt_fecha as fecha,
			pdd.ch_numero_correl as seq,
			pdd.ch_numero_documento as num,
			pdd.ch_moneda as moneda,
			pdd.nu_tipo_cambio as cambio,
			CASE
				WHEN pdd.ch_moneda='01' THEN pdd.nu_importe
				ELSE 0
			END as importesoles,
			CASE
				WHEN pdd.ch_moneda='02' THEN pdd.nu_importe
				ELSE 0
			END as importedolares,
			pdd.ch_usuario as usuario,
			pdd.ch_ip as ip
		FROM
			pos_depositos_diarios pdd
			LEFT JOIN pla_ta_trabajadores ptt ON (pdd.ch_codigo_trabajador = ptt.ch_codigo_trabajador)
		WHERE
			pdd.ch_almacen = '" . pg_escape_string($ch_almacen) . "'
			AND pdd.dt_dia = to_date('" . pg_escape_string($dt_dia) . "', 'dd/mm/YYYY')
			AND pdd.ch_posturno = '" . pg_escape_string($ch_posturno) . "'
			AND pdd.ch_codigo_trabajador = '" . pg_escape_string($ch_codigo_trabajador) . "'
			AND pdd.ch_numero_documento = '" . pg_escape_string($ch_numero_documento) . "'
			AND pdd.ch_numero_correl = '" . pg_escape_string($ch_numero_correl) . "';
		";

		if ($sqlca->query($sql) < 0) return false;
		$resultado = Array();

		$a = $sqlca->fetchRow();
		$resultado[$i]['almacen'] = $a[0];
		$resultado[$i]['tipo'] = $a[1];
		$resultado[$i]['valida'] = $a[2];
		$resultado[$i]['dia'] = $dt_dia;
		$resultado[$i]['turno'] = $a[4];
		$resultado[$i]['codtrab'] = $a[5];
		$resultado[$i]['trabajador'] = $a[6];
		$resultado[$i]['fecha'] = $a[7];
		$resultado[$i]['seq'] = $a[8];
		$resultado[$i]['num'] = $a[9];
		$resultado[$i]['moneda'] = $a[10];
		$resultado[$i]['cambio'] = $a[11];
		$resultado[$i]['importesoles'] = $a[12];
		$resultado[$i]['importedolares'] = $a[13];
    	$resultado[$i]['usuario'] = $a[14];
    	$resultado[$i]['ip'] = $a[15];

		return $resultado;
	}

	function actualizarFila($ch_almacen,$dt_dia,$ch_posturno,$ch_codigo_trabajador,$ch_numero_documento,$nvalida,$ndia,$nturno,$ncodtrab,$usuario,$ip, $ch_numero_correl) {
		global $sqlca;

		list($dia,$mes,$ano)	= explode("/", $ndia);
		list($dia2,$mes2,$ano2)	= explode("/", $dt_dia);

		//ch_numero_documento 	= '0'||'$ch_numero_documento' Le colocan porque si no su primary key se repetirá, esta mal compuesta la llave primaria :/

		$sql = "
		UPDATE
			pos_depositos_diarios
		SET
			ch_valida 				= '" . pg_escape_string($nvalida) . "',
			dt_dia 					= '" . pg_escape_string($ano."-".$mes."-".$dia) . "',
			ch_posturno 			= '" . pg_escape_string($nturno) . "',
			ch_codigo_trabajador 	= '" . pg_escape_string($ncodtrab) . "',
			ch_usuario 				= '" . pg_escape_string($usuario) . "',
			ch_ip 					= '" . pg_escape_string($ip) . "',
			ch_fecha_actualizo 		= '" . date('Y-m-d H:i:s') . "',
			ch_numero_documento 	= '0" . pg_escape_string($ch_numero_documento) . "'
		WHERE
			ch_almacen 					= '" . pg_escape_string($ch_almacen) . "'
			AND dt_dia 					= '" . pg_escape_string($ano2."-".$mes2."-".$dia2) . "'
			AND ch_posturno 			= '" . pg_escape_string($ch_posturno) . "'
			AND ch_codigo_trabajador 	= '" . pg_escape_string($ch_codigo_trabajador) . "'
			AND ch_numero_documento 	= '" . pg_escape_string($ch_numero_documento) . "'
			AND ch_numero_correl 		= '" . pg_escape_string($ch_numero_correl) . "';
		";
		error_log($sql);

		if ($sqlca->query($sql)==-1) return false;
		return true;
	}

	function obtenerTrabajadores() {
		global $sqlca;
		$sql = "SELECT btrim(ch_codigo_trabajador,' ') AS codigo,(btrim(ch_apellido_paterno,' ') || ' ' || btrim(ch_apellido_materno,' ') || ' ' || btrim(ch_nombre1,' ') || ' ' || btrim(ch_nombre2,' ')) AS nombre FROM pla_ta_trabajadores;";
		if ($sqlca->query($sql) < 0) return false;

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
