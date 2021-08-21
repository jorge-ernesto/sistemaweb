<?php

class TargpromocionModel extends Model {
	function llamadaRemota($procedimiento, $parametros) {
		global $sqlca;
			
		$sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$ip  = $row[0];
		
		$url = "http://".$ip."/sistemaweb/puntos/index.php?action=tarjetascuentas&proc=" . urlencode($procedimiento);

		foreach($parametros as $parametro=>$valor) {
			$url .= "&" . $parametro . "=" . urlencode($valor);
		}
		
		$fh = fopen($url,"rb");
		if ($fh===false)
			return false;
		
		$res = '';
	
		while (!feof($fh)) {
			$res .= fread($fh, 8192);
		}
	
		fclose($fh);

		return unserialize($res);
	}
	
	function ingresarCuenta($numero,$nombres,$apellidos,$vip,$fechanacimiento,$dni,$ruc,$direccion,$telefono,$telefono2,$email,$tipocuenta,$puntos,$usuario,$almacen) {
	    $params = array(
		"numero"		=> $numero,
		"nombres"		=> $nombres,
		"apellidos"		=> $apellidos,
		"vip"			=> $vip,
		"fechanacimiento"	=> $fechanacimiento,
		"dni"			=> $dni,
		"ruc"			=> $ruc,
		"direccion"		=> $direccion,
		"telefono"		=> $telefono,
		"telefono2"		=> $telefono2,
		"email"			=> $email,
		"tipocuenta" 		=> $tipocuenta,
		"puntos"		=> $puntos,
		"usuario"		=> $usuario,
		"almacen"		=> $almacen
	    );
	
	    return TargpromocionModel::llamadaRemota("ingresarCuenta", $params);
	}

	function actualizarCuenta($idcuenta,$numero,$nombres,$apellidos,$vip,$fechanacimiento,$dni,$ruc,$direccion,$telefono,$telefono2,$email,$tipocuenta,$estado,$usuario){
		$params = array(
			"idcuenta"		=> $idcuenta,
			"numero"		=> $numero,
			"nombres"		=> $nombres,
			"apellidos"		=> $apellidos,
			"vip"			=> $vip,
			"fechanacimiento"	=> $fechanacimiento,
			"dni"			=> $dni,
			"ruc"			=> $ruc,
			"direccion"		=> $direccion,
			"telefono"		=> $telefono,
			"telefono2"		=> $telefono2,
			"email"			=> $email,
			"tipocuenta" 		=> $tipocuenta,
			"estado" 		=> $estado,
			"usuario"		=> $usuario
	    		);

	    	return TargpromocionModel::llamadaRemota("actualizarCuenta", $params);
	}

	function obtenerCuenta($campovalor,$tipocampo) {
		$params = array(
			"campovalor"	=> $campovalor,
			"tipocampo"	=> $tipocampo
	    		);

	    	return TargpromocionModel::llamadaRemota("obtenerCuenta", $params);
	}
	
	function modificarTarjeta($idtarjeta,$numero,$descripcion,$placa,$fechaven,$flatcuenta,$flattitular,$usuario,$motivocambio,$estacion) {
		$params = array(
				"idtarjeta"	=> $idtarjeta,
				"numero"	=> $numero,
				"descripcion"	=> $descripcion,
				"placa"		=> $placa,
				"fechaven"	=> $fechaven,
				"flatcuenta"	=> $flatcuenta,
				"flattitular"	=> $flattitular,
				"usuario"	=> $usuario,
				"motivocambio"	=> $motivocambio,
				"estacion"	=> $estacion
			    );
	    
	    	return TargpromocionModel::llamadaRemota("modificarTarjeta", $params);
	}
	
	function insertarTarjeta($idcuenta,$numero,$descripcion,$placa,$fechaven,$puntos,$flatcuenta,$flattitular,$usuario) {
		$params = array(
				"idcuenta"	=> $idcuenta,
				"numero"	=> $numero,
				"descripcion"	=> $descripcion,
				"placa"		=> $placa,
				"fechaven"	=> $fechaven,
				"puntos"	=> $puntos,
				"flatcuenta"	=> $flatcuenta,
				"flattitular"	=> $flattitular,
				"usuario"	=> $usuario
			    );
	   
		return TargpromocionModel::llamadaRemota("insertarTarjeta", $params);
	}

