<?php

class DesconsolidarModel extends Model {

	function GetAlmacenes() {
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

	function FechaInicio($almacen){
		global $sqlca;

		$query = "SELECT count(*) fila FROM pos_consolidacion WHERE estado='1' AND almacen = '".$almacen."';";

		$sqlca->query($query);

		$datos = $sqlca->fetchRow();

		return $datos['fila'];

	}

	function desconsolidar($almacen, $fecha, $turno, $usuario, $ip) {
		global $sqlca;

		$query = "
		SELECT
			1
		FROM
			pos_consolidacion 
		WHERE
			almacen 	= '".$almacen."'
			AND dia 	= '".$fecha."' 
			AND turno 	= $turno
			AND estado 	= '1';
		";
		// echo "<pre>";
		// echo $query;
		// echo "</pre>";

		if ($sqlca->query($query) < 0) //Si hay un error en la query
			return false;

		if ($sqlca->query($query) == 0) //Si no hay registros devueltos por la query
			return false;
	
		$query2 = "UPDATE pos_consolidacion SET estado = '0', usuario = '".$usuario."', ip = '".$ip."', fecha = now() WHERE dia='".$fecha."' AND turno = ".$turno." AND almacen = '".$almacen."';";
		$sqlca->query($query2);

		$query3 = "DELETE FROM comb_diferencia_trabajador WHERE dia='".$fecha."' AND turno=".$turno." AND flag='0' AND es = '".$almacen."';";
		$sqlca->query($query3);

		return 1;
	}
	
	function obtenerSiguiente($almacen) {
		global $sqlca;

		if(!empty($almacen)){

			$sql =	"	
				SELECT 	
					c.dia, 
					max(c.turno), 
					to_char(c.dia,'DD/MM/YYYY') 
				FROM 
					pos_consolidacion c 
				WHERE 
					c.dia 		= (SELECT max(dia) FROM pos_consolidacion WHERE almacen = '".$almacen."' AND estado = '1')
					AND almacen 	= '".$almacen."'
					AND estado 	= '1'
				GROUP BY
					c.dia
				LIMIT 1;
			";
	
		}

		echo $sql;

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0)
			return FALSE;

		$r = $sqlca->fetchRow();
		$ret = Array();
		$ret['dia'] = $r[0];
		$ret['diab'] = $r[2];
		$ret['turno'] = $r[1];
				
		return $ret;
	}
}
