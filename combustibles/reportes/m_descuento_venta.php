<?php

class DescuentoVentaModel extends Model {

	function ingresarComoVenta($fecha, $ticket, $caja, $usuario) {
		global $sqlca;

		$fec = explode("/",$fecha);
		$dia = $fec[2]."-". $fec[1]."-".$fec[0];
		$query1 = "	SELECT 		es, 
						dia, 
						pump,	
						importe,
						trans
				FROM 	
						pos_trans".$fec[2].$fec[1]." 
				WHERE 	
						tipo='C' 
						AND importe < 0
						AND caja = '".$caja."' 
						AND trans = ".$ticket." 
						AND dia = '".$dia."' ";

		if ($sqlca->query($query1) < 0)	
			return false;

		if ($sqlca->numrows() == 0)
			return 0;

		if ($sqlca->numrows() > 1)
			return 2;

		$resultado = Array();
		
		$a = $sqlca->fetchRow();	
		$trans		= $a['trans'];


		$query = "	DELETE FROM 	
						pos_trans".$fec[2].$fec[1]." 
				WHERE 	
						tipo='C' 
						AND importe < 0
						AND caja = '".$caja."' 
						AND trans = ".$ticket."  
						AND dia = '".$dia."' ;";
		$sqlca->query($query);
		DescuentoVentaModel::actualizarParte($dia);
		return 1;
	} 

	function actualizarParte($dia) {
		global $sqlca;

		$query3 = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd='A';";
		if ($sqlca->query($query3) < 0)	
			return false;
		$a = $sqlca->fetchRow();		
		$actual = $a['da_fecha'];

		if ($dia != $actual){
			$query 	= "DELETE FROM comb_ta_contometros WHERE dt_fechaparte='$dia' AND ch_usuario='AUTO';";
			$sqlca->query($query);

			$query3 = "DELETE FROM inv_movialma WHERE date(mov_fecha)='$dia' AND tran_codigo IN ('23','24','25');";
			$sqlca->query($query3); 
			
			$query2 = "SELECT combex_fn_contometros_auto('$dia');";
			$sqlca->query($query2);
		}	
	} 
}
