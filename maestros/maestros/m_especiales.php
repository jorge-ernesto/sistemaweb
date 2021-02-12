<?php

Class EspecialesModel extends Model{
  
	function ValidarUsuario($login, $clave){
		global $sqlca;
		
		$query=" select ch_login, ch_password 
				 from int_usuarios_passwd where ch_login='".$login."' and ch_password='".md5($login.$clave.$login)."' ";
		//print_r(md5($clave));
		if ($sqlca->query($query)<=0){
		   return $sqlca->get_error();
		}
    
		$listado = array();
		    
		while( $reg = $sqlca->fetchRow())
		{
		   $listado['datos'] = $reg;
		}    
   
    	return (count($listado)>0?true:false);
	}
	
	
	function getRegistroEspecialporDetalle($registroid){
		global $sqlca;
	    $Valores     = explode(' ', $registroid);
	    $tipo    = trim($Valores[0]);
	    $cliente  = trim($Valores[1]);
	    $articulo    = trim($Valores[2]);
	    $fec_inicio = trim($Valores[3]);
	    $fechaDiv = explode("-", $fec_inicio);
  		$fec_inicio = $fechaDiv[0]."/".$fechaDiv[1]."/".$fechaDiv[2];
	    $query="
	    		SELECT 
				  public.fac_precios_clientes.ch_codigo_cliente_grupo,
				  public.fac_precios_clientes.dt_fecha_inicio,
				  public.fac_precios_clientes.art_codigo,
				  public.fac_precios_clientes.nu_preciopactado,
				  public.fac_precios_clientes.ch_tipo_precio,
				  public.fac_precios_clientes.ch_tipo_cliente,
				  public.fac_precios_clientes.ch_cartaref,
				  public.fac_precios_clientes.dt_fecha_fin
				FROM
				  public.fac_precios_clientes
				WHERE
				  public.fac_precios_clientes.ch_tipo_precio = '".$tipo."' AND 
				  public.fac_precios_clientes.ch_codigo_cliente_grupo = '".$cliente."' AND 
				  public.fac_precios_clientes.art_codigo = '".$articulo."' AND 
				  public.fac_precios_clientes.dt_fecha_inicio = '".$fec_inicio."'
	    ";
	    
	    if ($sqlca->query($query)<=0){
		   return $sqlca->get_error();
		}
    
		$listado = array();
		    
		while( $reg = $sqlca->fetchRow())
		{
		   $listado['datos'] = $reg;
		}    
   
    	return $listado;
	}

function autorizarRegistros($id){
	global $sqlca;
	$cond = '';
	//$registroid = EspecialesModel::GenerarRegistroID();
	$Valores = explode(' ', $id);
	$registroid['tipo_precio']    = trim($Valores[0]);
	$registroid['cod_cliente']   = trim($Valores[1]);
	$registroid['cod_articulo']     = trim($Valores[2]);
	$registroid['fec_inicio'] = trim($Valores[3]);
	print_r('ID: '.$id);
	$query=" update fac_precios_clientes set habilitado=true 
	where habilitado=false ";
	$query .= " and ch_codigo_cliente_grupo = '".trim($registroid["cod_cliente"]).
		"' and ch_tipo_precio='".trim($registroid['tipo_precio']).
		"' and art_codigo='".trim($registroid['cod_articulo']).
		"' and dt_fecha_inicio='".trim($registroid['fec_inicio'])."'";
	print_r($query);
	$sqlca->query($query);
	return true;
		
}
	
 function listadoSoloPendientes($criterio = array()){
		global $sqlca;
		$cond = '';
		$cond=" where public.fac_precios_clientes.habilitado=false ";
		if ($criterio["codigo"] != ""){
			$cond .= " and trim(public.int_clientes.cli_codigo) = '".trim($criterio["codigo"])."'";
			$cond .= " order by public.int_articulos.art_codigo, public.fac_precios_clientes.dt_fecha_fin desc";
		}else{
			$cond .= " order by public.int_clientes.cli_razsocial, public.int_articulos.art_codigo, public.fac_precios_clientes.dt_fecha_fin desc";
		}
		$query = "SELECT 
				  public.int_clientes.cli_codigo,
				  public.int_clientes.cli_razsocial,
				  public.int_articulos.art_codigo,
				  public.int_articulos.art_descripcion,
				  public.fac_precios_clientes.dt_fecha_inicio,
				  public.fac_precios_clientes.dt_fecha_fin,
				  public.fac_precios_clientes.nu_preciopactado,
				  public.fac_precios_clientes.ch_cartaref,
				  public.fac_precios_clientes.ch_tipo_precio,
				   public.fac_precios_clientes.habilitado
					FROM
					  public.int_clientes
					  INNER JOIN public.fac_precios_clientes ON (public.int_clientes.cli_codigo = public.fac_precios_clientes.ch_codigo_cliente_grupo)
					  INNER JOIN public.int_articulos ON (public.fac_precios_clientes.art_codigo = public.int_articulos.art_codigo)
					".$cond;
		if ($sqlca->query($query)<=0){
		    return $sqlca->get_error();
		}
    
		$listado[] = array();
		    
		while( $reg = $sqlca->fetchRow()){
		        $listado['datos'][] = $reg;
		}    
   
    	return $listado;
	}
	
	
  function tmListado($criterio=array())
  {
		    global $sqlca;
		    $cond = '';
		    if ($criterio["codigo"] != ""){
		      $cond = " where trim(public.int_clientes.cli_codigo) = '".trim($criterio["codigo"])."'";
		      $cond .= ($criterio["todos"] == "S")?"":" ";
		      $cond .= " ORDER BY  public.int_articulos.art_codigo, public.fac_precios_clientes.dt_fecha_fin DESC ";
		    }else {
		    	//$cond=" ORDER BY public.int_clientes.cli_razsocial desc, public.int_articulos.art_codigo";
		    	  	if ($criterio["todos"] == "S"){
			    		$cond .= " ORDER BY public.int_clientes.cli_razsocial , public.fac_precios_clientes.dt_fecha_fin DESC";
			    	}else {
			    		$cond .= " AND public.fac_precios_clientes.dt_fecha_fin='2999/01/01' ORDER BY public.int_clientes.cli_razsocial";
			    	}
		    	
		    }
		    
   
    		$query = "SELECT 
				  public.int_clientes.cli_codigo,
				  public.int_clientes.cli_razsocial,
				  public.int_articulos.art_codigo,
				  public.int_articulos.art_descripcion,
				  public.fac_precios_clientes.dt_fecha_inicio,
				  public.fac_precios_clientes.dt_fecha_fin,
				  public.fac_precios_clientes.nu_preciopactado,
				  public.fac_precios_clientes.ch_cartaref,
				  public.fac_precios_clientes.ch_tipo_precio,
				   public.fac_precios_clientes.habilitado
					FROM
					  public.int_clientes
					  INNER JOIN public.fac_precios_clientes ON (public.int_clientes.cli_codigo = public.fac_precios_clientes.ch_codigo_cliente_grupo)
					  INNER JOIN public.int_articulos ON (public.fac_precios_clientes.art_codigo = public.int_articulos.art_codigo)
					".$cond;
	     
	         $resultado_1 = $sqlca->query($query);
	         $numrows = $sqlca->numrows();
echo $query;
		    if ($sqlca->query($query)<=0){
		      return $sqlca->get_error();
		    }
    
		    $listado[] = array();
		    
		    while( $reg = $sqlca->fetchRow())
		    {
		        $listado['datos'][] = $reg;
		    }    
   
    		return $listado;
  }

function getClientes($codigo)
  {
    global $sqlca;
    $cbArray = array();
    //if ($codigo!=''){
//    	$cond = " and int_clientes.cli_codigo ~ '".trim($codigo)."'";
    	$cond = " and int_clientes.cli_codigo = '".trim($codigo)."'";
    //}
    $query = "SELECT 
			  public.int_clientes.cli_codigo,
			  public.int_clientes.cli_razsocial
			FROM
			  public.int_clientes
			WHERE
			  public.int_clientes.cli_tipo = 'AC'
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
  
  function getArticulos($codigo)
  {
    global $sqlca;
    $cbArray = array();
    //if ($codigo!=''){
    	$cond = " where public.int_articulos.art_codigo ~ '".$codigo."'";
    //}
    $query = "SELECT 
			  public.int_articulos.art_codigo,
			  public.int_articulos.art_descripcion
			FROM
			  public.int_articulos
			".$cond."
			ORDER BY
			  public.int_articulos.art_descripcion DESC";
             //"ORDER BY doc.num_descdocumento";
             
    if ($sqlca->query($query)<=0)
      return $cbArray;
      
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result['art_codigo'])] = $result["art_descripcion"];
    }

    //ksort($cbArray);
    return $cbArray;
  }
  
  
  
