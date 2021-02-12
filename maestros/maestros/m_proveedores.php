<?php
  // Modelo para Tarjetas Magneticas

Class ProveedoresModel extends Model{

  function CIFKey() {
    global $sqlca;
    $registro = array();
    $query = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'ocs_tid_apikey';";

    $sqlca->query($query);

    $reg = $sqlca->fetchRow();
    if ($reg && is_array($reg) && isset($reg[0])) {
      return $reg[0];
    }
    return NULL;
  }
  
  function validarCodigo($codigo)
  {
    global $sqlca;
    if(!empty($codigo))
    {
	$query = "SELECT trim(pro_codigo) ".
		 " FROM int_proveedores  ".
		 "WHERE pro_codigo ~ '".$codigo."' ".
		 " ORDER BY pro_codigo DESC LIMIT 1";
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
        return 'Debe Ingresar el C&oacute;digo.';
    }
  }
  
  function validarRuc($codigo)
  {
    global $sqlca;
    if(!empty($codigo))
    {
	$query = "SELECT trim(pro_ruc) ".
		 " FROM int_proveedores  ".
		 "WHERE pro_ruc = '".$codigo."' ".
		 " ORDER BY pro_ruc DESC LIMIT 1";
	//echo "QUERY : $query \n";
	$result = $sqlca->query($query);
	$numrows = $sqlca->numrows();	
	if($numrows > 0)
	{
	    $rows = $sqlca->fetchRow();
	    if($codigo==$rows[0])
	    {
	       return '<blink>El Ruc digitado ya existe, ingrese otro.<blink>';
	    }
	}
    }else{
        return '<blink>Debe Ingresar el Nro. de RUC</blink>';
    }
  }

  function guardarRegistro($datos, $datosxml){
    global $sqlca;
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
    $ip_remoto = $_SERVER["REMOTE_ADDR"];
    $datos['pro_fecactualiz'] = 'now()';
    $datos['fecha_replicacion'] = 'now()';
    $datos['flg_replicacion'] = '0';
    //$datos['pro_forma_pago'] = round($datos['pro_forma_pago']);
    $datos['pro_creditosol'] = '0';
    $datos['pro_creditodol'] = '0';
    //pro_creditosol
    //pro_creditodol
    $datos2 = $_REQUEST['datos2'];
    //echo "Ctas_Corrientes : ".$datos2['Ctas_Corrientes']."\n";
    if(empty($datos2))
    {
        //echo "ESTA VACIA";
        $datosxml = "";
        $datos['pro_xml_bancos'] = 'null';
    }
    if($datos['pro_tipo'] == "N")
    {
        $datos_persona = $_REQUEST['d_natural'];
        $pro_ap_paterno = $datos_persona['pro_ap_paterno']?$datos_persona['pro_ap_paterno']:'';
        $pro_ap_materno = $datos_persona['pro_ap_materno']?$datos_persona['pro_ap_materno']:'';
        $pro_pri_nombre = $datos_persona['pro_pri_nombre']?$datos_persona['pro_pri_nombre']:'';
        $pro_seg_nombre = $datos_persona['pro_seg_nombre']?$datos_persona['pro_seg_nombre']:'';

        $datos_final = "{".$pro_ap_paterno.",".$pro_ap_materno.",".$pro_pri_nombre.",".$pro_seg_nombre."}";
        $datos['pro_datos_natural'] = $datos_final;
        
        $dato_razonsoc_1 = $pro_ap_paterno." ".$pro_ap_materno." ".$pro_pri_nombre." ".$pro_seg_nombre;
        $dato_razonsoc_2 = $pro_ap_paterno." ".$pro_ap_materno." ".$pro_pri_nombre;
        $dato_razonsoc_3 = $pro_ap_paterno." ".$pro_ap_materno;
        $dato_razonsoc_4 = $pro_ap_paterno;
        if(strlen($dato_razonsoc_1)<=40)
        {
            $datos['pro_razsocial'] = $dato_razonsoc_1;
        }
        elseif(strlen($dato_razonsoc_2)<=40)
        {
            $datos['pro_razsocial'] = $dato_razonsoc_2;
        }
        elseif(strlen($dato_razonsoc_3)<=40)
        {
            $datos['pro_razsocial'] = $dato_razonsoc_3;
        }
        else
        {
            $datos['pro_razsocial'] = $dato_razonsoc_4;
        }
        
        if(strlen($dato_razonsoc_1)<=20)
        {
            $datos['pro_rsocialbreve'] = $dato_razonsoc_1;
        }
        elseif(strlen($dato_razonsoc_2)<=20)
        {
            $datos['pro_rsocialbreve'] = $dato_razonsoc_2;
        }
        elseif(strlen($dato_razonsoc_3)<=20)
        {
            $datos['pro_rsocialbreve'] = $dato_razonsoc_3;
        }
        else
        {
            $datos['pro_rsocialbreve'] = $dato_razonsoc_4;
        }
        //echo "APELLIDO PATERNO : ".$datos_final."\n";
    }else{
       $datos['pro_datos_natural']='null';
    }
    
    //$datosxml = $_SESSION["CUENTAS"];
    //print_r($datosxml);
    //pendiente for
    if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") 
    {
    //echo "ENTRO UPDATE\n";
	if($datosxml)
	{
	    foreach($datosxml as $registroID => $reg)
	    {
		//echo "REGISTROID : $registroID => REG : $reg \n";
		ProveedoresModel::ingresarRegistroXml($registroID, $reg,$_REQUEST["registroid"]);
	    }
	}/*else{
	   return OK;
	}*/
       //actualizar registro
      $registroid = trim($_REQUEST["registroid"]);
      if ($sqlca->perform('int_proveedores', $datos, 'update', "pro_codigo='$registroid'")>=0){
        //return "prueba";
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
      if ($sqlca->perform('int_proveedores', $datos, 'insert')>=0)
      {
 	foreach($datosxml as $registroID => $reg)
	{
	    //echo "REGISTROID : $registroID => REG : $reg \n";
	    ProveedoresModel::ingresarRegistroXml($registroID, $reg, $datos["pro_codigo"]);
	}

      } 
      else 
      { return $sqlca->get_error(); }
      
      //@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 
      $query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;
      if ($sqlca->query($query_funcion) < 0){
      }else { return $sqlca->get_error();}
      //@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 

      return OK;
    }

    return '<error>Error</error>';
  }
  function ingresarRegistroXmlVacio($reg,$registroid)
  {
    global $sqlca;
    //$dom = ProveedoresModel::regXmlData(trim($_REQUEST["registroid"]));
    $regid = $sqlca->functionDB("NEXTVAL('s_int_proveedores')");
    $regXml .= '<?php xml version="1.0"?>';
    $regXml .= '<registros>';
    $regXml .= '<reg id="'.$regid.'">'."\n";
    foreach($reg as $campo => $valor){
	if($campo != 'descrip_banco' && $campo != 'descrip_tipo_cuenta_bancaria')
	{
	    $regXml .= '<'.$campo.'>'.$valor.'</'.$campo.'>'."\n";
	}
    }
    $regXml .= '</reg></registros>';
    //echo "REGXML : $regXml\n";
    //$xmlData = str_replace('</registros>', $regXml, $dom->dump_mem(true));
        if (ProveedoresModel::updateTablaXml($registroid, $regXml) < 0){
      return $sqlca->error;
    }

  }
  
  function ingresarRegistroXml($registroID, $reg, $codigoProv)
  {
    global $sqlca;
    //$reg = $_REQUEST["reg"];
    $dom = ProveedoresModel::regXmlData(trim($codigoProv));
    
    if(!$dom)
    {
    //echo "DOM NEW : $dom \n";
        ProveedoresModel::ingresarRegistroXmlVacio($reg,$codigoProv);
        return OK;
    }

    //$dom = ProveedoresModel::regXmlData($_REQUEST["registroid"]);
    if (isset($_REQUEST["registroid"])){ //actualizar registro
      $ctx = xpath_new_context($dom);
      $regx = xpath_eval($ctx,'/registros/reg[@id="'.$registroID.'"]');
      $reg_ns = $regx->nodeset;
      $registro = $reg_ns[0];
      //echo "REGISTRO EXISTE : $registroID \n";
      if($registro){
	$valores = $registro->child_nodes();
	//update valores
	$reg_nuevo = $dom->create_element($registro->tagname);
	$reg_nuevo->set_attribute('id', $registro->get_attribute('id'));
	//unset($reg["id"]);
	foreach($reg as $campo => $valor){
	  if($campo != 'descrip_banco' && $campo != 'descrip_tipo_cuenta_bancaria')
	  {
	    $node = $dom->create_element($campo);
	    $newnode = $reg_nuevo->append_child($node);
	    $newnode->set_content($valor);
	  }
	}
	$registro->replace_node($reg_nuevo);
	$xmlData = $dom->dump_mem(true);
      }else{
        //$existe = $sqlca->functionDB("xpath_string(pro_xml_bancos,'//registros/reg[\"".trim($registroID)."\"]') AS registro FROM int_proveedores WHERE pro_codigo='".$_REQUEST["registroid"]."'");
        //echo "EXISTE : $existe";
        //if (trim($existe) != '')
        //return 'Error: '.$registroID.' ya existe en int_proveedores ';
	$regid = $sqlca->functionDB("NEXTVAL('s_int_proveedores')");
	$regXml = '<reg id="'.$regid.'">'."\n";
	foreach($reg as $campo => $valor){
	    if($campo != 'descrip_banco' && $campo != 'descrip_tipo_cuenta_bancaria')
	    {
		$regXml .= '<'.$campo.'>'.$valor.'</'.$campo.'>'."\n";
	    }
	}
	$regXml .= '</reg></registros>';
	//echo "REGXML : $regXml\n";
	$xmlData = str_replace('</registros>', $regXml, $dom->dump_mem(true));
      }
    }else{ //nuevo registro
      //controlar duplicado por codigo
      $regid = $sqlca->functionDB("NEXTVAL('s_int_proveedores')");
      $regXml = '<reg id="'.$regid.'">'."\n";
      foreach($reg as $campo => $valor){
	if($campo != 'descrip_banco' && $campo != 'descrip_tipo_cuenta_bancaria')
	{
	    $regXml .= '<'.$campo.'>'.$valor.'</'.$campo.'>'."\n";
	}
      }
      $regXml .= '</reg></registros>';
      $xmlData = str_replace('</registros>', $regXml, $dom->dump_mem(true));
   }
    if (ProveedoresModel::updateTablaXml($codigoProv, $xmlData) < 0){
      return $sqlca->error;
    }
    //@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 
    $query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;
    if ($sqlca->query($query_funcion) < 0){
    }else { return $sqlca->get_error();}
    //@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 

    return OK;
  }
  
  function updateTablaXml($codigo, $update_data){
    global $sqlca;
    //echo "UPDATE_DATA : $update_data\n";
    $update_data = array("pro_xml_bancos" => $update_data);
    $sqlca->perform('int_proveedores', $update_data, 'update', "pro_codigo = '$codigo'");
    //@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 
    $query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;
    if ($sqlca->query($query_funcion) < 0){
    }else { return $sqlca->get_error();}
    //@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 

    return 1;
  }

  function eliminarRegistroXml($codigo, $registroID){
    //recuperar data para task
    global $sqlca;
    $result = OK;
    $dom = ProveedoresModel::regXmlData($codigo);
    //eliminar node
    $ctx = xpath_new_context($dom);
    $regx = xpath_eval($ctx,'/registros/reg[@id="'.$registroID.'"]');
    $reg_ns = $regx->nodeset;
    $reg = $reg_ns[0];
    $id = $reg->get_attribute('id');
    if ($id == $registroID){
      $childx = $reg->unlink_node($reg);
      $update_data = $dom->dump_mem(true);
      //actualizar registro
      if (ProveedoresModel::updateTablaXml($codigo, $update_data) < 0)
          return '<error>'.$sqlca->error.'</error>';
    }
    return OK;
  }

  function recuperarRegistroArray($registroid){
    global $sqlca;
    $registro = array();
    $query = "SELECT pro_codigo, ".
                    "pro_razsocial, ".
		    "pro_rsocialbreve, ".
		    "pro_direccion, ".
		    "pro_comp_direcc, ".
		    "pro_ruc, ".
		    "pro_telefono1, ".
		    "pro_moneda, ".
		    "pro_agente_retencion, ".
		    "pro_telefono2, ".
		    "pro_ciiu, ".
		    "pro_forma_pago, ".
		    "pro_distrito, ".
		    "pro_contacto, ".
		    "pro_email, ".
		    "pro_grupo, ".
		    "pro_tipo, ".
		    "pro_xml_bancos, ".
		    "pro_datos_natural ".
              "FROM int_proveedores ".
	      //"WHERE trim(pro_codigo)=trim(pro_codigo) ".
	      "WHERE pro_codigo='".trim($registroid)."'";
    //echo "QUERY : $query";      
    $sqlca->query($query);

    while( $reg = $sqlca->fetchRow()){$registro = $reg;}
    
    return $registro;
  }

  function recuperarRegistrosXml($codigo, $pagina=1){
    //seleccionar data de tabla
    global $sqlca;
    $query = "SELECT * from int_proveedores WHERE pro_codigo='".$codigo."'";
    if ($sqlca->query($query) < 0)
      return '<error>'.$sqlca->error.'</error>';
    $result = $sqlca->fetchRow();
    $registros = str_replace('<?php xml version="1.0"?>','',$result["pro_xml_bancos"]);
    //$campos = str_replace('{', '', $result["tab_campos"]);
    $xml = $registros;
    //echo "XML : $xml \n";
    if (!$dom = domxml_open_mem($xml, DOMXML_LOAD_PARSING + //0
          DOMXML_LOAD_COMPLETE_ATTRS + //8
          DOMXML_LOAD_SUBSTITUTE_ENTITIES + //4
          DOMXML_LOAD_DONT_KEEP_BLANKS //16
          ,$error)) { 
      echo "Error al procesar XML Listado Recupera registro\n";
      return null; }
    return $dom;
  }

  function eliminarRegistro($idregistro){
    global $sqlca;
    //$query = "DELETE FROM int_proveedores WHERE pro_codigo = '$idregistro';";
    //$sqlca->query($query);
    if ($sqlca->perform('int_proveedores  ', ' ', 'delete', "pro_codigo='$idregistro'")>=0){
    } else {
      //return $sqlca->get_error();
      return 'kk';
    }
    return OK;
  }

  //Otras funciones para consultar la DB

  function tmListado($filtro=array(),$pp, $pagina)
  {
    global $sqlca;
    $cond = '';
    if ($filtro["codigo"] != ""){
      $cond = " WHERE trim(pro_codigo)||trim(pro_razsocial)||trim(pro_ruc) ~ '".$filtro["codigo"]."' ";
    }

    $query = "SELECT  pro_codigo, ".
                     "pro_razsocial, ".
		     "pro_rsocialbreve, ".
		     "pro_direccion, ".
		     "pro_ruc, ".
		     "pro_telefono1, ".
		     "pro_moneda ".
             "FROM int_proveedores ".
	     " ".$cond." ".
	     "ORDER BY pro_razsocial, pro_codigo ";
         $resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
	if($pp && $pagina)
	{
	//echo "ENTRO 2\n";
	$paginador = new paginador($numrows,$pp, $pagina);
	}else{
	//echo "ENTRO 2 ELSE\n";
	$paginador = new paginador($numrows,100,0);
	}
	
	$listado2['partir'] = $paginador->partir();
	$listado2['fin'] = $paginador->fin();
	$listado2['primera_pagina'] = $paginador->primera_pagina;
	$listado2['ultima_pagina'] = $paginador->numero_paginas();
	$listado2['numero_paginas'] = $paginador->numero_paginas();
	$listado2['pagina_previa'] = $paginador->pagina_previa();
	$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
	$listado2['pp'] = $paginador->pp;
	$listado2['paginas'] = $paginador->paginas();

        if ($pp > 0)
	    $query .= "LIMIT " . pg_escape_string($pp) . " ";
	if ($pagina > 0)
	    $query .= "OFFSET " . pg_escape_string($paginador->partir());
	    
    echo "QUERY : $query \n";
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
    //print_r($listado2);
    return $listado;
  }

  function CiiuCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    //if ($codigo != "") $condicion = "where cli_codigo~'".pg_escape_string($codigo)."'";
    //$query = "select trim(cli_codigo), cli_razsocial 
      //        from int_clientes $condicion order by cli_razsocial;";
    //SELECT tab_elemento, tab_descripcion FROM int_tabla_general WHERE tab_tabla = 'CIIU' AND tab_elemento<>'000000';
    $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             "WHERE tab_tabla = 'CIIU' AND tab_elemento<>'000000'";
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
  
  function FormaPagoCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT ".
                    "substring(tab_elemento for 2 from length(tab_elemento)-1 ) AS tab_elemento, ".
                    "tab_descripcion  FROM int_tabla_general ".
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
    $query = "SELECT ch_codigo_rubro, ch_descripcion  FROM cpag_ta_rubros ";
             //"WHERE tab_elemento<>'000000'";
    $query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["ch_codigo_rubro"])] = $result["ch_codigo_rubro"].' '.$result["ch_descripcion"];
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

  function regXmlData($codigo, $pagina=1){
    //seleccionar data de tabla
    global $sqlca;
    $query = "SELECT * FROM int_proveedores WHERE pro_codigo='".trim($codigo)."'";
    //echo "QUERY : $query \n";
    if ($sqlca->query($query) < 0)
      return '<error>'.$sqlca->error.'</error>';
    $result = $sqlca->fetchRow();
    if (!$dom = domxml_open_mem($result["pro_xml_bancos"], DOMXML_LOAD_PARSING + //0
          DOMXML_LOAD_COMPLETE_ATTRS + //8
          DOMXML_LOAD_SUBSTITUTE_ENTITIES + //4
          DOMXML_LOAD_DONT_KEEP_BLANKS //16
          ,$error)) { 
      echo "Error al procesar XML Listado SELECT\n";
      return null; }
      //echo "DOM : $dom \n";
    return $dom;
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
		    echo "MENSAJE : $mensaje \n";
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

/*
  public function actualizarProveedor($arrPOST){
      echo "<pre>";
      var_dump($arrPOST);
      echo "</pre>";
  }
  */
}
