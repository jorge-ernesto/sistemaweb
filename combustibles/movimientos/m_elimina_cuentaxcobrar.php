<?php

class EliminaCuentaxCobrarModel extends Model {
	
	function buscar($filtro=array()){

		global $sqlca;

		$cond = ' and 2 ';

		 if ($filtro["codigo"] != ""){
      			$cond = " AND trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(ch_numdocumento) ~ '".pg_escape_string($filtro["codigo"])."' ";
    		}
		    $sql = "SELECT cc.cli_codigo, ".
				    "cl.cli_razsocial, ".
				    "cc.ch_tipdocumento, ".
				    "cc.ch_seriedocumento, ".
				    "cc.ch_numdocumento, ".
				    "cc.dt_fechaemision, ".
				    "cc.dt_fechasaldo, ".
				    "cc.ch_moneda, ".
				    "cc.nu_importetotal, ".
				    "cc.nu_importesaldo ".
			     "FROM ccob_ta_cabecera cc, int_clientes cl ".
			     "WHERE cc.cli_codigo=cl.cli_codigo ".
			     "AND cc.nu_importesaldo<>0 ".
			     "AND trim(cc.ch_tipcontable) = 'C' ".
			     " ".$cond." ".
			     "ORDER BY cc.ch_tipdocumento,cc.ch_seriedocumento,cc.ch_numdocumento ";

		//echo $sql;
	
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

			$codigo_cliente 	= $a[0];
			$cli_razonsocial 	= $a[1];
		    	$tipodocumento 		= $a[2];
		    	$seriedocumento 	= $a[3];
		    	$numdocumento 		= $a[4];
		    	$fechaemision		= $a[5];
			$fechasaldo	 	= $a[6];
			$moneda		 	= $a[7];
			$importetotal		= $a[8];
			$importesaldo	 	= $a[9];
		
			$resultado[$i]['cli_codigo']			= $codigo;
			$resultado[$i]['cli_razsocial']			= $almacen;
			$resultado[$i]['ch_tipdocumento']		= $fecha;
			$resultado[$i]['ch_seriedocumento'] 		= $turno;
			$resultado[$i]['ch_numdocumento'] 		= $descripcion;
			$resultado[$i]['dt_fechaemision'] 		= $usuario;
			$resultado[$i]['dt_fechasaldo'] 		= $actualizacion;
			$resultado[$i]['ch_moneda'] 			= $ip;
			$resultado[$i]['nu_importetotal'] 		= $actualizacion;
			$resultado[$i]['nu_importesaldo'] 		= $ip;

		}
		
		return $resultado;
  	}

	function EliminarCuenta($codigo, $tipo, $serie, $numero, $importe){
		global $sqlca;

		$sql="DELETE FROM
				ccob_ta_detalle
			WHERE
				cli_codigo = '$codigo' AND
				ch_tipdocumento = '$tipo' AND
				ch_seriedocumento = '$serie' AND
				ch_numdocumento = '$numero' AND
				nu_importemovimiento = '$importe' ";
		echo $sql;
		$sqlca->query($sql);

		return ok;
	}

	function recuperarRegistroArray($almacen,$fecha,$turno,$descripcion,$actualizacion){
	  	global $sqlca;
		
		    $registro = array();
		    $sql = "select 
					es,
					fecha,		
					turno,
					descripcion,
					usuario,
					fecha_actualizacion,
					auditorpc
				from
					caja_cuadre_turno_ticket
				where
					es = '$almacen' AND
					fecha = to_date('$fecha','DD/MM/YYYY') AND
					turno = '$turno' AND
					descripcion = '$descripcion'";
			 
		    $sqlca->query($sql);

		    while( $reg = $sqlca->fetchRow()){
				$registro = $reg;
			}
		    
		    return $registro;
	  }

}
