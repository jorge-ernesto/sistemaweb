<?php
  // Modelo para Eliminacion de Cuentas por cobrar

Class ContClienteModel extends Model{
  
	
function guardar($datos){
	global $sqlca;
	if ($_REQUEST['registroid']!=''){
		$sqlca->perform('control_cliente', $datos, 'update', "trim(cliente)||trim(id)='".$_REQUEST['registroid']."'");
		return 100;
	}else{
		return $sqlca->functionDB("registrar_control_cliente('".$datos['cliente']."','".$datos['tipo_combustible']."','".$datos['lim_galones']."','".$datos['sal_galones']."','".$datos['lim_importe']."','".$datos['sal_importe']."','".$datos['fec_inicio']."','".$datos['fec_fin']."');");		
	}
	
}
  //Otras funciones para consultar la DB
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

function listado($criterio=array()){
	global $sqlca;
	$query = "SELECT id, cliente, art.art_descripcion, lim_galones, sal_galones, lim_importe, sal_importe, 
     		fec_inicio, fec_fin, estado, ult_consumo
  			FROM control_cliente c left join control_articulos art on art.art_codigo=c.tipo_combustible 
 			where cliente='".$criterio['codigo']."'";
		if ($sqlca->query($query)<=0){
		    return $sqlca->get_error();
		}
    
		$listado[] = array();
		    
		while( $reg = $sqlca->fetchRow()){
		        $listado['datos'][] = $reg;
		}    
   
    	return $listado;
}

function eliminarCriterio($codigo){
	 global $sqlca;
	 $query="delete from control_cliente where trim(cliente)||trim(id)='".trim($codigo)."'";
	 $sqlca->query($query);
	 return OK;
}

function getArticulos($codigo){
    global $sqlca;
    $cbArray = array();
    //if ($codigo!=''){
    	$cond = " where public.control_articulos.art_codigo ~ '".$codigo."'";
    //}
    $query = "SELECT 
			  public.control_articulos.art_codigo,
			  public.control_articulos.art_descripcion
			FROM
			  public.control_articulos
			".$cond."
			ORDER BY
			  public.control_articulos.art_descripcion DESC";
             //"ORDER BY doc.num_descdocumento";
             
    if ($sqlca->query($query)<=0)
      return $cbArray;
      
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result['art_codigo'])] = $result["art_descripcion"];
    }

    //ksort($cbArray);
    return $cbArray;
  }

function getClientes($codigo)
  {
    global $sqlca;
    $cbArray = array();
    //if ($codigo!=''){
    	$cond = " int_clientes.cli_codigo ~ '".trim($codigo)."'";
    //}
    $query = "SELECT 
			  public.int_clientes.cli_codigo,
			  public.int_clientes.cli_razsocial
			FROM
			  public.int_clientes
			WHERE
			  
			  ".$cond."
			ORDER BY
			  public.int_clientes.cli_razsocial";
             //"ORDER BY doc.num_descdocumento";
             
    if ($sqlca->query($query)<=0)
      return $cbArray;
      
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result['cli_codigo'])] = $result["cli_razsocial"];
    }

    //ksort($cbArray);
    return $cbArray;
  }
  
  function devolverCriterioControl($codigo){
  	global $sqlca;
  	$query = "SELECT id, cliente, lim_galones, sal_galones, lim_importe, sal_importe, 
        	  fec_inicio, fec_fin, estado, fec_estado, tipo_combustible, ult_consumo
 			  FROM control_cliente where trim(cliente)||trim(id)='".trim($codigo)."'";
  	$sqlca->query($query);
  	$result = $sqlca->fetchRow();
  	return $result;
  }
  
}
?>