function guardar($registro){
	global $sqlca;
	
	if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") {
	 	$registroid = EspecialesModel::GenerarRegistroID();
	 	if ($sqlca->perform('fac_precios_clientes', $registro, 'update', "ch_tipo_precio='".$registroid['tipo_precio']."' AND ch_codigo_cliente_grupo='".$registroid['cod_cliente']."' AND art_codigo='".$registroid['cod_articulo']."' AND dt_fecha_inicio='".$registroid['fec_inicio']."'")>=0){
      	}else { return $sqlca->get_error(); }
      
	}else {
//		$valor_tmp = explode('/',$registro['dt_fecha_inicio']);
//	    $registro['dt_fecha_inicio']=$valor_tmp[1].'/'.$valor_tmp[0].'/'.$valor_tmp[2];
	    $cadena="fn_insertar_precio_especial('".
	    						$registro['ch_tipo_precio']."','".
	    						$registro['ch_codigo_cliente_grupo']."','".
	    						$registro['art_codigo']."',".
	    						"to_date('".$registro['dt_fecha_inicio']."','DD/MM/YYYY'),".
	    						$registro['nu_preciopactado'].",'".
	    						$_SESSION['auth_usuario']."',".
	    						"to_date('".date('d/m/Y')."','DD/MM/YYYY'),'".
	    						$registro['ch_cartaref']."','".
	    						$registro['ch_tipo_cliente']."')";
	   if($sqlca->functionDB($cadena)){
			return 'OK';
		}
	}
	 return 'OK';
}

