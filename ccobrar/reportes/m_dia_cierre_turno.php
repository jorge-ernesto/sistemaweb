<?php

class DiaCierreTurnoModel extends Model {
	
	function buscar($dia,$dia2){

		global $sqlca;

		$sql="select 
				stype,
				systemdate,
				begintime,
				endtime,
				to_char('created','DD/MM/YYYY'),
				createdby 
			from 
				s_shiftconstraint
			where
				created BETWEEN to_date('$dia','DD/MM/YYYY') AND to_date('$dia2','DD/MM/YYYY')
			order by 
				created desc";
	
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['stype']		= $a[0];
			$resultado[$i]['systemdate']	= $a[1];
			$resultado[$i]['begintime'] 	= $a[2];
			$resultado[$i]['endtime'] 	= $a[3];
			$resultado[$i]['created'] 	= $a[4];
			$resultado[$i]['createdby'] 	= $a[5];
			
		}
		
		return $resultado;
  	}
/*
	function agregar($moneda,$fecha,$compralibre,$ventalibre,$comprabanco,$ventabanco,$compraoficial,$ventaoficial) {
		global $sqlca;
		
		$validar = TipodeCambioModel::ValidaTipoCambio($codigo, $fecha);
		if ($validar == 1) {

		$sql = "insert into int_tipo_cambio (tca_moneda,
								       tca_fecha,
								       tca_compra_libre,
								       tca_venta_libre,
								       tca_compra_banco,
								       tca_venta_banco,
								       tca_compra_oficial,
								       tca_venta_oficial)
						      values ('$moneda',
							      '$fecha',
 						    	      '$compralibre',
							      '$ventalibre',
							      '$comprabanco',
							      '$ventabanco',
							      '$compraoficial',
							      '$ventaoficial');";

			if ($sqlca->query($sql) < 0)
					return 0;
				return 1;
			}else{
				return 2;
			}

	}

	function ValidaTipoCambio($codigo,$fecha){
		global $sqlca;
		
		$query = "select count(*) from itn_tipo_cambio where tca_moneda = '$codigo' and tca_fecha = '$fecha';";
		if ($sqlca->query($sql) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 0;
		}else{
			return 1;
		}

	}
*/
}
