<?php
 
Class ResumenModel extends Model{
  
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
  
  function obtenerResumenes($filtro = array()){
	 global $sqlca;
	 $query = "select cc.cli_codigo, cl.cli_razsocial, cc.ch_tipdocumento, 
	 			cc.ch_seriedocumento, cc.ch_numdocumento, cc.dt_fechaemision, 
	 			cc.dt_fechasaldo, cc.ch_moneda, cc.nu_importetotal, cc.nu_importesaldo 
	 			from ccob_ta_cabecera cc, int_clientes cl where cc.cli_codigo=cl.cli_codigo and cc.ch_tipdocumento='22' 
	 			";
	 if ($filtro['codigo']!=''){
	 	$query.=" and trim(cc.ch_seriedocumento)||trim(cc.ch_numdocumento)||trim(cc.cli_codigo) ~ '".trim($filtro['codigo'])."'";
	 }
	
	 $sqlca->query($query);
     $listado['datos'] = array();
	 while( $reg = $sqlca->fetchRow()){
	       $listado['datos'][] = $reg;
	 }    
	
     return $listado;
  }
	
  function eliminarResumen($id){
  	global $sqlca;
  	return $sqlca->functionDB("ccobrar_fn_eliminacion_documentos('".$id."')");
  
  }
}


?>