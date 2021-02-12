<?php

class AplicacionesModel extends Model{

	function tmSeleccionaCargoAbono($filtro) {
		global $sqlca;

    		$cond = '';

    		if ($filtro != ""){
      			$cond = " AND trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(ch_numdocumento) = '".pg_escape_string($filtro)."' ";
    		}

    		$query = "SELECT 
				cc.cli_codigo, 
                    		cl.cli_razsocial, 
                    		cc.ch_tipdocumento, 
                    		cc.ch_seriedocumento, 
                    		cc.ch_numdocumento, 
                    		cc.dt_fechaemision, 
                    		cc.dt_fechasaldo, 
                    		cc.ch_moneda, 
                    		cc.nu_importetotal, 
                    		cc.nu_importesaldo 
             		FROM 
				ccob_ta_cabecera cc, 
				int_clientes cl 
             		WHERE 
				cc.cli_codigo=cl.cli_codigo $cond
	     		ORDER BY 
				cc.ch_tipdocumento,
				cc.ch_seriedocumento,
				cc.ch_numdocumento ";

	     	if ($sqlca->query($query) <= 0){
		    	return $sqlca->get_error();
		}
        	while( $reg = $sqlca->fetchRow())  {
	        	$listado['datos'][] = $reg;
	    	}   

	    	return $listado; 
	}
  
	function tmListadoFinalCargos($filtro) {
		global $sqlca;

	    	$cond = '';

    		if ($filtro != "") {
      			$cond = " AND trim(cc.cli_codigo)||trim(cc.ch_tipdocumento)||trim(cc.ch_seriedocumento)||trim(cc.ch_numdocumento) = '".pg_escape_string(trim($filtro))."' ";
    		}

    		$query = "SELECT 
				cc.cli_codigo, 
                    		cl.cli_razsocial, 
                    		cc.ch_tipdocumento, 
                    		cc.ch_seriedocumento, 
                    		cc.ch_numdocumento, 
                    		cc.dt_fechaemision, 
                    		cc.dt_fechasaldo, 
                    		cc.ch_moneda, 
                    		cc.nu_importetotal, 
                    		cc.nu_importesaldo 
             		FROM 
				ccob_ta_cabecera cc, 
				int_clientes cl 
             		WHERE 
				cc.cli_codigo=cl.cli_codigo 
             			AND cc.nu_importesaldo>0 
             			--AND trim(cc.ch_tipcontable) = 'C' 
				$cond
	 		ORDER BY 
				cc.ch_tipdocumento,
				cc.ch_seriedocumento,
				cc.ch_numdocumento ";
		//echo $query;

 		$resultado_1 = $sqlca->query($query);

	 	if($filtro != "") {
			while( $reg = $sqlca->fetchRow())
			{
				$listado['datos'][] = $reg;
			}    
	    	}

	    	return $listado;    
	}
		
  	function tmListadoCargos($filtro=array()) {
  		global $sqlca;

    		$cond = '';
    		if ($filtro["codigo"] != "") {
      			$cond = " AND trim(cc.cli_codigo) = '".pg_escape_string($filtro["codigo"])."' ";
    		}

    		$query = "SELECT 
				cc.cli_codigo, 
                    		cl.cli_razsocial, 
                    		cc.ch_tipdocumento, 
                    		cc.ch_seriedocumento, 
                    		cc.ch_numdocumento, 
                    		cc.dt_fechaemision, 
                    		cc.dt_fechasaldo, 
                    		cc.ch_moneda, 
                    		cc.nu_importetotal, 
                    		cc.nu_importesaldo 
             		FROM 
				ccob_ta_cabecera cc, 
				int_clientes cl 
             		WHERE 
				cc.cli_codigo=cl.cli_codigo 
             			AND cc.nu_importesaldo>0 
				$cond
	 		ORDER BY 
				cc.ch_tipdocumento,
				cc.ch_seriedocumento,
				cc.ch_numdocumento ";  //AND trim(cc.ch_tipcontable) = 'C';
	//print($query);
    		if ($sqlca->query($query)<=0){
      			return $sqlca->get_error();
    		}

   		if($filtro["codigo"] != "") {
			while( $reg = $sqlca->fetchRow()) {
				$listado['datos'][] = $reg;
			}    
    		}    
    	
    		return $listado;
  	}

