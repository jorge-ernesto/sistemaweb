<?php

class TipodeCambioModel extends Model {
	
	function Paginacion($pp, $pagina, $fecha, $fecha2){

		global $sqlca;

		$query = "select 
				tca_moneda,
				to_char(tca_fecha,'DD/MM/YYYY'),
				tca_compra_libre,
				tca_venta_libre,
				tca_compra_banco,
				tca_venta_banco,
				tca_compra_oficial,
				tca_venta_oficial
			from 
				int_tipo_cambio";

		if($fecha != ''){
		$query .= "
			where
				tca_fecha BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')";
		}
			
		$query .= "
			order by 
				tca_fecha desc,
				tca_moneda desc";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp, $pagina);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		$query .= " LIMIT " . pg_escape_string($pp) . " ";
		$query .= " OFFSET " . pg_escape_string($paginador->partir());

		echo $query;

		if ($sqlca->query($query) < 0)
			return false;
	    
    		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['tca_moneda']		= $a[0];
			$resultado[$i]['tca_fecha']		= $a[1];
			$resultado[$i]['tca_compra_libre'] 	= $a[2];
			$resultado[$i]['tca_venta_libre'] 	= $a[3];
			$resultado[$i]['tca_compra_banco'] 	= $a[4];
			$resultado[$i]['tca_venta_banco'] 	= $a[5];
			$resultado[$i]['tca_compra_oficial'] 	= $a[6];
			$resultado[$i]['tca_venta_oficial'] 	= $a[7];
			
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function agregar($moneda,$fecha,$compralibre,$ventalibre,$comprabanco,$ventabanco,$compraoficial,$ventaoficial) {
		global $sqlca;
		
		$validar = TipodeCambioModel::ValidaTipoCambio($codigo, $fecha);
		if ($validar == 1){
			$validar_ingreso_directo = TipodeCambioModel::ValidarIngresoDirecto();	
			if($validar_ingreso_directo == 1){ //SI ES INGRESO DIRECTO
				$query2 = "INSERT INTO int_tipo_cambio
							(tca_moneda,
							tca_fecha,
							tca_compra_libre,
							tca_venta_libre,
							tca_compra_banco,
							tca_venta_banco,
							tca_compra_oficial,
							tca_venta_oficial)
							VALUES
							('$moneda',
							'$fecha',
							'$compralibre',
							'$ventalibre',
							'$comprabanco',
							'$ventabanco',
							'$compraoficial',
							'$ventaoficial');";
			}else{ //NO ES INGRESO DIRECTO ASI QUE VALIDA LA FECHA CON LA TABLA pos_aprosys
				$query2 = "INSERT INTO int_tipo_cambio
							(tca_moneda,
							tca_fecha,
							tca_compra_libre,
							tca_venta_libre,
							tca_compra_banco,
							tca_venta_banco,
							tca_compra_oficial,
							tca_venta_oficial)
							VALUES
							('$moneda',
							(SELECT da_fecha FROM pos_aprosys WHERE da_fecha = '$fecha'),
							'$compralibre',
							'$ventalibre',
							'$comprabanco',
							'$ventabanco',
							'$compraoficial',
							'$ventaoficial');";
			}

			echo "<pre>";
			echo $query2;
			echo "</pre>";
			$sqlca->query($query2);
			return 1;
		}else{
			return 2;
		}
	}
	
	function eliminarRegistro($idregistro,$fecha){

		global $sqlca;

		$query = "DELETE FROM int_tipo_cambio WHERE tca_moneda = '$idregistro' AND tca_fecha = '$fecha';";
		echo $sql;
		$sqlca->query($query);
		return 'OK';
	}

	function actualizar($codigo,$fecha,$compralibre,$ventalibre,$comprabanco,$ventabanco,$compraoficial,$ventaoficial){
		global $sqlca;

			$query = "UPDATE 
					int_tipo_cambio
				  SET 
					tca_compra_libre   = '$compralibre',
					tca_venta_libre    = '$ventalibre',
					tca_compra_banco   = '$comprabanco',
					tca_venta_banco    = '$ventabanco',
					tca_compra_oficial = '$compraoficial',
					tca_venta_oficial  = '$ventaoficial'
				  WHERE 
					tca_moneda = '$codigo' AND
					tca_fecha  = '$fecha';";
			
			//echo $sql;

			$result = $sqlca->query($query);
			return '';
 	}
	
	function recuperarRegistroArray($codigo,$fecha){
	  	global $sqlca;
		
		    $registro = array();
		    $query = "select 
					tca_moneda,
					tca_fecha,
					tca_compra_libre,
					tca_venta_libre,
					tca_compra_banco,
					tca_venta_banco,
					tca_compra_oficial,
					tca_venta_oficial
				from
					int_tipo_cambio
				where
					tca_moneda = '$codigo' AND
					tca_fecha  = to_date('$fecha','DD/MM/YYYY')";
			 
		    $sqlca->query($query);

		    while( $reg = $sqlca->fetchRow()){
				$registro = $reg;
			}
		    
		    return $registro;
	  }

	function ValidaTipoCambio($codigo,$fecha){
		global $sqlca;

		$codigo = $_REQUEST['tca_moneda'];

		$query = "select count(*) from int_tipo_cambio where tca_moneda = '$codigo' and tca_fecha = '$fecha';";

		echo "<pre>";
		echo $query;
		echo "</pre>";

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 0;
		}else{
			return 1;
		}

	}

	function ValidarIngresoDirecto(){
		global $sqlca;

		$query = "select par_valor from int_parametros where par_nombre = 'permitir_tc_futuro';";

		echo "<pre>";
		echo $query;
		echo "</pre>";

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		return $a['par_valor'];
		
	}
}
