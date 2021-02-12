<?php
  // Modelo para Eliminacion de Cuentas por cobrar

Class PreciosModel extends Model{
  
  //Otras funciones para consultar la DB

  function tmListado($filtro)
  {
    global $sqlca;
    $cond = '';
    //print_r($filtro);
    if ($filtro["codigo"] != "")
    {
      $cond .= " and trim(l.art_codigo) = '".pg_escape_string($filtro["codigo"])."' ";
    }
    if ($filtro["radio"] != ""){
    	$cond .= " and  l.pre_lista_precio='".$filtro["radio"]."'";
    }else {
    	$cond .= " and (l.pre_lista_precio='90' or l.pre_lista_precio='91')";
    }
	$query = "  select l.pre_lista_precio, iif(l.pre_moneda='01','SOLES','DOLARES') AS pre_moneda, art.art_descripcion, l.pre_precio_act1, l.art_codigo 
					from fac_lista_precios l, int_articulos art where l.art_codigo=art.art_codigo ".$cond;
	 
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    while( $reg = $sqlca->fetchRow())
	{
		$listado['datos'][] = $reg;
	}    
   print_r($query);
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
  
  function ArticulosCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT ".
                    "art_codigo, ".
		    "art_descripcion ".
		    "FROM int_articulos  where ".$condicion.' ORDER BY art_codigo';
     
    if ($sqlca->query($query)<=0)
      return $cbArray;
      
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["art_codigo"])] = $result["art_codigo"].' '.$result["art_descripcion"];
    }
    ksort($cbArray);
    return $cbArray;
  }

  function grabarRegistro($lista, $cliente, $articulo, $precio, $usuario){
  	global $sqlca;
  	$query ="ventas_registro_lista_precio('".$lista."','".$cliente."','".$articulo."',".$precio.",'".$usuario."');";
  	print_r($query);
  	$Monto = $sqlca->functionDB($query);
  	return OK;
  }
}
