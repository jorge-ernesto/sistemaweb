<?php

class StockTurnoModel extends Model {

	function obtieneListaEstaciones() {

		global $sqlca;
	
		$sql = "SELECT
				ch_almacen,
				trim(ch_nombre_almacen)
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1'
			ORDER BY
				ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}
	
		return $result;

    	}


	function Paginacion($pp, $pagina, $desde, $hasta){

		global $sqlca;

		$sql = "SELECT
				to_char(CT.fecha_stock_sistema, 'DD/MM/YYYY') as fecha,
				CT.turno_stock stock,
				to_char(CT.fecha_stock_fisico, 'DD/MM/YYYY hh:mi') sfecha,
				CT.id_tanque tanque,
				A.art_descbreve desces,
				CT.stock_fisico fstock,
				substring(A.art_unidad from 4 for char_length(A.art_unidad)) uni,
				CT.responsable res,
				to_char(C.dt_posz_fecha_cierre, 'DD/MM/YYYY hh:mi') hora,
				CT.id_stock_combustible_turno turno
			FROM
				comb_stock_combustible_turno CT
					LEFT JOIN comb_ta_tanques T ON T.ch_tanque = CT.id_tanque and T.ch_sucursal = CT.id_sucursal
					LEFT JOIN int_articulos A ON T.ch_codigocombustible = A.art_codigo
					LEFT JOIN pos_z_cierres C ON C.nu_posturno = CT.turno_stock and C.dt_posz_fecha_sistema = CT.fecha_stock_sistema";

		if($desde != ''){
		$sql .= "
			WHERE
				fecha_stock_sistema BETWEEN to_date('".$desde."', 'DD/MM/YYYY') AND to_date('".$hasta."', 'DD/MM/YYYY')";
		}

		$sql .= "
			GROUP BY
				CT.fecha_stock_sistema,
				stock,
				sfecha,	
				tanque,
				desces,
				fstock,
				uni,
				res,
				hora,
				turno
			ORDER BY
				CT.fecha_stock_sistema DESC,
				CT.turno_stock DESC";

		$resultado_1 = $sqlca->query($sql);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp,$pagina);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		$sql .= "
			LIMIT " . pg_escape_string($pp) . " ";
		$sql .= "
			OFFSET " . pg_escape_string($paginador->partir());

		echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
    		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['f_sistema'] 	= $a[0];
			$resultado[$i]['turno'] 	= $a[1];
			$resultado[$i]['f_fisico'] 	= $a[2];
			$resultado[$i]['tanque'] 	= $a[3];
			$resultado[$i]['articulo'] 	= $a[4];
			$resultado[$i]['stock'] 	= $a[5];
			$resultado[$i]['unidad'] 	= $a[6];
			$resultado[$i]['responsable'] 	= $a[7];
			$resultado[$i]['f_cierre'] 	= $a[8];
			$resultado[$i]['registroid'] 	= $a[9];
		}
		
		$sql = "COMMIT";
		$sqlca->query($sql);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	} 

	function obtenerEstaciones() {
		global $sqlca;

		$sql = "SELECT
				ch_almacen,
				trim(ch_nombre_almacen)
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1'
			ORDER BY
				ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}
		return $result;
	}

	function obtenerFechas() {
		global $sqlca;

		$sql = "SELECT
				to_char(da_fecha,'DD/MM/YYYY')
			FROM
				pos_aprosys
			WHERE
				ch_posturno != '1'
			ORDER BY
				da_fecha DESC
			LIMIT
				100;";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0];
		}
		return $result;
    	}

	function obtenerTanques() {
		global $sqlca;

		$sql = "SELECT DISTINCT
				a.ch_tanque,
				a.ch_tanque||' - '|| b.ch_nombrebreve||' - '|| substring(ar.art_unidad from 4 for char_length(ar.art_unidad)),
				a.dt_fechaultimamedida
			FROM
			    	comb_ta_tanques a,
			    	comb_ta_combustibles b,
			    	comb_ta_tanques c,
			    	int_articulos ar
			WHERE
				a.ch_codigocombustible=b.ch_codigocombustible
			    	AND a.ch_tanque=c.ch_tanque
			    	AND c.ch_codigocombustible=b.ch_codigocombustible
			    	AND ar.art_codigo = b.ch_codigocombustible			
			ORDER BY 
				a.ch_tanque,a.dt_fechaultimamedida;";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		} 
		return $result;
    	}

	function agregar($sucursal,$fecha_sistema,$turno, $fecha_inventario, $hora_inventario, $tanque, $stock, $responsable, $ip, $usuario) {
		global $sqlca;

		$sql =	"	INSERT INTO
					comb_stock_combustible_turno
				(
					id_sucursal,
					fecha_stock_sistema,
					turno_stock,
					fecha_stock_fisico,
					id_tanque,
					stock_fisico,
					responsable,
					auditoria_ip,
					auditoria_usuario
				) VALUES (
					'$sucursal',
					to_date('$fecha_sistema','DD/MM/YYYY'),
					$turno,
					to_timestamp(('$fecha_inventario $hora_inventario'), 'DD/MM/YYYY hh:mi'),
					'$tanque', 
					$stock, 
					'$responsable', 
					'$ip', 
					'$usuario'
				);";

		echo "\n inserta".$sql.'>>>';

		if ($sqlca->query($sql)<0)
			return FALSE;
		return TRUE;
	}

	function modificar($regid, $stock, $fecha, $hora, $responsable) {
		global $sqlca; 

		$fechaf = substr($fecha,6,4).'-'.substr($fecha,3,2).'-'.substr($fecha,0,2).' '.trim($hora).':00';

		$sql = " UPDATE
				comb_stock_combustible_turno
			SET
				stock_fisico=".$stock." 
			WHERE
				id_stock_combustible_turno = ".trim($regid).";";

		if ($sqlca->query($sql)<0)
			return FALSE;
			
		$sql =" UPDATE
				comb_stock_combustible_turno
			SET
				fecha_stock_fisico = '".$fechaf."' 
			WHERE
				id_stock_combustible_turno = ".trim($regid).";";

		if ($sqlca->query($sql)<0)
			return FALSE;
			
		$sql =" UPDATE
				comb_stock_combustible_turno
			SET
				responsable = '".$responsable."'
			WHERE
				id_stock_combustible_turno = ".trim($regid).";";

		if ($sqlca->query($sql)<0)
			return FALSE;
			
		return TRUE;
	}
	
	function recuperarRegistroArray($registroid, $responsable, $articulo, $unidad){
    		global $sqlca;
		
		$registro = array();
		$query = "SELECT
				id_sucursal, 
				substring(fecha_stock_sistema::text from 9 for 2) || '/' || substring(fecha_stock_sistema::text from 6 for 2) || '/' || substring(fecha_stock_sistema::text from 1 for 4),
				turno_stock,
				substring(fecha_stock_fisico::text from 9 for 2) || '/' || substring(fecha_stock_fisico::text from 6 for 2) || '/' || substring(fecha_stock_fisico::text from 1 for 4)||substring(fecha_stock_fisico::text from 12 for 5),
				id_tanque,
				stock_fisico,
				'".$responsable."',
				id_stock_combustible_turno,
				'".trim($articulo)."',
				'".trim($unidad)."'
			FROM
				comb_stock_combustible_turno 
			WHERE
				id_stock_combustible_turno = ".$registroid.";";

		$sqlca->query($query);

		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		} 

	    	return $registro;
	}	
}
