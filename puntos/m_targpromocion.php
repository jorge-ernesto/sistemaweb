<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

class TargpromocionModel {

	function validarDNI($dni) {
		global $sqlca;
		
		$sql="	SELECT 
				count(*)
			FROM 
				prom_ta_cuentas
			WHERE
				ch_cuenta_dni = '" .pg_escape_string($dni). "'";
		$res = $sqlca->query($sql);
		$arr = $sqlca->fetchRow();
		return $arr[0];
	}

	function ingresarCuenta($numero,$nombres,$apellidos,$vip,$fechanacimiento,$dni,$ruc,$direccion,$telefono,$telefono2,$email,$tipocuenta,$puntos,$usuario,$almacen) {
		global $sqlca;

		list($dia,$mes,$anio) = explode('/',pg_escape_string($fechanacimiento));
		$fn = $anio.'-'.$mes.'-'.$dia;

		$queryInicial = "SELECT 
					nu_cuenta_numero 
				FROM 
					prom_ta_cuentas 
				WHERE 
					nu_cuenta_numero = '".$numero."' ";

		if($sqlca->query($queryInicial) > 0) {
			return '0';
		} else {		
			$query = "
				SELECT 
					trim(par_valor) 
				FROM 
					int_parametros 
				WHERE 
					par_nombre='prom_venc_puntos'";

			$result = $sqlca->query($query);
			$row = $sqlca->fetchRow();
			$vencimiento = $row[0].' days';
			
			$query ="INSERT INTO prom_ta_cuentas(	nu_cuenta_numero,
								ch_cuenta_nombres,
								ch_cuenta_apellidos,
								ch_cuenta_vip,
								dt_fecha_nacimiento,
								ch_cuenta_dni,
								ch_cuenta_ruc,
								ch_cuenta_direccion,
								ch_cuenta_telefono1,
								ch_cuenta_telefono2,
								ch_cuenta_email,
								id_tipo_cuenta,
								nu_cuenta_puntos,
								ch_usuario,
								dt_fecha_vencimiento,
								ch_sucursal) 
				VALUES (
					'".pg_escape_string($numero)."',
					'".pg_escape_string($nombres)."',
					'".pg_escape_string($apellidos)."',
					'".pg_escape_string($vip)."',
					'".pg_escape_string($fn)."',
					'".pg_escape_string($dni)."',
					'".pg_escape_string($ruc)."',
					'".pg_escape_string($direccion)."',
					'".pg_escape_string($telefono)."',
					'".pg_escape_string($telefono2)."',
					'".pg_escape_string($email)."',
					'".pg_escape_string($tipocuenta)."',
					".pg_escape_string($puntos).",
					'".pg_escape_string($usuario)."',
					now()+interval '" . "30 days" . "',
					'".pg_escape_string($almacen)."')";	
		
				$result = $sqlca->query($query);
				return '1';
		}
	}

	function actualizarCuenta($idcuenta,$numero,$nombres,$apellidos,$vip,$fechanacimiento,$dni,$ruc,$direccion,$telefono,$telefono2,$email,$tipocuenta,$estado,$usuario) {
		global $sqlca;

		list($dia,$mes,$anio) = explode('/',pg_escape_string($fechanacimiento));
		$fn = $anio.'-'.$mes.'-'.$dia;

		$query ="UPDATE prom_ta_cuentas SET nu_cuenta_numero=".pg_escape_string($numero).",".
						  "ch_cuenta_nombres='".pg_escape_string($nombres)."',".
						  "ch_cuenta_apellidos='".pg_escape_string($apellidos)."',".
						  "ch_cuenta_vip='".pg_escape_string($vip)."',".
						  "dt_fecha_nacimiento='".pg_escape_string($fn)."',".
						  "ch_cuenta_dni='".pg_escape_string($dni)."',".
						  "ch_cuenta_ruc='".pg_escape_string($ruc)."',".
						  "ch_cuenta_direccion='".pg_escape_string($direccion)."',".
						  "ch_cuenta_telefono1='".pg_escape_string($telefono)."',".
						  "ch_cuenta_telefono2='".pg_escape_string($telefono2)."',".
						  "ch_cuenta_email='".pg_escape_string($email)."',".
						  "id_tipo_cuenta=".pg_escape_string($tipocuenta).",".
						  "dt_fecha_actualiza=now(),".
						  "isactive=".$estado.",".
						  "ch_usuario='".pg_escape_string($usuario)."' ".
			" WHERE id_cuenta= ".pg_escape_string($idcuenta);

		$result = $sqlca->query($query);
		if ($result < 0) {
	      		return '0';
   		} else {
   			return '1';
   		}	
	}

	function obtenerCuenta($campovalor,$tipocampo)  {
		global $sqlca; 

		$registro = array();
		$campo = '';	

		if($tipocampo == '1') { 
			$campo = 'id_cuenta='.pg_escape_string($campovalor)." ";
		} else {
			$campo = 'nu_cuenta_numero='.pg_escape_string($campovalor)." ";	
		}

		$query = "SELECT 
				id_cuenta,
				nu_cuenta_numero,
				ch_cuenta_nombres,
				ch_cuenta_apellidos,
				ch_cuenta_vip,
				to_char(dt_fecha_nacimiento,'dd/mm/yyyy') as dt_fecha_nacimiento,
				ch_cuenta_dni,
				ch_cuenta_ruc,
				ch_cuenta_direccion,
				ch_cuenta_telefono1,
				ch_cuenta_telefono2,
				ch_cuenta_email,
				id_tipo_cuenta,
				nu_cuenta_puntos,
				ch_usuario,
				dt_fecha_creacion,
				dt_fecha_vencimiento,
				isactive
			FROM 
				prom_ta_cuentas 
			WHERE 
				" . $campo . " 
			ORDER BY 
				nu_cuenta_numero ASC ";

		$sqlca->query($query);
		while($reg = $sqlca->fetchRow()) {
			$registro = $reg;			
		}

		return $registro;		
	}
	
	function modificarTarjeta($idtarjeta, $numero, $descripcion, $placa, $fechaven, $flatcuenta, $flattitular, $usuario, $motivocambio, $id_motivo_duplicada, $estacion) {
		global $sqlca;

		$query = "SELECT
				nu_tarjeta_numero
			FROM
				prom_ta_tarjetas
			WHERE
				id_tarjeta = $idtarjeta;";

		$result = $sqlca->query($query);
		if ($result < 0)
			return '0';

		$reg = $sqlca->fetchRow();

		if ($reg[0] != $numero) {
			$query = "INSERT INTO
					prom_ta_tarjeta_duplicada
				 	(
						fecha,
						id_tarjeta,
						nu_tarjeta_numero_anterior,
						nu_tarjeta_numero_duplicado,
						motivo,
						ch_usuario,
						ch_sucursal,
						id_motivo_duplicada
					) VALUES (
						now(),
						$idtarjeta,
						{$reg[0]},
						$numero,
						'" . pg_escape_string($motivocambio) . "',
						'".pg_escape_string($usuario)."',
						'".pg_escape_string($estacion)."',
						{$id_motivo_duplicada}
					);";
			$result = $sqlca->query($query);
			if ($result < 0)
				return '0';
		}
		
		$query = "UPDATE 
				prom_ta_tarjetas 
			SET 
				nu_tarjeta_numero ='".pg_escape_string($numero).
				"',ch_tarjeta_descripcion ='".pg_escape_string($descripcion).
				"',ch_tarjeta_placa ='".pg_escape_string($placa).
				"',dt_tarjeta_vencimiento= to_date('".pg_escape_string($fechaven).
				"','dd/mm/yyyy'),nu_cuenta_numero=".pg_escape_string($flatcuenta).
				",ch_tarjeta_titular='".pg_escape_string($flattitular).
				"',dt_fecha_actualiza=now()".
				",ch_usuario='".pg_escape_string($usuario)."' ".
				" where id_tarjeta =".$idtarjeta." ";

		$result = $sqlca->query($query);

		if ($result < 0) {
			return '0';
   		} else {
   			return '1';
   		}
	}

	function insertarTarjeta($idcuenta,$numero,$descripcion,$placa,$fechaven,$puntos,$flatcuenta,$flattitular,$usuario) {
		global $sqlca;

		$queryInicial = "SELECT nu_tarjeta_numero FROM prom_ta_tarjetas WHERE nu_tarjeta_numero =".$numero."";

		if($sqlca->query($queryInicial) > 0) {
			return '0';
		} else {
			$query = "INSERT INTO
					prom_ta_tarjetas 
					(		id_cuenta,
							nu_tarjeta_numero,
							ch_tarjeta_descripcion,
							ch_tarjeta_placa,
							dt_tarjeta_vencimiento,
							nu_tarjeta_puntos,
							nu_cuenta_numero,
							ch_tarjeta_titular,
							ch_usuario
					) values (".	
							pg_escape_string($idcuenta).",".
							pg_escape_string($numero).",'".
							pg_escape_string($descripcion)."','".
							pg_escape_string($placa)."',to_date('".pg_escape_string($fechaven)."','dd/mm/yyyy'),".
							pg_escape_string($puntos).",'".
							pg_escape_string($flatcuenta)."','".
							pg_escape_string($flattitular)."','".
							pg_escape_string($usuario)."')";

			$result = $sqlca->query($query);
			return '1';
		}	
	}

	function eliminarTarjeta($idcuenta,$idtarjeta) {
		global $sqlca;

		$query  = "Delete from prom_ta_tarjetas where id_tarjeta=".pg_escape_string($idtarjeta)." ";
		$result = $sqlca->query($query);

		return '1';
	}

	function eliminarCuenta($idcuenta) {
		global $sqlca;

		$query = "Delete from prom_ta_cuentas where id_cuenta=".pg_escape_string($idcuenta)." ";
		$result= $sqlca->query($query);

		return '1';
	}
		
	public static function listarTarjetas($filtro,$tipo) {
		//Siempre ordenado pro fecha de modificaciòn y 10 registros

		global $sqlca;

		$cond = "";
		if(!empty($filtro)) {
			if($tipo == "1") {
				$cond = " WHERE id_cuenta = ".pg_escape_string($filtro)." ";
			}else if($tipo == "2") {
				$cond = " WHERE id_tarjeta = ".pg_escape_string($filtro)." ";
			}else if($tipo == "3") {
				$cond = " WHERE nu_tarjeta_numero = ".pg_escape_string($filtro)." ";
			}else{
				$cond = " WHERE ch_tarjeta_descripcion like '".pg_escape_string($filtro)."%'";
			}		
		}

		$cond .= " ORDER by dt_fecha_actualiza DESC LIMIT 10;";

		$query="SELECT 	id_tarjeta,
				id_cuenta,
				nu_tarjeta_numero,
				ch_tarjeta_descripcion,
				ch_tarjeta_placa,
				to_char(dt_tarjeta_creacion,'dd/mm/yyyy') as dt_tarjeta_creacion,
				to_char(dt_tarjeta_vencimiento,'dd/mm/yyyy') as dt_tarjeta_vencimiento,
				nu_tarjeta_puntos,
				nu_cuenta_numero,
				ch_tarjeta_titular,
				ch_usuario,
				dt_fecha_actualiza 
			FROM
				prom_ta_tarjetas".$cond;

		$listado   = array();
		$resultado = $sqlca->query($query);

		while($reg = $sqlca->fetchRow()) {
			$listado['datostarjeta'][] = $reg;
		}

		return $listado;
	}
	
	function listarTiposCuenta($filtro,$tipo) {
		global $sqlca;

		$cond = " ";
		if(!empty($filtro)) {
			if($tipo == "1"){
				$cond = " WHERE id_tipo_cuenta = ".pg_escape_string($filtro)." ";
			}else{
				$cond = " WHERE ch_tipo_descripcion like '".pg_escape_string($filtro)."%'";
			}		
		}

		$query = "SELECT id_tipo_cuenta, ch_tipo_descripcion FROM prom_ta_tipo_cuentas".$cond;		
		$listado = array();
		$resultado= $sqlca->query($query);
		while($reg = $sqlca->fetchRow()){
			$listado['datostipocuenta'][] = $reg;
		}

		return $listado;
	}

	function listarMotivoDuplicada() {
		global $sqlca;

		$query = "SELECT id_motivo_duplicada,nombre FROM prom_ta_motivo_duplicada";		
		$listado   = array();
		$resultado = $sqlca->query($query);
		while($reg = $sqlca->fetchRow()) {
			$listado[$reg[0]] = $reg[1];
		}

		return $listado;
	}

	function tmListado($filtro,$tipo,$pp, $pagina, $almacen) {
		global $sqlca;

		$cond = '';

		$ordby = " t.nu_tarjeta_numero ASC ";
		$fromc = " prom_ta_tarjetas t LEFT JOIN prom_ta_cuentas c ON (t.id_cuenta  = c.id_cuenta) LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen) ";

		$wherec = "";

		if (!empty($filtro)) {
			$tipo = strtoupper(trim($tipo));
			if ($tipo =='D') {
				$wherec = " c.ch_cuenta_dni = '".pg_escape_string($filtro)."' ";
				$ordby = " c.ch_cuenta_dni ASC ";
				$fromc = " prom_ta_cuentas c LEFT JOIN prom_ta_tarjetas t ON (t.id_cuenta  = c.id_cuenta) LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen) "; //TABLAS
			} else if ($tipo =='C') {
				$wherec = " c.nu_cuenta_numero = ".pg_escape_string($filtro)." ";
				$ordby = " c.nu_cuenta_numero ASC ";
				$fromc = " prom_ta_cuentas c LEFT JOIN prom_ta_tarjetas t ON (t.id_cuenta  = c.id_cuenta) LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen) "; //TABLAS
			} else if ($tipo =='T') {
				$wherec = " t.nu_tarjeta_numero = ".pg_escape_string($filtro)." ";
				$ordby = " t.nu_tarjeta_numero ASC ";
			} else if ($tipo =='F') {
				list($dia,$mes,$anio) = explode('/',pg_escape_string($filtro));
				$fn = $anio.'-'.$mes.'-'.$dia;
				$wherec = " c.dt_fecha_creacion = '$fn' ";
				$ordby = " c.dt_fecha_creacion DESC ";
				$fromc = " prom_ta_cuentas c LEFT JOIN prom_ta_tarjetas t ON (t.id_cuenta  = c.id_cuenta) LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen) "; //TABLAS
			} else {
				$wherec = " c.ch_cuenta_nombres LIKE '%".pg_escape_string($filtro)."%' OR c.ch_cuenta_apellidos LIKE '%".pg_escape_string($filtro)."%' ";
				$ordby = " c.ch_cuenta_nombres,c.ch_cuenta_apellidos ";
				$fromc = " prom_ta_cuentas c LEFT JOIN prom_ta_tarjetas t ON (t.id_cuenta  = c.id_cuenta) LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen) "; //TABLAS
			}
		}

		if ($almacen != "TODOS") {
			if ($wherec == "")
				$wherec = " c.ch_sucursal = '$almacen' ";
			else
				$wherec = " AND c.ch_sucursal = '$almacen' ";
		}

		if ($wherec == "")
			$fwhere = "";
		else
			$fwhere = " WHERE {$wherec} ";

		$query = "SELECT 
				c.nu_cuenta_numero,
				c.ch_cuenta_nombres || ' ' || c.ch_cuenta_apellidos as nombre_cuenta,
				t.nu_tarjeta_numero,
				t.ch_tarjeta_descripcion,
				t.ch_tarjeta_placa,
				c.nu_cuenta_puntos,
				t.nu_tarjeta_puntos,
				t.id_tarjeta,
				c.id_cuenta,
				to_char(t.dt_tarjeta_creacion,'dd/mm/yyyy') as dt_tarjeta_creacion,
				to_char(t.dt_tarjeta_vencimiento,'dd/mm/yyyy') as dt_tarjeta_vencimiento,
				t.ch_tarjeta_titular,
				c.ch_cuenta_dni,
				to_char(c.dt_fecha_creacion, 'dd/mm/yyyy') as dt_fecha_creacion,
				alma.ch_nombre_breve_almacen as ch_sucursal,
				c.ch_usuario
			FROM {$fromc} {$fwhere} ORDER BY {$ordby} ";

/*		if($almacen == "TODOS" && empty($filtro)){
			$cond = "ORDER BY t.nu_tarjeta_numero ASC";
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
				$cond =" ORDER BY t.nu_tarjeta_puntos DESC ";
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

		$query = "SELECT 
				c.nu_cuenta_numero,
				c.ch_cuenta_nombres || ' ' || c.ch_cuenta_apellidos as nombre_cuenta,
				t.nu_tarjeta_numero,
				t.ch_tarjeta_descripcion,
				t.ch_tarjeta_placa,
				c.nu_cuenta_puntos,
				t.nu_tarjeta_puntos,
				t.id_tarjeta,
				c.id_cuenta,
				to_char(t.dt_tarjeta_creacion,'dd/mm/yyyy') as dt_tarjeta_creacion,
				to_char(t.dt_tarjeta_vencimiento,'dd/mm/yyyy') as dt_tarjeta_vencimiento,
				t.ch_tarjeta_titular,
				c.ch_cuenta_dni,
				to_char(c.dt_fecha_creacion, 'dd/mm/yyyy') as dt_fecha_creacion,
				alma.ch_nombre_breve_almacen as ch_sucursal,
				c.ch_usuario
			FROM 
				prom_ta_tarjetas t 
				LEFT JOIN prom_ta_cuentas c ON (t.id_cuenta  = c.id_cuenta)
				LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen) ".$cond;*/
	
		error_log("tmListado");
		error_log(json_encode($query));
		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		if($pp && $pagina){
			$paginador = new paginador($numrows,$pp, $pagina);
		} else {
			$paginador = new paginador($numrows,100,0);
		}
			
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();
		
		if ($pp > 0)
			$query .= " LIMIT " . pg_escape_string($pp) . " ";
		
		if ($pagina > 0)
 			$query .= " OFFSET " . pg_escape_string($paginador->partir());

		if ($sqlca->query($query) <= 0) {
			return $sqlca->get_error();
		}
		
		$listado[] = array();
		while($reg = $sqlca->fetchRow()) {
			$listado['datos'][] = $reg;
		}			
		$listado['paginacion'] = $listado2;
		
		return $listado;
	}

	function listarCambiosTarjetas($numerotarjeta,$fecha1,$fecha2) {
		global $sqlca;

		if (!settype($numerotarjeta,"int"))
			$numerotarjeta = 0;

		$sql =	"
		SELECT
			td.id_tarjeta_duplicada,
			to_char(td.fecha,'DD/MM/YYYY') AS fecha,
			td.id_tarjeta,
			td.nu_tarjeta_numero_anterior,
			td.nu_tarjeta_numero_duplicado,
			td.motivo,
			td.ch_usuario,
			td.fecha_actualizacion,
			td.ch_sucursal,
			md.nombre
		FROM
			prom_ta_tarjeta_duplicada td
			LEFT JOIN prom_ta_motivo_duplicada md ON td.id_motivo_duplicada = md.id_motivo_duplicada
		WHERE
			1 = 1
			" . (($numerotarjeta==0)?"":"AND (td.nu_tarjeta_numero_anterior=$numerotarjeta OR td.nu_tarjeta_numero_duplicado=$numerotarjeta)") . "
			" . (($fecha1=="" || $fecha2=="")?"":"AND td.fecha BETWEEN to_date('$fecha1','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')") . "
		ORDER BY
			8;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['id'] 			= $a[0];
			$resultado[$i]['fecha'] 		= $a[1];
			$resultado[$i]['tarjeta'] 		= $a[2];
			$resultado[$i]['numero_anterior'] 	= $a[3];
			$resultado[$i]['numero_duplicado'] 	= $a[4];
			$resultado[$i]['motivo'] 		= $a[5];
			$resultado[$i]['motivo_duplicada'] 	= $a[9];
			$resultado[$i]['usuario'] 		= $a[6];
			$resultado[$i]['fecha_actualizacion'] 	= $a[7];
			$resultado[$i]['sucursal'] 		= $a[8];
		}

		return $resultado;
	}
}
