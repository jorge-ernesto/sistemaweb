<?php

class CierresZModel extends Model {
	function busqueda($fecha_del,$fecha_al,$almacen,$caja, $trabajador) {
		global $sqlca;

		$sql = " SELECT 
						dt_posz_fecha_sistema AS fecha_sistema, 
						nu_posturno AS turno, 
						ch_posz_pos AS numero_caja, 
						nu_posz_z_serie AS serie_registradora, 
						nu_posz_z_numero AS numero_z, 
						to_char(dt_posz_fecha_apertura,'dd-mm-yyyy HH24:MI:SS') AS fecha_hora_apertura, 
						to_char(dt_posz_fecha_cierre,'dd-mm-yyyy HH24:MI:SS') AS fecha_hora_cierre, 
						nu_posz_tran_inicial AS numero_tiket_inicial, 
						nu_posz_tran_final AS numero_tiket_final, 
						nu_posz_b_transas AS numero_boletas, 
						nu_posz_b_total AS importe_total_boletas, 
						nu_posz_b_impuesto AS impuesto_total_boletas, 
						nu_posz_f_transas AS numero_facturas, 
						nu_posz_f_total AS importe_total_facturas, 
						nu_posz_f_impuesto AS impuesto_total_facturas, 
						nu_posz_t_transas AS numero_tikets, 
						nu_posz_t_total AS importe_total_tikets, 
						nu_posz_t_impuesto AS impuesto_total_tikets, 
						nu_posz_tipo_cambio AS tipo_cambio, 
						ch_sucursal AS sucursal
					FROM 
						pos_z_cierres 
					WHERE
						to_date(to_char(dt_posz_fecha_sistema,'dd/mm/yyyy'),'dd/mm/yyyy') 
						BETWEEN to_date('" . pg_escape_string($fecha_del) . "', 'dd/mm/yyyy') AND to_date('" . pg_escape_string($fecha_al) . "', 'dd/mm/yyyy') ";
	
		if(!isset($almacen) || $almacen!="--") {
			$sql .= " AND ch_sucursal = '" . pg_escape_string($almacen) . "' ";
		}
	
		if(!isset($caja) || $caja!='') {
			$sql .= " AND ch_posz_pos = '" . pg_escape_string($caja) . "' ";
		}

			$sql .= " ORDER BY
							dt_posz_fecha_sistema, 
							nu_posturno, 
							ch_posz_pos 
						";
		
		//echo $sql;
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$resultado = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {	
			$resultado[$i] = $sqlca->fetchRow();	
		}
	
		return $resultado;
	}

	function totales($fecha_del,$fecha_al,$almacen,$caja, $trabajador) {
		global $sqlca;
	
		$sql = " SELECT
						sum(nu_posz_b_total) AS total_importe_total_boletas,
						sum(nu_posz_b_impuesto) AS total_impuesto_total_boletas,
						sum(nu_posz_f_total) AS total_importe_total_facturas,
						sum(nu_posz_f_impuesto) AS total_impuesto_total_facturas,
						sum(nu_posz_t_transas) AS total_numero_tikets,
						sum(nu_posz_t_total) AS total_importe_total_tikets,
						sum(nu_posz_t_impuesto) AS total_impuesto_total_tikets
					FROM 
						pos_z_cierres 
					WHERE
						to_date(to_char(dt_posz_fecha_sistema,'dd/mm/yyyy'),'dd/mm/yyyy') 
						BETWEEN to_date('" . pg_escape_string($fecha_del) . "', 'dd/mm/yyyy') AND to_date('" . pg_escape_string($fecha_al) . "', 'dd/mm/yyyy') ";
	
		if(!isset($almacen) || $almacen!="--") {
			$sql .= " AND ch_sucursal = '" . pg_escape_string($almacen) . "' ";
		}
	
		if(!isset($caja) || $caja!='') {
			$sql .= " AND ch_posz_pos = '" . pg_escape_string($caja) . "' ";
		}
		
		//echo $sql;
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$resultado = Array();	
		$resultado = $sqlca->fetchRow();
		
		return $resultado;
	}

	function obtenerListaEstaciones() {
		global $sqlca;

		$sql = " SELECT
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

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array("--"=>"Todos los Almacenes");

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}
}