function eliminar(){
	global $sqlca;
	//if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") {
	 	$registroid = EspecialesModel::GenerarRegistroID();
	 	$cadena="fn_eliminar_precio_especial('".
	 				$registroid['tipo_precio']."','".
	 				$registroid['cod_cliente']."','".
	 				$registroid['cod_articulo']."','".
	 				$registroid['fec_inicio']."','".
	 				$registroid['fec_fin']."')";
	 	//print_r($cadena);
	 	if($sqlca->functionDB($cadena)){
			return 'OK';
		}
	return 'OK';
	 	/*if ($sqlca->perform('fac_precios_clientes', $registro, 'update', "ch_tipo_precio='".$registroid['tipo_precio']."' AND ch_codigo_cliente_grupo='".$registroid['cod_cliente']."' AND art_codigo='".$registroid['cod_articulo']."' AND dt_fecha_inicio='".$registroid['fec_inicio']."'")>=0){
      	}else { return $sqlca->get_error(); }*/
}

function GenerarRegistroID()
  {
  	//$Valores=array();
  	//$registroid=array();
  	//$vari = $_REQUEST['registroid'];
  	//print_r('registro 2 es: '.$_REQUEST['registroid']);
   if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") {
		$Valores = explode(' ', $_REQUEST["registroid"]);
		$registroid['tipo_precio']    = trim($Valores[0]);
		$registroid['cod_cliente']   = trim($Valores[1]);
		$registroid['cod_articulo']     = trim($Valores[2]);
		$registroid['fec_inicio'] = trim($Valores[3]);
		$registroid['fec_fin'] = trim($Valores[4]);
	    //print_r($registroid);
	    return $registroid;
    }
  }
}

