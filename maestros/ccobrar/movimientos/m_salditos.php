<?php

class SalditosModel extends Model{
    		
  	function cancelarSalditos($id, $monto,$tipo_doc,$num_doc,$caja,$glosa) {
  		global $sqlca;
  		
    		$FechaCancela = $_SESSION['fec_aplicacion'];
    		$arrfecha = explode('/',$FechaCancela);
    		$FechaCancela = $arrfecha[2].'/'.$arrfecha[1].'/'.$arrfecha[0];
    		$result = $sqlca->functionDB("registra_cancelacion('".trim($id)."',".$monto.",'".trim($FechaCancela)."','".$tipo_doc."','".$num_doc."','".$caja."','".$glosa."')");
    		
    		return OK;
  	}
  
  	function tmListadoDocumentos($filtro=array()) {
  		global $sqlca;
  		
    		$cond = '';
    		if ($filtro["codigo"] != ""){
      			$cond = " AND (trim(cc.cli_codigo) = '".pg_escape_string($filtro["codigo"])."' OR trim(cc.ch_numdocumento) = '".pg_escape_string($filtro["codigo"])."')";
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
			      	AND  cc.nu_importesaldo>0 ".$cond." 
			ORDER BY 
				cc.ch_tipdocumento,cc.ch_seriedocumento,cc.ch_numdocumento ";
				
    		if ($sqlca->query($query)<=0){
      			return $sqlca->get_error();
    		}
    		if($filtro["codigo"] != "") {
			while( $reg = $sqlca->fetchRow()){
				$listado['datos'][] = $reg;
			}    
    		}    
        
    		return $listado;
  	}

  	function obtenerDocumento($id){
  		global $sqlca;
  		
  		$cond = " AND trim(cc.cli_codigo)||trim(cc.ch_tipdocumento)||trim(cc.ch_seriedocumento)||trim(cc.ch_numdocumento) = '".pg_escape_string($id)."' ";
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
		     		ccob_ta_cabecera cc, int_clientes cl 
		     	WHERE 
		     		cc.cli_codigo=cl.cli_codigo ".$cond;
	
	 	if ($sqlca->query($query)<=0){
     	 		return $sqlca->get_error();
    		}
    		while( $reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
		}    
		
    	 	return $listado;
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
