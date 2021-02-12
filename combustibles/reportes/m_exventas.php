<?php

class ExVentasModel extends Model {

	function obtenerVentaPromedio($fecha, $dias, $sucursal) {
		global $sqlca;

		$segundos_x_dia = 60*60*24;	// 60 segundos x 60 minutos x 24 horas

		list($dia,$mes,$ano) = sscanf($fecha, "%2s/%2s/%4s");
		$timestamp = mktime(0, 0, 0, $mes, $dia, $ano);
		$timestamp -= $segundos_x_dia*$dias;
		$desde = date("Y-m-d", $timestamp);

		$sql = "SELECT
			    	(sum(nu_ventagalon)-sum(nu_afericionveces_x_5*5))/" . pg_escape_string($dias) . " AS nu_venta,
			    	ch_tanque
			FROM
			    	comb_ta_contometros
			WHERE
				ch_sucursal='" . pg_escape_string($sucursal) . "'
			    	AND dt_fechaparte>='" . pg_escape_string($desde) . "'
			    	AND dt_fechaparte<=to_date('" . pg_escape_string($fecha) . "', 'DD/MM/YYYY')
			GROUP BY
			    	ch_tanque
			ORDER BY
			    	ch_tanque;";
//print_r($sql);
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $resultado[$a[1]] = $a[0];
		}
	
		return $resultado;
    	}

    	function obtenerReporte($fecha, $dias) {
		$reporte = ExistenciasModel::search($fecha);

		foreach($reporte['sucursales'] as $cod_sucursal => $sucursal) {
		    	$ventas = ExVentasModel::obtenerVentaPromedio($fecha, $dias, $sucursal['codigo']);	
		    	$total  = 0;
		    
		    	foreach($ventas as $ch_tanque => $promedio) {
				$producto = $sucursal['productos']['tanque_'.$ch_tanque]; 
				if ($producto != "GLP") 
					$total += $promedio;
				$promedio = number_format($promedio, 0, '', ',');
				$reporte['sucursales'][$cod_sucursal]['productos'][$producto.'_promedio'] = $promedio;		
		   	}		    
		    	$reporte['sucursales'][$cod_sucursal]['totales']['promedio'] = $total;
		}
	print_r($reporte);
		return $reporte;
    	}
}
