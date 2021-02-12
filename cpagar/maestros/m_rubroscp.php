<?php
  // Modelo para Tarjetas Magneticas

Class RubrosCPModel extends Model{

  function validarCodigo($codigo)
  {
    global $sqlca;
    if(!empty($codigo))
    {
	$query = "SELECT trim(ch_codigo_rubro) ".
		 " FROM cpag_ta_rubros  ".
		 "WHERE ch_codigo_rubro ~ '".$codigo."' ".
		 " ORDER BY ch_codigo_rubro DESC LIMIT 1";
	//echo "QUERY : $query \n";
	$result = $sqlca->query($query);
	$numrows = $sqlca->numrows();
	
	if($numrows > 0)
	{
	    $rows = $sqlca->fetchRow();
	    if($codigo==$rows[0])
	    {
	       return '<blink>El C&oacute;digo ingresado ya existe, ingrese otro.<blink>';
	    }
	    return 'El &uacute;ltimo c&oacute;digo ingresado es : '.$rows[0].' ';
	}
    }else{
        return '<blink>Debe Ingresar el C&oacute;digo.</blink>';
    }
  }
  
  function validarCodigoShell($codigo)
  {
    global $sqlca;
    if(!empty($codigo))
    {
        if($_REQUEST['registroid'])
        {
            $AddSel = ", cli_codigo ";
            $AddWhere = " AND cli_codigo='".$_REQUEST['registroid']."' ";
        }
	$query = "SELECT trim(cli_grupo) 
	          ".$AddSel."
		  FROM int_clientes  
		  WHERE cli_grupo ~ '".$codigo."' 
		  ORDER BY cli_grupo DESC LIMIT 1";
		  
	//echo "QUERY : $query \n";
	$result = $sqlca->query($query);
	$numrows = $sqlca->numrows();	
	if($numrows > 0)
	{
	    $rows = $sqlca->fetchRow();
	    if($_REQUEST['registroid'] && $codigo==$rows[0] && $_REQUEST['registroid']==trim($rows[1]))
	    {
	       return '';
	    }elseif($_REQUEST['registroid']!=trim($rows[1])){
	       return '<blink>El C&oacute;digo Shell ingresado ya existe, ingrese otro.<blink>';
	    }
	    if($codigo==$rows[0])
	    {
	       return '<blink>El C&oacute;digo Shell ingresado ya existe, ingrese otro.<blink>';
	    }
	}
    }else{
        return '<blink>Debe Ingresar el C&oacute;digo Shell.</blink>';
    }
  }

  function validarRuc($codigo)
  {
    global $sqlca;
    if(!empty($codigo))
    {
	$query = "SELECT trim(cli_ruc) ".
		 " FROM int_clientes  ".
		 "WHERE cli_ruc = '".$codigo."' ".
		 " ORDER BY cli_ruc DESC LIMIT 1";
	//echo "QUERY : $query \n";
	$result = $sqlca->query($query);
	$numrows = $sqlca->numrows();	
	if($numrows > 0)
	{
	    $rows = $sqlca->fetchRow();
	    if($codigo==$rows[0])
	    {
	       return '<blink>El Ruc Digitado ya existe, ingrese otro.<blink>';
	    }
	}
    }else{
        return '<blink>Debe Ingresar el Nro. de RUC</blink>';
    }
  }

  function guardarRegistro($datos, $datosxml = ""){
    global $sqlca;
    $ip_remoto = $_SERVER["REMOTE_ADDR"];
    $datos['cli_fecactualiz'] = 'now()';
    $datos['fecha_replicacion'] = 'now()';
    $datos['flg_replicacion'] = '0';
    $datos['cli_fpago_credito'] = substr(trim($datos['cli_fpago_credito']),-2,2);
    //    echo "SUBSTR : ".substr('000011',-2,2)."";

    if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") 
    {
      //actualizar registro
      $registroid = trim($_REQUEST["registroid"]);
      if ($sqlca->perform('cpag_ta_rubros', $datos, 'update', "cli_codigo='$registroid'")>=0){
      } else { return $sqlca->get_error(); }
      //@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 
      $query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;
      if ($sqlca->query($query_funcion) < 0){
      }else { return $sqlca->get_error();}
      //@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 

      return OK;
    } 
    else 
    {
      if ($sqlca->perform('cpag_ta_rubros', $datos, 'insert')>=0){      
      }else { return $sqlca->get_error(); }
      //@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 
      $query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;
      if ($sqlca->query($query_funcion) < 0){
      }else { return $sqlca->get_error();}
      //@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 

      return OK;
    }

    return '<error>Error</error>';
  }  

  function recuperarRegistroArray($registroid){
    global $sqlca;
    $registro = array();
/*
cli_codigo | cli_razsocial | cli_rsocialbreve | cli_grupo | cli_direccion | cli_ruc |
cli_moneda | cli_fpago_credito | cli_fecultventa | cli_telefono1 | cli_telefono2 |
cli_telefono3 | cli_contacto | cli_email | cli_fecactualiz | cli_estado | cli_trasmision
cli_tipo | cli_creditosol | cli_creditodol | cli_salsol | cli_saldol | flg_replicacion
fecha_replicacion | cli_anticipo | cli_comp_direccion | cli_distrito |
cli_lista_precio | cli_mantenimiento
*/

    $query = "SELECT ch_codigo_rubro, ".
                    "ch_descripcion, ".
		    "ch_descripcion_breve, ".
		    "plc_codigo, ".
		    "ch_tipo_item, ".
		    "ch_percepcion_tipo, ".
		    "ch_percepcion_porcentaje, ".
		    "ch_detraccion_tipo, ".
		    "ch_detraccion_porcentaje ".
              "FROM cpag_ta_rubros ".
	      //"WHERE trim(cli_codigo)=trim(cli_codigo) ".
	      "WHERE ch_codigo_rubro='".trim($registroid)."'";
	      
    echo "QUERY : $query";      
    $sqlca->query($query);

    while( $reg = $sqlca->fetchRow()){$registro = $reg;}
    
    return $registro;
  }

  function TipoItemCBArray()
  {
   global $sqlca;
     $query = "SELECT tab_elemento, 
                     tab_descripcion 
              FROM int_tabla_general 
              WHERE tab_tabla = '21' 
              AND tab_elemento<>'000000' 
              ORDER BY tab_descripcion";
    //echo "QUERY : $query \n";
    $cbArray = array();
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result[0])] = $result[1];
    }
    return $cbArray;
  }

  function eliminarRegistro($idregistro){
    global $sqlca;
    //$query = "DELETE FROM int_clientes WHERE cli_codigo = '$idregistro';";
    //$sqlca->query($query);
    if ($sqlca->perform('cpag_ta_rubros  ', ' ', 'delete', "ch_codigo_rubro='$idregistro'")>=0){
    } else { return $sqlca->get_error(); }
    return OK;
  }

  //Otras funciones para consultar la DB

  function tmListado($filtro=array(),$pp, $pagina)
  {
    global $sqlca;
    $cond = '';
    if ($filtro["codigo"] != ""){
      $cond = " WHERE trim(ch_codigo_rubro)||''||trim(ch_descripcion)||''||trim(plc_codigo) ~ '".$filtro["codigo"]."' ";
    }
    

    $query = "SELECT  ch_codigo_rubro, ".
                     "ch_descripcion, ".
		     "ch_descripcion_breve, ".
		     "plc_codigo, ".
		     "ch_tipo_item, ".
		     "ch_percepcion_tipo, ".
		     "ch_detraccion_tipo ".
             "FROM cpag_ta_rubros ".
	     " ".$cond." ".
	     "ORDER BY ch_codigo_rubro, ch_descripcion ";
         $resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
	if($pp && $pagina)
	{
	//echo "ENTRO 2 \n REGPP : $pp \n PAG : $pagina\n";
	$paginador = new paginador($numrows,$pp, $pagina);
	}else{
	//echo "ENTRO 2 ELSE\n";
	//echo "ENTRO 2 ELSE\n REGPP : $pp \n PAG : $pagina\n";
	$paginador = new paginador($numrows,100,0);
	}
	$listado2['partir'] = $paginador->partir();
	$listado2['fin'] = $paginador->fin();
	
	//$listado2['ultima_pagina'] = $paginador->numero_paginas();
	$listado2['numero_paginas'] = $paginador->numero_paginas();
	$listado2['pagina_previa'] = $paginador->pagina_previa();
	$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
	$listado2['pp'] = $paginador->pp;
	$listado2['paginas'] = $paginador->paginas();
	$listado2['primera_pagina'] = $paginador->primera_pagina();
	$listado2['ultima_pagina'] = $paginador->ultima_pagina();
	
        //print_r($listado2);
        if ($pp > 0)
	    $query .= "LIMIT " . pg_escape_string($pp) . " ";
	if ($pagina > 0)
	    $query .= "OFFSET " . pg_escape_string($paginador->partir());
	    
   // echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    
    $listado[] = array();
    //$listado['datos_pag']
    while( $reg = $sqlca->fetchRow())
    {
        $listado['datos'][] = $reg;
    }    
    
    
    $listado['paginacion'] = $listado2;
    //print_r($listado);
    
    return $listado;
  }

  function FormaPagoCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = '96' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    }
    ksort($cbArray);
    return $cbArray;
  }

  function ListaPreciosCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = 'LPRE' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    }
    ksort($cbArray);
    //print_r($cbArray);
    return $cbArray;
  }

  function DistritoCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = '02' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    }
    ksort($cbArray);
    return $cbArray;
  }
  
  function RubrosCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = 'RCPG' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    }
    ksort($cbArray);
    return $cbArray;
  }
  
  function CuentasCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = '03' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    }
    ksort($cbArray);
    //print_r($cbArray);
    return $cbArray;
  }
  
  function TipoCtaBanCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = 'TCBC' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    }
    ksort($cbArray);
    //print_r($cbArray);
    return $cbArray;
  }

  function AgregarCuenta($dato, $dato2, $dato3, $dato4, $dato5, $contador)
  {
  $datos = $GLOBALS['CUENTAS'];
    if($dato!='')
    {
        //echo " CONTADOR : ".($contador)."\n";
        for($i=0;$i<$contador+1;$i++)
        {
	    $T = $datos[$i];
	    if($T['codigo_banco']==$dato && $T['nro_cuenta_bancaria']==$dato3)
	    { 	
		    //$agregar = false;
		    $mensaje = "Este articulo ya ha sido ingresado antes";
		    //echo "MENSAJE : $mensaje \n";
            }
	}
	if(!$mensaje)
	{
	$var = $contador+1;
	    $datos[$var]['codigo_banco']                   = $dato;
	    $datos[$var]['descrip_banco']                  = $dato2;
	    $datos[$var]['nro_cuenta_bancaria']            = $dato3;
	    $datos[$var]['tipo_cuenta_bancaria']           = $dato4;
	    $datos[$var]['descrip_tipo_cuenta_bancaria']   = $dato5;
	}
    }
    return $datos;
  }

}