	function eliminarTarjeta($idcuenta,$idtarjeta) {
		$params = array(
				"idcuenta"	=> $idcuenta,
				"idtarjeta"	=> $idtarjeta
			    );
	    
	    	return TargpromocionModel::llamadaRemota("eliminarTarjeta", $params);
	}
	
	function eliminarCuenta($idcuenta) {
	    	$params = array( "idcuenta"	=> $idcuenta );
	    
	    	return TargpromocionModel::llamadaRemota("eliminarCuenta", $params);
	}
		
	function listarTarjetas($filtro,$tipo) {
	$params = array(
			"filtro"	=> $filtro,
			"tipo"		=> $tipo
		    );
	    
	    	return TargpromocionModel::llamadaRemota("listarTarjetas", $params);
	}

	function listarTiposCuenta($filtro,$tipo) {
	    	$params = array(
				"filtro"	=> $filtro,
				"tipo"		=> $tipo
			    );
	   
	    	return TargpromocionModel::llamadaRemota("listarTiposCuenta", $params);
	}

	function listarMotivoDuplicada() {
	    	return TargpromocionModel::llamadaRemota("listarMotivoDuplicada", Array());
	}
	
	function tmListado($filtro, $tipo, $pp, $pagina, $almacen) {
	    	$params = array(
				"filtro"	=> $filtro,
				"tipo"		=> $tipo,
				"pp"		=> $pp,
				"pagina"	=> $pagina,
				"almacen"	=> $almacen
			    );
	    	
	    	return TargpromocionModel::llamadaRemota("tmListado", $params);
	}

	function listarCambiosTarjetas($numerotarjeta,$fecha1,$fecha2) {
		$params = Array(
			"numerotarjeta"	=>	$numerotarjeta,
			"fecha1"	=>	$fecha1,
			"fecha2"	=>	$fecha2
			);

		return TargpromocionModel::llamadaRemota("listarCambiosTarjetas",$params);
	}