  	function tmListadoAbonos($codigo, $tipo) {
    		global $sqlca;

    		$cond = '';

		if ($codigo != "") {
      			$cond = " AND trim(cli_codigo) = '".pg_escape_string(trim($codigo))."' ";
      			$cond .= ($tipo=='10' || $tipo=='35' || $tipo=='20')?" AND trim(ch_tipdocumento) = '20' ":"";
   		}
    
    		$query = "SELECT 
				ch_tipdocumento, 
                    		ch_seriedocumento, 
                    		ch_numdocumento, 
                    		dt_fechaemision, 
                    		dt_fechasaldo, 
                    		ch_moneda, 
                    		nu_importetotal, 
                    		nu_importesaldo 
             		FROM 
				ccob_ta_cabecera 
             		WHERE 
				nu_importesaldo>0 
              			--AND trim(ch_tipcontable) = 'A'  
             			$cond
	     		ORDER BY 
				CH_NUMDOCUMENTO ASC ";
		trigger_error($query);
    		//echo "QUERY ABONOS: $query \n";
	//print($query);
    		if ($sqlca->query($query)<=0) {
      			return $sqlca->get_error();
    		}

    		while( $reg = $sqlca->fetchRow()) {
        		$listado[] = $reg;
    		}    

    		return $listado;
  	}
  
  	function ActualizarCargos($CodCliente,$CodDocCargo,$NumDocCargo,$CodDocAbono,$NumDocAbono,$ImporteAplicacion) {
  		global $sqlca;

    		//$FechaAplicacion = $_SESSION['fec_aplicacion'];
    		//$result = $sqlca->functionDB("ccob_fn_aplicaciones('".$CodDocCargo."','".$NumDocCargo."','".$CodDocAbono."','".$NumDocAbono."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");
		$sql = "
			UPDATE
				ccob_ta_cabecera
			SET
				nu_importesaldo = nu_importesaldo - $ImporteAplicacion,
				dt_fecha_actualizacion = now()
			WHERE
				cli_codigo = '$CodCliente' AND
				ch_tipdocumento = '$CodDocCargo' AND
				ch_seriedocumento||ch_numdocumento = '$NumDocCargo';
		";

		var_dump($sql);

		if($sqlca->query($sql) < 0)
			return $sqlca->get_error();
		
		$sql2 = "
			UPDATE
				ccob_ta_detalle
			SET
				ch_tipmovimiento = '3'
			WHERE
				cli_codigo = '$CodCliente' AND
				ch_tipdocumento = '$CodDocCargo' AND
				ch_seriedocumento||ch_numdocumento = '$NumDocCargo';
		";

		var_dump($sql2);

		if($sqlca->query($sql2) < 0)
			return $sqlca->get_error();

    		return OK;
  	}

	function ActualizarCargosNC($CodCliente,$numdoc,$ImporteAplicacion) {
  		global $sqlca;

    		//$FechaAplicacion = $_SESSION['fec_aplicacion'];
    		//$result = $sqlca->functionDB("ccob_fn_aplicaciones('".$CodDocCargo."','".$NumDocCargo."','".$CodDocAbono."','".$NumDocAbono."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");
		$sql = "
			UPDATE
				ccob_ta_cabecera
			SET
				nu_importesaldo		= nu_importesaldo - $ImporteAplicacion,
				dt_fecha_actualizacion	= now()
			WHERE
				cli_codigo = '$CodCliente' AND
				ch_tipdocumento = '20' AND
				ch_seriedocumento||ch_numdocumento = '$numdoc';
			";

		var_dump($sql);

		if($sqlca->query($sql) < 0)
			return $sqlca->get_error();
				
		$sql2 = "
			UPDATE
				ccob_ta_detalle
			SET
				ch_tipmovimiento = '3'
			WHERE
				cli_codigo = '$CodCliente' AND
				ch_tipdocumento = '$CodDocCargo' AND
				ch_seriedocumento||ch_numdocumento = '$NumDocCargo';
		";

		var_dump($sql2);

		if($sqlca->query($sql2) < 0)
			return $sqlca->get_error();

    		return OK;
  	}
  
  	function AplicarporMonto($CodCliente,$CodDocCargo,$NumDocCargo,$ImporteAplicacion){
	  	global $sqlca;

	  	$FechaAplicacion = $_SESSION['fec_aplicacion'];

	  	$result = $sqlca->functionDB("ccob_fn_aplicaciones_por_monto_notas_credito('".$CodDocCargo."','".$NumDocCargo."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");

	  	return OK;
  	}
  
  	function ClientesCBArray($condicion='') {
    		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT cli_codigo,cli_razsocial,cli_rsocialbreve FROM int_clientes ".
    		$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';

    		if ($sqlca->query($query)<=0)
     			return $cbArray;

    		while($result = $sqlca->fetchRow()){
      			$cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_rsocialbreve"];
    		}

    		ksort($cbArray);

    		return $cbArray;
  	}
}
