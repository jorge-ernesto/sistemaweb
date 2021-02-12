<?php

class TipoCambioModel extends Model { // CAMBIAR TIPO DE CAMBIO DE FAC_TA_FACTURA_CABECERA POR EL DE INT_TIPO_CAMBIO

	function migrar($desde, $hasta, $tipocambio) {
		global $sqlca;

		switch ($tipocambio) {
			case "1" : $tc = "tca_compra_libre"; 	break;
			case "2" : $tc = "tca_venta_libre"; 	break;
			case "3" : $tc = "tca_compra_banco"; 	break;
			case "4" : $tc = "tca_venta_banco"; 	break;
			case "5" : $tc = "tca_compra_oficial"; 	break;
			case "6" : $tc = "tca_venta_oficial"; 	break;
		}

		$query = "SELECT tca_fecha FROM int_tipo_cambio WHERE tca_moneda='02' AND tca_fecha between '$desde' and '$hasta' order by tca_fecha; ";

		if ($sqlca->query($query) < 0)	
			return false;

		$fechas = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$fechas[$i] = $a[0];
		}

		for ($k = 0; $k < count($fechas); $k++) {
			$sql = "UPDATE fac_ta_factura_cabecera 
				SET nu_tipocambio = (	SELECT $tc
						  	FROM int_tipo_cambio 
							WHERE tca_moneda='02' AND tca_fecha='".pg_escape_string($fechas[$k])."') 
				WHERE ch_fac_tipodocumento IN ('10', '20', '35') AND dt_fac_fecha ='".pg_escape_string($fechas[$k])."'; ";
			$sqlca->query($sql);
		}
		return 1;
  	}
}