	function generarReporte($filtro, $tipo, $almacen) {
		global $sqlca;	

		$cond = '';

		if($almacen == "TODOS" && empty($filtro)){
			$cond = "ORDER BY t.nu_tarjeta_puntos DESC";
		} else if($almacen != "TODOS" && empty($filtro)){
			$cond = "WHERE c.ch_sucursal = '$almacen' ORDER BY t.nu_tarjeta_numero ASC";
		} else if($almacen == "TODOS" && !empty($filtro)){
			if(strtoupper(trim($tipo)) =='DEFAULT') {
				$cond =" ORDER BY t.nu_tarjeta_numero ASC ";
			} else if(strtoupper(trim($tipo)) =='D') {
				$cond = " WHERE c.ch_cuenta_dni = '".pg_escape_string($filtro)."' ORDER BY c.ch_cuenta_dni ASC";  
			} else if(strtoupper(trim($tipo)) =='C') {
				$cond = " WHERE c.nu_cuenta_numero = ".pg_escape_string($filtro)." ORDER BY c.nu_cuenta_numero ASC";  
			} else if(strtoupper(trim($tipo)) =='T') {
				$cond = " WHERE t.nu_tarjeta_numero = ".pg_escape_string($filtro)." ORDER BY t.nu_tarjeta_numero ASC"; 
			} else if(strtoupper(trim($tipo)) =='F') {
				list($dia,$mes,$anio) = explode('/',pg_escape_string($filtro));
				$fn = $anio.'-'.$mes.'-'.$dia;
				$cond = " WHERE c.dt_fecha_creacion = '$fn' ORDER BY c.dt_fecha_creacion DESC"; 
			} else {
				$cond = " WHERE c.ch_cuenta_nombres LIKE '".pg_escape_string($filtro)."%' OR c.ch_cuenta_apellidos LIKE '".pg_escape_string($filtro)."%' ORDER BY c.ch_cuenta_nombres,c.ch_cuenta_apellidos ";			
			}
		} else if($almacen != "TODOS" && !empty($filtro)){
			if(strtoupper(trim($tipo)) =='DEFAULT') {
				$cond =" ORDER BY t.nu_tarjeta_numero ASC ";
			} else if(strtoupper(trim($tipo)) =='D') {
				$cond = " WHERE c.ch_cuenta_dni = '".pg_escape_string($filtro)."' AND c.ch_sucursal = '$almacen' ORDER BY c.ch_cuenta_dni ASC";  
			} else if(strtoupper(trim($tipo)) =='C') {
				$cond = " WHERE c.nu_cuenta_numero = ".pg_escape_string($filtro)." AND c.ch_sucursal = '$almacen' ORDER BY c.nu_cuenta_numero ASC";  
			} else if(strtoupper(trim($tipo)) =='T') {
				$cond = " WHERE t.nu_tarjeta_numero = ".pg_escape_string($filtro)." AND c.ch_sucursal = '$almacen' ORDER BY t.nu_tarjeta_numero ASC"; 
			} else if(strtoupper(trim($tipo)) =='F') {
				list($dia,$mes,$anio) = explode('/',pg_escape_string($filtro));
				$fn = $anio.'-'.$mes.'-'.$dia;
				$cond = " WHERE c.dt_fecha_creacion = '$fn' AND c.ch_sucursal = '$almacen' ORDER BY c.dt_fecha_creacion DESC"; 
			} else {
				$cond = " WHERE c.ch_cuenta_nombres LIKE '".pg_escape_string($filtro)."%' OR c.ch_cuenta_apellidos LIKE '".pg_escape_string($filtro)."%' AND c.ch_sucursal = '$almacen' ORDER BY c.ch_cuenta_nombres,c.ch_cuenta_apellidos ";			
			}

		}

		$sql = "CREATE TEMPORARY TABLE tmpreporteCT AS
				SELECT
					to_char(t.dt_tarjeta_creacion,'YYYY-MM-DD') as fcreacion,
					c.nu_cuenta_numero as numero_cuenta,
					c.ch_cuenta_nombres || ' ' || c.ch_cuenta_apellidos as titular_cuenta,
					c.nu_cuenta_puntos,
					t.nu_tarjeta_numero as numero_tarjeta,
					t.ch_tarjeta_descripcion as nombre_tarjeta,
					t.ch_tarjeta_placa as placa,
					c.ch_cuenta_direccion as direccion,
					c.ch_cuenta_telefono1 as telefono,
					c.ch_cuenta_dni as dni,
					c.ch_cuenta_email as email,
					c.dt_fecha_nacimiento as f_nacimiento,
					c.id_tipo_cuenta as tipo_cuenta,
					t.nu_tarjeta_puntos as puntos_tarjeta
				FROM
					prom_ta_tarjetas t 
					INNER JOIN prom_ta_cuentas c ON (t.id_cuenta  = c.id_cuenta)
				".$cond;

		$sqlca->query($sql);

		$sql2 = "SELECT * FROM tmpreporteCT";

    	if ($sqlca->query($sql2) < 0)
			return false;

		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$fecha_creacion 	= $a[0];
			$nu_cuenta_numero 	= $a[1];
			$ch_cuenta_nombres 	= $a[2];
			$puntos_cuenta 		= $a[3];
			$numero_tarjeta 	= $a[4];
			$nombre_tarjeta 	= $a[5];
			$placa 				= $a[6];
			$direccion 			= $a[7];
			$telefono 			= $a[8];
			$dni 				= $a[9];
			$email 				= $a[10];
			$f_nacimiento 		= $a[11];
			$tipo_cuenta 		= $a[12];
			$puntos_tarjeta 	= $a[13];
			
			$result[$i]['fecha_creacion'] 	= $fecha_creacion;	
			$result[$i]['numero_tarjeta'] 	= $numero_tarjeta;
			$result[$i]['nombre_tarjeta'] 	= $nombre_tarjeta;
			$result[$i]['placa'] 			= $placa;
			$result[$i]['direccion'] 		= $direccion;
			$result[$i]['telefono'] 		= $telefono;
			$result[$i]['dni'] 				= $dni;
			$result[$i]['puntos_cuenta'] 	= $puntos_cuenta;
			$result[$i]['email'] 			= $email;
			$result[$i]['f_nacimiento'] 	= $f_nacimiento;
			$result[$i]['tipo_cuenta'] 		= $tipo_cuenta;
			$result[$i]['puntos_tarjeta'] 	= $puntos_tarjeta;
			$result[$i]['nu_cuenta_numero'] = $nu_cuenta_numero;
			$result[$i]['ch_cuenta_nombres'] = $ch_cuenta_nombres;
		}
		return $result;
	}

	function obtenerAlmacenes() {
        	global $sqlca;

		$sql = "
			SELECT
				ch_almacen,
				ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1'
			ORDER BY
				ch_almacen;
			";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}
}
