<?php
  // Modelo para Inclusion de Cuentas por cobrar

Class InclusionModel extends Model{
  
  //Otras funciones para consultar la DB

  function tmListadoCargos($filtro=array())
  {
  global $sqlca;
    $cond = '';
    if ($filtro["codigo"] != "")
    {
      $cond = " AND trim(cl.cli_razsocial)||''||trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(cc.ch_numdocumento) ~ '".pg_escape_string($filtro["codigo"])."' ";
    }
		$query = "SELECT cc.cli_codigo, ".
						"cl.cli_razsocial, ".
						"cc.ch_tipdocumento, ".
						"cc.ch_seriedocumento, ".
						"cc.ch_numdocumento, ".
						"cc.dt_fechaemision, ".
						"cc.dt_fechavencimiento, ".
						"cc.ch_moneda, ".
						"cc.nu_importetotal, ".
						"cc.nu_importesaldo, ".
						"trim(cc.ch_tipcontable), cc.nu_dias_vencimiento ".
				 "FROM ccob_ta_cabecera cc, int_clientes cl ".
				 "WHERE cc.cli_codigo=cl.cli_codigo ".
				 " ".$cond." ".
			 "ORDER BY cc.dt_fechaemision desc ";
	

    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    
    if($filtro["codigo"] != "")
	{
		while( $reg = $sqlca->fetchRow())
		{
			$listado['datos'][] = $reg;
		}    
    }    
    
    $listado['paginacion'] = $listado2;
	return $listado;
  }

  function obtenerDocumentodeCargo($codigo){
  	global $sqlca;
    $cond = " AND trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(cc.ch_numdocumento) = '".pg_escape_string($codigo)."' ";
    $query = "SELECT cc.cli_codigo, ".
						"cl.cli_razsocial, ".
						"cc.ch_tipdocumento, ".
						"cc.ch_seriedocumento, ".
						"cc.ch_numdocumento, ".
						"cc.dt_fechaemision, ".
						"cc.dt_fechavencimiento, ".
						"cc.ch_moneda, ".
						"cc.nu_importetotal, ".
						"cc.nu_importesaldo, ".
						"trim(cc.ch_tipcontable), cc.nu_dias_vencimiento ".
				 "FROM ccob_ta_cabecera cc, int_clientes cl ".
				 "WHERE cc.cli_codigo=cl.cli_codigo ".
				 " ".$cond." ".
			 "ORDER BY cc.dt_fechaemision desc ";
			    if ($sqlca->query($query)<=0){
			      return $sqlca->get_error();
			    }
			    while( $reg = $sqlca->fetchRow())
			    {
			        $listado['datos'][] = $reg;
			    }    
			    return $listado;
  }
  
  function tmListadoAbonos($codigo)
  {
    global $sqlca;
    $cond = '';
    if ($codigo != ""){
      $cond = " WHERE trim(cli_codigo)||''||trim(ch_tipdocumento)||''||trim(ch_seriedocumento)||''||trim(ch_numdocumento) = '".pg_escape_string($codigo)."' ";
    }
	$query = "SELECT ch_tipdocumento, ".
						"ch_seriedocumento, ".
						"ch_numdocumento, ".
						"dt_fechamovimiento, ".
						"dt_fecha_actualizacion, ".
						"ch_moneda, ".
						"nu_importemovimiento, ".
						"ch_tipmovimiento, ".
						"ch_tipdocreferencia, ".
						"ch_numdocreferencia ".
				 "FROM ccob_ta_detalle ".
				 " ".$cond." ".
			 "ORDER BY ch_tipmovimiento, dt_fechamovimiento";
	
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    while( $reg = $sqlca->fetchRow())
    {
        $listado[] = $reg;
    }    
    return $listado;
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
  
  function modificarDiasVencimiento($cliente, $tipo, $numero, $dias){
  	global $sqlca;
  	$result = $sqlca->functionDB("modificar_dias_vencimiento('".trim($cliente)."','".trim($tipo)."','".trim($numero)."',".trim($dias).")");
	return OK;
  }
  
}
