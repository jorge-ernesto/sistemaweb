<?php
 require("../clases/funciones.php");
 include("../include/functions.php");
 
Class ValesModel extends Model{
  function getValesporTarjeta($tipovale, $inicio, $fin){
		global $sqlca;
		$listado = array();
		$query = "select val.ch_tipovale, val.ch_numero_inicio, val.ch_numero_fin, val.ch_numerovale, val.ch_fecha_consumo, s.ch_nombre_breve_sucursal, val.nu_importe, val.ch_bloqueado, val.ch_consumido 
		from val_ta_tarjeta_vale_det val left join int_ta_sucursales s on trim(s.ch_sucursal)=trim(val.ch_estacion) where val.ch_tipovale='".$tipovale."' and val.ch_numero_inicio='".$inicio."' and val.ch_numero_fin='".$fin."' order by val.ch_numero_inicio;";
		$sqlca->query($query);
	    while($reg = $sqlca->fetchRow()){
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
  
  function getlistadoVales($criterio, $tipo){
  	 global $sqlca;
     if ($tipo=='0'){
     	$query="select cli.cli_codigo, cli.cli_razsocial, tipo.ch_descripcion_corta, cab.ch_numero_inicio, cab.ch_numero_fin, cab.dt_fecha_entrega, cab.flg_replicacion, tipo.ch_tipovale, cab.ch_tarjeta 
     			from val_ta_tarjeta_vale_cab cab inner join int_clientes cli on cli.cli_codigo=cab.ch_cliente 
     			inner join val_ta_tipovale tipo on tipo.ch_tipovale=cab.ch_tipovale ";
     	if ($criterio!='')
     		$query .= "where cli.cli_codigo='".$criterio."' order by tipo.ch_descripcion_corta";
     	else
     		$query .= "order by tipo.ch_descripcion_corta";
     	
     }else{
     	$query="select cli.cli_codigo, cli.cli_razsocial, tipo.ch_descripcion_corta, cab.ch_numero_inicio, cab.ch_numero_fin, cab.dt_fecha_entrega, cab.flg_replicacion, tipo.ch_tipovale, cab.ch_tarjeta
     			from val_ta_tarjeta_vale_cab cab inner join int_clientes cli on cli.cli_codigo=cab.ch_cliente 
     			inner join val_ta_tipovale tipo on tipo.ch_tipovale=cab.ch_tipovale ";
     	if ($criterio!='')
     		$query .= "where tipo.ch_tipovale='".$criterio."' order by cli.cli_razsocial";
     	else
     		$query .= "order by cli.cli_razsocial";
     	
     }
     print_r($query);
     $sqlca->query($query);
     $listado['datos'] = array();
	 while( $reg = $sqlca->fetchRow()){
	       $listado['datos'][] = $reg;
	 }    
	 return $listado;
  }
  
  function bloquear($tipovale, $inicio, $fin, $vale){
  	global $sqlca;
  	$query = "update val_ta_tarjeta_vale_det set ch_bloqueado='S', flg_replicacion=0 where ch_tipovale='".trim($tipovale)
  	."' and ch_numero_inicio='".trim($inicio)."' and ch_numero_fin='".trim($fin)."' and ch_numerovale='"
  	.trim($vale)."'";
  	$sqlca->query($query);
  	return 1;
  }
  
  function Desbloquear($tipovale, $inicio, $fin, $vale){
  	global $sqlca;
  	$query = "update val_ta_tarjeta_vale_det set ch_bloqueado='N', flg_replicacion=0 where ch_tipovale='".trim($tipovale)
  	."' and ch_numero_inicio='".trim($inicio)."' and ch_numero_fin='".trim($fin)."' and ch_numerovale='"
  	.trim($vale)."'";
  	$sqlca->query($query);
  	return 1;
  }
  
  function EliminarValesTarjeta($cliente, $tipovale, $inicio, $fin){
  	global $sqlca;
  	$resultado = $sqlca->functionDB("fn_eliminar_vales_tarjeta('".$cliente."','".$tipovale."','".$inicio."','".$fin."');");
  	if ($resultado=='1'){
  		$funcion = new class_funciones;
  		$conector_repli_id = $funcion->conectar("","","acosa_backups","","");
  		$almacenes = ValesModel::ObtenerEstaciones();
  		$v_sql = "SELECT fn_eliminar_vales_tarjeta('".$cliente."','".$tipovale."','".$inicio."','".$fin."');";
  		foreach ($almacenes as $k => $v){
  			$Datos['Ip_Estacion']  = ObtenerIPAlmacen($conector_repli_id, trim($k));
    		$Datos['Cod_Estacion'] = $k;
    		$SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $v_sql, $Datos);
  		}
  		sleep(30);
  		return '';
  	}else{
  		return 'Error: No se pudo eliminar porque hay vales ya consumidos en ese rango.';
  	}
  }
  
  function ObtenerEstaciones(){
  	global $sqlca;
  	$query = "SELECT ch_sucursal, ch_nombre_sucursal, ch_nombre_breve_sucursal, ch_direccion, 
       ch_distrito, ch_telefonos, ch_tipo_sucursal, ch_propiedad_acosa
  	FROM int_ta_sucursales;";
  	$sqlca->query($query);
  	$cbArray = array();
  	while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["ch_sucursal"])] = $result["ch_nombre_sucursal"];
    }
    return $cbArray;
  }
  
  function guardarCabecera($datos){
  	 global $sqlca;
  	 $datos['dt_fecha_entrega']='now()';
  	 $datos['ch_usuario']='WEB';
  	 $datos['dt_fecha_update']='now()';
  	 $datos['dt_fecha_replicacion']='now()';
  	 $datos['flg_replicacion']='0';
  	  /*verificar si existe en ese rango de numeros para ese tipo alguno ya existente*/  	 
  	 $resultado = $sqlca->functionDB("fn_verificar_rango_vales('".$datos['ch_tipovale']."','".$datos['ch_numero_inicio']."','".$datos['ch_numero_fin']."');");
  	 if ($resultado!='0'){
  	 	return 'Error: Hay Vales ya asignados dentro de ese rango, mueva el numerador de vales a '.substr('000000000000'.($resultado+1),strlen('000000000000'.($resultado+1))-10);
  	 }else{
  	 	if ($sqlca->perform('val_ta_tarjeta_vale_cab', $datos, 'insert')>=0){
	    } else { return $sqlca->get_error(); }
	  	 
	  	$query_funcion = "select fn_registrar_vales('".$datos['ch_tipovale']."','".$datos['ch_numero_inicio']."','".$datos['ch_numero_fin']."');" ;
	  	 
	    if ($sqlca->query($query_funcion) < 0){
	    }else { return $sqlca->get_error();}
  	 }
  	 return '';
  }
    
  function getTipoVales($codigo){
  	 global $sqlca;
  	 print_r('model:'.$codigo.'fin');
    $cbArray = array();
    $query = "SELECT ch_tipovale, ch_descripcion_corta FROM val_ta_tipovale ".
    $query .= ($codigo!=''?' WHERE '.$codigo:'')." and ch_tipovale!='00' ORDER BY 2";
    $sqlca->query($query);
    print_r($query);
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["ch_tipovale"])] = $result["ch_tipovale"].' '.$result["ch_descripcion_corta"];
    }
    ksort($cbArray);
    return $cbArray;
  }
   
  function TarjetasMagneticas($codigo){
  	global $sqlca;
  	$query = "select cli_grupo from int_clientes where cli_codigo='".$codigo."'";
    $sqlca->query($query);
    $result2 = $sqlca->fetchRow();
    if (is_null($result2['cli_grupo'])){
    	$result['maximo']="NO_GRUPO";
    }else{
    	/*SELECCIONA LA TARJETA DE VALES DEL CLIENTE*/
    	$query2 = "select numtar from pos_fptshe1 where codcli='".$codigo."' and substring(numtar from 8 for 3)='000'";
    	$sqlca->query($query2);
    	$result3 = $sqlca->fetchRow();
    	if (is_null($result3['numtar'])) $result['maximo']="NO_TARJETA";
    	else $result['maximo']=$result3['numtar'];	
    }
  	return $result['maximo'];
  }
  
  function getValeInicial($numero){
  	global $sqlca;
  	$query = "select num_numactual from int_num_documentos where num_tipdocumento='VA' and trim(num_seriedocumento)='".$numero."'";
  	$sqlca->query($query);
  	$result = $sqlca->fetchRow();
  	return $result['num_numactual'];
  }
}

?>