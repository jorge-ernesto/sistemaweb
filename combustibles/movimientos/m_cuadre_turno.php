<?php

class CuadreTurnoModel extends Model {

	function obtenerAlmacenes($alm) {
		global $sqlca;
		
			if(trim($alm) == "")
				$cond = "";
			else
				$cond = " AND ch_almacen = '$alm'"; 
	
			$sql = "SELECT
				    ch_almacen,
				    ch_almacen||' - '||ch_nombre_almacen
				FROM
				    inv_ta_almacenes
				WHERE
				    ch_clase_almacen='1' $cond 
				ORDER BY
				    ch_almacen;";
	
			if ($sqlca->query($sql) < 0) 
				return false;
	
			$result = Array();
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			    	$a = $sqlca->fetchRow();		    
			    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}
	
	function buscar($almacen,$dia,$dia2){

		global $sqlca;

		$sql="select
				id_cuadre_turno_ticket,
				es,
				to_char(fecha,'DD/MM/YYYY'),
				turno,
				descripcion,
				usuario,
				to_char(fecha_actualizacion,'DD/MM/YYYY HH24:MI:SS'),
				auditorpc
			from 
				caja_cuadre_turno_ticket
			where
				fecha BETWEEN to_date('$dia','DD/MM/YYYY') AND to_date('$dia2','DD/MM/YYYY')
			order by 
				fecha desc,
				turno desc";

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$codigo 		= $a[0];
			$almacen 		= $a[1];
		    	$fecha 			= $a[2];
		    	$turno 			= $a[3];
		    	$descripcion 		= $a[4];
		    	$usuario		= $a[5];
			$actualizacion	 	= $a[6];
			$ip		 	= $a[7];
		
			$resultado[$i]['id_cuadre_turno_ticket']	= $codigo;
			$resultado[$i]['es']				= $almacen;
			$resultado[$i]['fecha']				= $fecha;
			$resultado[$i]['turno'] 			= $turno;
			$resultado[$i]['descripcion'] 			= $descripcion;
			$resultado[$i]['usuario'] 			= $usuario;
			$resultado[$i]['fecha_actualizacion'] 		= $actualizacion;
			$resultado[$i]['auditorpc'] 			= $ip;
		}
		
		return $resultado;
  	}


	function agregar($almacen,$fecha,$turno,$descripcion,$fecha2,$usuario,$ip){
		global $sqlca;

		$anio = substr($fecha,6,4);
		$mes = substr($fecha,3,2);
		$dia = substr($fecha,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		$flag2 = CuadreTurnoModel::validaCuadre($almacen, $fecha, $turno);

		if ($flag2 == 1) {

			$sql = "INSERT INTO
					caja_cuadre_turno_ticket
								(es,
								 fecha,
								 turno,
								 descripcion,
								 usuario,
								 fecha_actualizacion,
								 auditorpc)
					VALUES
								('$almacen',
							         '$fecha',
 						    	         '$turno',
							         '$descripcion',
								 '$usuario',
							         now(),
								 '$ip');";

			if ($sqlca->query($sql) < 0)
					return 0;
				return 1;
		}else{
			return 2;
		}
	}

	function eliminarRegistro($codigo){
		global $sqlca;

		$sql="DELETE FROM caja_cuadre_turno_ticket WHERE id_cuadre_turno_ticket = '$codigo';";

		$sqlca->query($sql);
		return ok;	
	}

	function actualizar($almacen,$fecha,$turno,$descripcion,$fecha2,$usuario){
		global $sqlca;

		$anio = substr($fecha,6,4);
		$mes = substr($fecha,3,2);
		$dia = substr($fecha,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		$sql = "
			UPDATE
				caja_cuadre_turno_ticket
			SET				
				descripcion = '$descripcion',
				fecha_actualizacion = now()
			WHERE 
				es = '$almacen' AND
				fecha = '$fecha' AND
				turno = '$turno';
			";

		if ($sqlca->query($sql) < 0)
			return 0;
		return '';

 	}
	
	function recuperarRegistroArray($almacen,$fecha,$turno,$descripcion,$actualizacion){
		global $sqlca;
		
		$registro = array();
		$sql = "
			SELECT 
				es,
				fecha,		
				turno,
				descripcion,
				usuario,
				fecha_actualizacion,
				auditorpc
			FROM
				caja_cuadre_turno_ticket
			WHERE
				es = '$almacen' AND
				fecha = to_date('$fecha','DD/MM/YYYY') AND
				turno = '$turno' AND
				descripcion = '$descripcion';
			";
			 
		$sqlca->query($sql);

		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}
		    
		return $registro;

	}
		
	function validaCuadre($almacen, $fecha, $turno) {
		global $sqlca;

		$sql = "SELECT count(*) FROM caja_cuadre_turno_ticket WHERE es = '$almacen' AND fecha = '$fecha' AND turno = '$turno';";

		echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$a = $sqlca->fetchRow();

		if($a[0]>=1) {
			return 0;  // no se puede ingresar ..!!!
		} else {
			return 1; // ingreso ..!!!
		}
	}
}
