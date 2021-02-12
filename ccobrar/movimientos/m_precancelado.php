<?php
  // Modelo para Aplicaciones de Cuentas por cobrar

Class PrecanceladoModel extends Model{
  function quitarPrecancelado($codigo){
  	global $sqlca;
  	
  	   $query = "UPDATE ccob_ta_cabecera 
  			  SET ch_precancelado=null, dt_fecha_precancelado=null, ch_sucursal_precancelado=null, nu_importe_precancelado=null, ccob_informo=null 
  			  WHERE trim(ch_tipdocumento)||trim(ch_seriedocumento)||trim(ch_numdocumento)||trim(cli_codigo)='".trim($codigo)."';
  			  UPDATE tmp_precancelado
  			  SET ch_precancelado=null, dt_fecha_precancelado=null, ch_sucursal_precancelado=null, nu_importe_precancelado=null 
  			  WHERE trim(ch_tipdocumento)||trim(ch_seriedocumento)||trim(ch_numdocumento)||trim(cli_codigo)='".trim($codigo)."';
  			  ";
  	   //print_r($query);
	     if ($sqlca->query($query)<=0){
		    return $sqlca->get_error();
		 }
		
		 return OK;
  }
	
  //Otras funciones para consultar la DB
  function tmSeleccionaDocumento($filtro){
		global $sqlca;
    	$cond = 'where 1=2 ';
    	if ($filtro != ""){
      		$cond = "where trim(cli_codigo)||trim(ch_seriedocumento)||''||trim(ch_numdocumento) ~ '".pg_escape_string($filtro)."' and nu_importesaldo>0 ";
    	}
    	$query = "SELECT ch_tipdocumento, 
    					 trim(ch_seriedocumento)||trim(ch_numdocumento) as numero,
    					 cli_codigo,    				
    				 	 ch_moneda,
    				 	 nu_importetotal,
    				 	 nu_importesaldo,
    				 	 nu_importe_precancelado, 
    				 	 dt_fecha_precancelado,
    				 	 ccob_informo, 
       					 dt_fechaemision, 
       				     ch_sucursal, 
       				     ch_sucursal_precancelado,
       				     dt_fechasaldo
  				  FROM ccob_ta_cabecera ".$cond." ORDER BY ch_tipdocumento, ch_numdocumento";
	     if ($sqlca->query($query)<=0){
		    return $sqlca->get_error();
		 }
        while( $reg = $sqlca->fetchRow())
	    {
	        $listado['datos'][] = $reg;
	    }   
	    return $listado; 
	}
  
  function recuperarRegistroporID($codigo){
  	global $sqlca;
  	$query = "SELECT     ch_tipdocumento, 
    					 ch_seriedocumento,
    					 ch_numdocumento,
    					 cli_codigo,    				
    				 	 ch_moneda,
    				 	 nu_importetotal,
    				 	 nu_importesaldo,
    				 	 nu_importe_precancelado, 
    				 	 dt_fecha_precancelado,
    				 	 ccob_informo, 
       					 dt_fechaemision, 
       				     ch_sucursal, 
       				     ch_sucursal_precancelado
  				  FROM ccob_ta_cabecera WHERE trim(ch_tipdocumento)||trim(ch_seriedocumento)||trim(ch_numdocumento)||trim(cli_codigo)='".trim($codigo)."'";
	     if ($sqlca->query($query)<=0){
		    return $sqlca->get_error();
		 }
        while( $reg = $sqlca->fetchRow())
	    {
	        $listado['datos'][] = $reg;
	    }   
	    //print_r($query);
	    return $listado; 
  }
  
  function TiposSeriesCBArray($codigo='')
  {
    global $sqlca;
    if ($codigo!=''){
    	$cond = "where ch_sucursal ~ '".$codigo."'";
    }
    $cbArray = array();
    $query = "SELECT ch_sucursal as codigo, ch_sucursal||' - '||ch_nombre_sucursal as descripcion from int_ta_sucursales ".$cond;
    
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result["codigo"])] = $result["descripcion"];
     }

    ksort($cbArray);
    return $cbArray;
  }
  
  function ClientesCBArray($condicion=''){
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
  
  function precancelarDocumento($datos){
  	global $sqlca;
  	if ($sqlca->perform('ccob_ta_cabecera', $datos, 'update', "ch_tipdocumento='".trim($datos['ch_tipdocumento'])."' AND ch_seriedocumento='".trim($datos['ch_seriedocumento'])."' AND ch_numdocumento='".trim($datos['ch_numdocumento'])."' AND cli_codigo='".trim($datos['cli_codigo'])."'")>=0){
        $sqlca->functionDB("pasar_precancelados('".trim($datos['ch_tipdocumento'])."','".trim($datos['ch_seriedocumento'])."','".trim($datos['ch_numdocumento'])."','".trim($datos['cli_codigo'])."','".trim($datos['ch_precancelado'])."',".trim($datos['nu_importe_precancelado']).",'".trim($datos['ch_sucursal_precancelado'])."','".trim($datos['dt_fecha_precancelado'])."')");
   		return OK;
    }
  }
}
