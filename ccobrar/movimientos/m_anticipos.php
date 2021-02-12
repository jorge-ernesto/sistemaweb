<?php

Class AnticiposModel extends Model{

	function tmListaAnticipos($filtro = array()) {
		global $sqlca;

	    	$cond = '';
	    	if ($filtro["codigo"] != "") {
	      		$cond = " AND trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(ch_numdocumento) ~ '".pg_escape_string($filtro["codigo"])."' ";
	    	}
	    	
	    	$query = "SELECT cc.cli_codigo, 
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
		     	WHERE 	cc.cli_codigo=cl.cli_codigo 
				and trim(cc.ch_tipdocumento)='21' and cc.nu_importesaldo>0".$cond.
		     	"ORDER BY 
				cc.ch_tipdocumento,
				cc.ch_seriedocumento,
				cc.ch_numdocumento ";
		      	 
		if ($sqlca->query($query) <= 0) 
			return $sqlca->get_error();
	
		if($filtro["codigo"] != "") {
			while( $reg = $sqlca->fetchRow()) {
				$listado['datos'][] = $reg;
			}   
		}

		return $listado; 
	}
	
	function tmListaResumenes($codigo) {
		global $sqlca;

	    	$cond = '';
	    	if ($codigo != "") {
			$cond = " AND trim(cc.cli_codigo) = '".pg_escape_string(substr(trim($codigo),0,6))."' ";
		}
	    	
	    	$query = "SELECT cc.cli_codigo, 
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
				and trim(cc.ch_tipdocumento)='22' 
				and cc.nu_importesaldo>0 ".$cond.
			"ORDER BY 
				cc.ch_tipdocumento,
				cc.ch_seriedocumento,
				cc.ch_numdocumento ";
	
		if ($sqlca->query($query) <= 0) 
			return $sqlca->get_error();

		while( $reg = $sqlca->fetchRow()) {
		        $listado['datos'][] = $reg;
		}   
		return $listado; 
	}
	
	
	function tmSeleccionaAnticipo($filtro) {
		global $sqlca;

	    	$cond = '';
	    	if ($filtro != ""){
	      		$cond = " AND trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(ch_numdocumento) = '".pg_escape_string($filtro)."' ";
	    	}
	    	$query = "SELECT cc.cli_codigo, 
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
				and trim(cc.ch_tipdocumento)='21' ".$cond.
			"ORDER BY 
				cc.ch_tipdocumento,
				cc.ch_seriedocumento,
				cc.ch_numdocumento ";
		    
		if ($sqlca->query($query) <= 0) {
			return $sqlca->get_error();
		}
		while( $reg = $sqlca->fetchRow()) {
			$listado['datos'][] = $reg;
		}	       
	   
		return $listado; 
	}
	
	function AplicarResumenes($CodCliente, $CodDocCargo,$NumDocCargo,$CodDocAbono,$NumDocAbono,$ImporteAplicacion) {
	  	global $sqlca;

		$FechaAplicacion = $_SESSION['fec_aplicacion'];
		$result = $sqlca->functionDB("ccob_fn_aplicaciones('".$CodDocCargo."','".$NumDocCargo."','".$CodDocAbono."','".$NumDocAbono."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");

		return OK;
	}
	  
	function AplicarporMonto($CodCliente, $CodDocCargo,$NumDocCargo,$ImporteAplicacion) {
	  	global $sqlca;

		$FechaAplicacion = $_SESSION['fec_aplicacion'];
		$result = $sqlca->functionDB("ccob_fn_aplicaciones_por_monto_resumenes('".$CodDocCargo."','".$NumDocCargo."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");

		return OK;
	}
	  
	function ClientesCBArray($condicion = '') {
		global $sqlca;

		$cbArray = array();
		$query = "SELECT cli_codigo,cli_razsocial,cli_rsocialbreve FROM int_clientes ".
		$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()){
			$cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_rsocialbreve"];
		}
		ksort($cbArray);

		return $cbArray;
	}
}
