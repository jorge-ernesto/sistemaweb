<?php
  // Modelo para Tarjetas Magneticas

Class FacturasModel extends Model{

	
  function autorizarFactura($id){
	global $sqlca;
	$arrid = explode(' ',$id);
	if($sqlca->functionDB("ventas_fn_autorizar_factura('".$arrid[0]."', '".$arrid[1]."', '".$arrid[2]."','".$arrid[3]."')")){
    	return OK;
    }
  }
  
  function ModelReportePDF($filtro=array())
  {
	$serie = $filtro['serie'];
    $fecha_ini = $filtro['fecha_ini'];
    $fecha_fin = $filtro['fecha_fin'];
    $codigo = $filtro['codigo'];
    $tipo_doc = $filtro['tipo'];
    $num_doc = $filtro['numero'];
	$tmp = date("d/m/Y");
	global $sqlca;

	if($fecha_ini == $tmp && $fecha_fin == $tmp)
	{	
     	$cond1 .= "AND det.dt_fac_fecha  = '$tmp'";
	}
	if($codigo != '')
	{
		$cond2 .= "AND det.cli_codigo  = '$codigo'";
	}
	if($tipo_doc != '')
	{
		$cond3 .= "AND det.ch_fac_tipodocumento  = '$tipo_doc'";
	}
	if($num_doc != '')
	{
		$cond4 .= "AND det.ch_fac_numerodocumento  = '$num_doc'";
	}
	if($serie != '')
	{
		$cond5 .= "AND det.ch_fac_seriedocumento  = '$serie'";
	}

	$query = "select ".
	          "det.cli_codigo as CLIENTE ".
			  ",cli.cli_razsocial as RAZON_SOCIAL ".
			  ",det.dt_fac_fecha as FECHA".
			  ",det.ch_fac_seriedocumento as SERIE ".
			  ", iif(det.ch_fac_tipodocumento = '10','FACTURA',iif(det.ch_fac_tipodocumento = '20','N/CREDITO',iif(det.ch_fac_tipodocumento = '11', 'N/DEBITO',iif(det.ch_fac_tipodocumento = '35', 'BOL/VENTA',NULL)))) as TIPO ".
			  ", det.ch_fac_numerodocumento as NUMERO ".
              ", det.nu_fac_valorbruto as VALOR_VENTA ".
              ", det.nu_fac_impuesto1 as IGV ".
              ", det.nu_fac_valortotal as TOTAL_VENTA ".
			  ", det.ch_fac_credito as ANTICIPO ".
			  "from tmp_fac_ta_factura_cabecera as det, int_clientes as cli ".
	          "where (det.dt_fac_fecha >= '$fecha_ini' AND det.dt_fac_fecha <= '$fecha_fin')".
			  "AND ch_fac_tipodocumento <> '45' ".
			  "AND (det.ch_fac_seriedocumento = '001' OR det.ch_fac_seriedocumento = '501' )".
			  "AND det.cli_codigo = cli.cli_codigo ".
			  "".$cond1."".
			  "".$cond2."".
			  "".$cond3."".
			  "".$cond4."".
			  "".$cond5."".
			  "order by det.cli_codigo, det.dt_fac_fecha DESC, det.ch_fac_seriedocumento, det.ch_fac_numerodocumento";
	$sqlca->query($query);
	$numrows = $sqlca->numrows();
    while($reg = $sqlca->fetchRow())
	{
		$registro[] = $reg;
	}
    return $registro;
	
  }
  function GenerarRegitroID()
  {
    if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") 
    {
	$Valores     = explode(' ', $_REQUEST["registroid"]);
	$registroid['TipoDoc']    = trim($Valores[0]);
	$registroid['SerieDoc']   = trim($Valores[1]);
	$registroid['NroDoc']     = trim($Valores[2]);
	$registroid['CodCliente'] = trim($Valores[3]);
    //print_r($registroid);
    return $registroid;
    }
  }
  
  function actualizarRegistro($datos, $datosArticulos){
  	
  	global $sqlca;
  	$datos['ch_punto_venta']=$datos['ch_almacen'];
  	$datos['dt_fac_fecha'] = "to_date('".$datos['dt_fac_fecha']."','dd/mm/yyyy')";
  	if ($sqlca->perform('tmp_fac_ta_factura_cabecera', $datos, 'update', "ch_fac_tipodocumento='".$datos['ch_fac_tipodocumento']."' AND ch_fac_seriedocumento='".$datos['ch_fac_seriedocumento']."' AND ch_fac_numerodocumento='".$datos['ch_fac_numerodocumento']."' AND cli_codigo='".$datos['cli_codigo']."'")>=0)
      {
    	/*if(!empty($datosArticulos))
			FacturasModel::InsertarArticulos($datos, $datosArticulos);
		FacturasModel::guardarRegistroComplemento($datos, $datosComplementarios);*/
	} else return $sqlca->get_error(); 
	foreach($datosArticulos as $llave => $Valores)
    {   
		$Articulos['ch_fac_tipodocumento']  = $datos['ch_fac_tipodocumento'];
		$Articulos['ch_fac_seriedocumento'] = $datos['ch_fac_seriedocumento'];
		$Articulos['ch_fac_numerodocumento']= $datos['ch_fac_numerodocumento'];
		$Articulos['cli_codigo']            = $datos['cli_codigo'];
		$Articulos['art_codigo']            = $Valores['cod_articulo'];
		$Articulos['pre_lista_precio']      = $Valores['pre_lista_precio'];
		$Articulos['nu_fac_cantidad']       = $Valores['cant_articulo'];
		$Articulos['nu_fac_precio']         = $Valores['precio_articulo'];
		$Articulos['nu_fac_importeneto']    = $Valores['neto_articulo'];
		$Articulos['ch_factipo_descuento1'] = $datos['ch_factipo_descuento1'];
		$Articulos['nu_fac_descuento1']     = ($Valores['dscto_articulo']==''?0.00:$Valores['dscto_articulo']);
		$Articulos['ch_fac_cd_impuesto1']   = $datos['ch_fac_cd_impuesto1'];
		$Articulos['nu_fac_impuesto1']      = $Valores['igv_articulo'];
		$Articulos['nu_fac_valortotal']     = $Valores['total_articulo'];
		$Articulos['ch_art_descripcion']    = $Valores['desc_articulo'];
		//print_r($Articulos);
		if($sqlca->perform('tmp_fac_ta_factura_detalle', $Articulos, 'update', "art_codigo='".$Articulos['art_codigo']."' AND ch_fac_tipodocumento='".$Articulos['ch_fac_tipodocumento']."' AND ch_fac_seriedocumento='".$Articulos['ch_fac_seriedocumento']."' AND ch_fac_numerodocumento='".$Articulos['ch_fac_numerodocumento']."' AND cli_codigo='".$Articulos['cli_codigo']."' ")>=0){
      	} else { return $sqlca->get_error(); }
	//print_r($Articulos);
	}
  }
  
  function guardarRegistro($datos){
    global $sqlca;
    $datosArticulos = $GLOBALS['ARTICULOS'];
    $datos['ch_almacen']=substr($datos['ch_almacen'],0,3);
    $datos['ch_punto_venta']=$datos['ch_almacen'];
    //$datos['nu_fac_valortotal']=$datos['nu_fac_valorbruto'] + $datos['nu_fac_impuesto1'];
    $datos['dt_fac_fecha'] = "to_date('".$datos['dt_fac_fecha']."','dd/mm/yyyy')";
    $datos['fecha_replicacion'] = 'now()';
    $datos['flg_replicacion'] = '0';
    $datos['nu_fac_descuento1']=($datos['nu_fac_descuento1']==''?0.00:$datos['nu_fac_descuento1']);
    $datos['nu_fac_impuesto2'] = ($datos['nu_fac_impuesto2']==''?0:$datos['nu_fac_impuesto2']);
    $datos['ch_descargar_stock']='null';
    $datos['nu_fac_recargo2']=0.00;
    $datosComplementarios = $_SESSION['ARR_COMP'];
    if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") 
    {
      
      $registroid = FacturasModel::GenerarRegitroID();
      if ($sqlca->perform('tmp_fac_ta_factura_cabecera', $datos, 'update', "ch_fac_tipodocumento='".$registroid['TipoDoc']."' AND ch_fac_seriedocumento='".$registroid['SerieDoc']."' AND ch_fac_numerodocumento='".$registroid['NroDoc']."' AND cli_codigo='".$registroid['CodCliente']."'")>=0)
      {
     //print_r($datosArticulos);
			if(!empty($datosArticulos))
			{
			   FacturasModel::InsertarArticulos($datos, $datosArticulos);
			}	
	
	    	FacturasModel::guardarRegistroComplemento($datos, $datosComplementarios);
	
	} else { return $sqlca->get_error(); }
      return OK;
    } 
    else 
    {
      if ($sqlca->perform('tmp_fac_ta_factura_cabecera', $datos, 'insert')>=0)
      {      
   
	if(!empty($datosArticulos))
	{
	   FacturasModel::InsertarArticulos($datos, $datosArticulos);
	}	
	
	    FacturasModel::guardarRegistroComplemento($datos, $datosComplementarios);
	
	}else { return $sqlca->get_error(); }
      FacturasModel::AumentaCorreDoc($datos['ch_fac_tipodocumento'],$datos['ch_fac_seriedocumento'], 'insert');
      return OK;
    }
  }  
  function AumentaCorreDoc($TipoDoc, $SerieDoc, $Accion)
  {
  global $sqlca;
    if($sqlca->functionDB("util_fn_corre_docs('".$TipoDoc."', '".$SerieDoc."', '".$Accion."')"))
    {
    return OK;
    }
  }
  function InsertarArticulos($datos, $datosArticulos)
  {
  global $sqlca;
    foreach($datosArticulos as $llave => $Valores)
    {
	$Articulos['ch_fac_tipodocumento']  = $datos['ch_fac_tipodocumento'];
	$Articulos['ch_fac_seriedocumento'] = $datos['ch_fac_seriedocumento'];
	$Articulos['ch_fac_numerodocumento']= $datos['ch_fac_numerodocumento'];
	$Articulos['cli_codigo']            = $datos['cli_codigo'];
	$Articulos['art_codigo']            = $Valores['cod_articulo'];
	$Articulos['pre_lista_precio']      = $Valores['pre_lista_precio'];
	$Articulos['nu_fac_cantidad']       = $Valores['cant_articulo'];
	$Articulos['nu_fac_precio']         = $Valores['precio_articulo'];
	$Articulos['nu_fac_importeneto']    = $Valores['neto_articulo'];
	$Articulos['ch_factipo_descuento1'] = $datos['ch_factipo_descuento1'];
	$Articulos['nu_fac_descuento1']     = $Valores['dscto_articulo'];
	$Articulos['ch_fac_cd_impuesto1']   = $datos['ch_fac_cd_impuesto1'];
	$Articulos['nu_fac_impuesto1']      = $Valores['igv_articulo'];
	$Articulos['nu_fac_valortotal']     = $Valores['total_articulo'];
	$Articulos['ch_art_descripcion']    = $Valores['desc_articulo'];
	//print_r($Articulos);
	FacturasModel::guardarRegistroArticulos($Articulos);
    }
  return OK;
  }
 
  function guardarRegistroArticulos($datos){
    global $sqlca;
    //$datos['fecha_replicacion'] = 'now()';
    //$datos['flg_replicacion'] = '0';
    if(!empty($datos['pre_lista_precio']))
    {
        //echo "ENTRO \n PRECIO : ".$_REQUEST['articulos']['pre_lista_precio']." \n";
        $datos['pre_lista_precio'] = $_REQUEST['articulos']['pre_lista_precio'];
    }else{
        $datos['pre_lista_precio'] = $_REQUEST['articulos']['pre_lista_precio'];
        //echo "ENTRO ELSE \n PRECIO : ".$datos['pre_lista_precio']." \n";
    }
    //print_r($datos);
    if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") 
    {
      
      $registroid = FacturasModel::GenerarRegitroID();
      if($sqlca->perform('tmp_fac_ta_factura_detalle', $datos, 'update', "art_codigo='".$datos['art_codigo']."' AND ch_fac_tipodocumento='".$registroid['TipoDoc']."' AND ch_fac_seriedocumento='".$registroid['SerieDoc']."' AND ch_fac_numerodocumento='".$registroid['NroDoc']."' AND cli_codigo='".$registroid['CodCliente']."' ")>=0){
      } else { return $sqlca->get_error(); }
      
      if($sqlca->numrows_affected()>0){
      } else { $sqlca->perform('tmp_fac_ta_factura_detalle', $datos, 'insert'); }
      
      return OK;
    } 
    else 
    {
      if ($sqlca->perform('tmp_fac_ta_factura_detalle', $datos, 'insert')>=0){      
      }else { return $sqlca->get_error(); }

      return OK;
    }
  }  
  function obtenerRecargoMantenimiento($codigo){
  	global $sqlca;
  	$query="select cli_mantenimiento from int_clientes where cli_codigo='".$codigo."'";
  	
  	$sqlca->query($query);
  	$rs = $sqlca->fetchrow();
  	print_r($query.' bbbbb '.$rs[0]);
  	return $rs[0];
  }
  function obtenerListadePrecios($codigo){
  	global $sqlca;
  	$query="SELECT c.cli_lista_precio, t.tab_descripcion  
			FROM int_tabla_general t INNER JOIN int_clientes c on substring(c.cli_lista_precio for 2 from 1)=substring(t.tab_elemento for 2 from 1)
			where t.tab_tabla = 'LPRE' and c.cli_codigo='".$codigo."'";
  	$sqlca->query($query);
  	$rs = $sqlca->fetchrow();
  	//print_r($query.' bbbbb '.$rs[0]);
  	return $rs;
  }
  function obtenerporcDesc($codigo){
  	global $sqlca;
  	$query="SELECT substring(tab.tab_elemento for 2 from length(tab_elemento)-1) AS cod_descuento, 
	tab.tab_descripcion AS des_descuento, round((tab_num_01/100),6) AS porc_descuento, c.cli_estado_desc 
	FROM  int_tabla_general tab inner join int_clientes c on c.cli_descuento=substring(tab.tab_elemento for 2 from length(tab_elemento)-1)
	WHERE tab_tabla= 'DESC' AND tab_elemento<>'000000' and c.cli_codigo='".$codigo."'";
  	
  	$sqlca->query($query);
  	$rs = $sqlca->fetchrow();
  	//print_r($query.' bbbbb '.$rs[0]);
  	return $rs;
  }
  function obtenerComplementarios($codigo){
  	global $sqlca;
  	$query="SELECT cli_codigo, cli_razsocial, cli_rsocialbreve, cli_grupo, cli_direccion, 
       cli_ruc, cli_moneda, cli_fpago_credito, cli_fecultventa, cli_telefono1, 
       cli_telefono2, cli_telefono3, cli_contacto, cli_email, cli_fecactualiz, 
       cli_estado, cli_trasmision, cli_tipo, cli_creditosol, cli_creditodol, 
       cli_salsol, cli_saldol, flg_replicacion, fecha_replicacion, cli_anticipo, 
       cli_comp_direccion, cli_distrito, cli_lista_precio, cli_mantenimiento, 
       cli_descuento from int_clientes where cli_codigo='".$codigo."'";
  	$sqlca->query($query);
  	$rs = $sqlca->fetchrow();
  	return $rs;
  }
 
  function guardarRegistroComplemento($datos, $datosComplementarios=array()){
    global $sqlca;
    $Complementos['ch_fac_tipodocumento']          = $datos['ch_fac_tipodocumento'];
    $Complementos['ch_fac_seriedocumento']         = $datos['ch_fac_seriedocumento'];
    $Complementos['ch_fac_numerodocumento']        = $datos['ch_fac_numerodocumento'];
    $Complementos['cli_codigo']                    = $datos['cli_codigo'];
    $Complementos['dt_fac_fecha']                  = $datos['dt_fac_fecha'];
    $Complementos['ch_fac_observacion1']           = $datosComplementarios['obs1'];
    $Complementos['ch_fac_observacion2']           = $datosComplementarios['obs2'];
    $Complementos['ch_fac_observacion3']           = $datosComplementarios['obs3'];
    $Complementos['ch_fac_ruc']                    = $datosComplementarios['ruc'];
    $Complementos['nu_fac_direccion']              = $datosComplementarios['direccion'];
    $Complementos['nu_fac_complemento_direccion']  = $datosComplementarios['comp_dir'];
    $Complementos['ch_fac_nombreclie']				= $datosComplementarios['razon_social'];
    $Complementos['dt_fechactualizacion']          = "now()";
    if ($sqlca->perform('tmp_fac_ta_factura_complemento', $Complementos, 'insert')>=0){
      }else { return $sqlca->get_error(); }
     return OK;  
  }  
  function ObtTipoAccContable($TipoDoc)
  {
  global $sqlca;
    $TipAccCont = $sqlca->functionDB("util_fn_tipo_accion_contable('CC','$TipoDoc')");
  return $TipAccCont;
  }
  function CostoPromedio($FechaDoc, $CodAlm, $CodArt)
  {
  global $sqlca;
  $Monto = $sqlca->functionDB("util_fn_costo_promedio(to_char(to_date('".$FechaDoc."','dd/mm/yyyy'),'yyyy'),to_char(to_date('".$FechaDoc."','dd/mm/yyyy'),'mm'),'".$CodArt."',lpad('".$CodAlm."',3,'0'))");
  return $Monto;
  }
  
  
  function recuperarRegistro($registroid){
    global $sqlca;
    $registro = array();
    
    $query = "SELECT ".
		    "trim(dt_fac_fecha) as dt_fac_fecha, ".
		    "ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||ch_fac_numerodocumento||trim(cli_codigo) as registroid, ".
		    "trim(ch_fac_tipodocumento) as ch_fac_tipodocumento, ".
		    "trim(ch_fac_seriedocumento) as ch_fac_seriedocumento, ".
		    "trim(ch_fac_numerodocumento) as ch_fac_numerodocumento, ".
		    "trim(cli_codigo) as cli_codigo, ".
		    "trim(ch_almacen) as ch_almacen, ".
		    "trim(ch_fac_moneda) as ch_fac_moneda, ".
		    "round(nu_tipocambio,2) as nu_tipocambio, ".
		    "ch_fac_credito, ".
		    "ch_fac_forma_pago, ".
		    "ch_factipo_descuento1,
		    nu_fac_recargo2,  ".
		    "nu_fac_descuento1, ".
		     "ch_fac_cd_impuesto1, ".
		    "ch_fac_anticipo, ".
		    "nu_fac_valorbruto, ".
		    "nu_fac_impuesto1, ".
		    "nu_fac_valortotal, ".
		    "nu_fac_impuesto2, ch_descargar_stock, ch_liquidacion ".
	     "FROM tmp_fac_ta_factura_cabecera 
	      ".
	     "WHERE ch_fac_tipodocumento='".$registroid['TipoDoc']."' AND ch_fac_seriedocumento='".$registroid['SerieDoc']."' AND ch_fac_numerodocumento='".$registroid['NroDoc']."' AND cli_codigo='".$registroid['CodCliente']."' ";
	      
    //echo "QUERY : $query \n";      
    $sqlca->query($query);

    while( $reg = $sqlca->fetchRow()){$registro = $reg;}
    
    return $registro;
  }
  function recuperarArticulos($registroid){
    global $sqlca;
    
    $query = "SELECT ".
		    "trim(det.art_codigo) as cod_articulo, ".
		    "det.ch_art_descripcion as desc_articulo, ".
		    "nu_fac_cantidad as cant_articulo, ".
		    "round(nu_fac_precio,2) as precio_articulo, ".
		    "round(nu_fac_importeneto,2) as neto_articulo, ".
		    "round(nu_fac_impuesto1,2) as igv_articulo, ".
		    "round(nu_fac_valortotal,2) as total_articulo, ".
		    "nu_fac_descuento1 as dscto_articulo, ".
		    "det.pre_lista_precio ".
	     "FROM tmp_fac_ta_factura_detalle det, ".
	          "int_articulos art ".
	     "WHERE ch_fac_tipodocumento='".$registroid['TipoDoc']."' AND ch_fac_seriedocumento='".$registroid['SerieDoc']."' AND ch_fac_numerodocumento='".$registroid['NroDoc']."' AND cli_codigo='".$registroid['CodCliente']."' ".
    	     "AND det.art_codigo=art.art_codigo ".
    	     "ORDER BY det.art_codigo ";
    //echo "QUERY : $query \n";
    $sqlca->query($query);
    $registros = array();
    $x=1;
    while( $reg = $sqlca->fetchRow())
    {
        $registros[$x] = $reg;
    $x++;
    }    
    //print_r($registros);
    return $registros;
  }
  function recuperarComplemento($registroid){
    global $sqlca;
    
    $query = "SELECT ".
                    "trim(cli.cli_razsocial) as cli_razsocial, ".
		    "dt_fac_fecha, ".
		    "ch_fac_observacion1, ".
		    "ch_fac_observacion2, ".
		    "ch_fac_observacion3, ".
		    "ch_fac_ruc, ".
		    "nu_fac_direccion, ".
		    "nu_fac_complemento_direccion ".
	     "FROM tmp_fac_ta_factura_complemento fac, int_clientes cli ".
	     "WHERE fac.ch_fac_tipodocumento='".$registroid['TipoDoc']."' ".
	     "AND fac.ch_fac_seriedocumento='".$registroid['SerieDoc']."' ".
	     "AND fac.ch_fac_numerodocumento='".$registroid['NroDoc']."' ".
	     "AND fac.cli_codigo='".$registroid['CodCliente']."' ".
    	     "AND fac.cli_codigo=cli.cli_codigo";
    //echo "QUERY : $query \n";
    $sqlca->query($query);
    $registros = array();
    $reg = $sqlca->fetchRow();


   /* while( $reg = $sqlca->fetchRow())
    {
        $registros = $reg;
    }   */
    if($sqlca->numrows()>0)
    {
	$registros["razon_social"]   = $reg["cli_razsocial"]; 
	$registros["direccion"]      = $reg["nu_fac_direccion"];
	$registros["ruc"]            = $reg["ch_fac_ruc"];
	$registros["comp_dir"]       = $reg["nu_fac_complemento_direccion"];
	$registros["obs1"]           = $reg["ch_fac_observacion1"];
	$registros["obs2"]           = $reg["ch_fac_observacion2"];
	$registros["obs3"]           = $reg["ch_fac_observacion3"];
    }
    //print_r($registros);
    return $registros;
  }
  function eliminarRegistro($registroid){
    global $sqlca;
    $Valores     = explode(' ', $registroid);
    $cod_docu    = trim($Valores[0]);
    $serie_docu  = trim($Valores[1]);
    $num_docu    = trim($Valores[2]);
    $cod_cliente = trim($Valores[3]);
    
	$VARI = $sqlca->functionDB("tmp_ventas_fn_eliminacion_documentos('$cod_docu','$serie_docu','$num_docu','$cod_cliente','ELIMINACION')");
	if($VARI==''){
	   return "El documento presenta cancelaciones o eliminaciones en cuentas por cobrar";
	}
	   
	return 'OK';
 }
  function anulacionRegistro($registroid){
    global $sqlca;
    $Valores     = explode(' ', $registroid);
    $cod_docu    = trim($Valores[0]);
    $serie_docu  = trim($Valores[1]);
    $num_docu    = trim($Valores[2]);
    $cod_cliente = trim($Valores[3]);
   
    $VARI = $sqlca->functionDB("tmp_ventas_fn_eliminacion_documentos('$cod_docu','$serie_docu','$num_docu','$cod_cliente','ANULACION')");
    if($VARI==''){
   	 return "El documento presenta cancelaciones o eliminacionen en cuentas por cobrar";
   }
    return 'OK';
  }
  function VerificaMontos($registroid)
  {
  global $sqlca;
    $query = "SELECT ".
                    "nu_importetotal, ".
                    "nu_importesaldo ".
             "FROM ccob_ta_cabecera ".
             "WHERE trim(ch_tipdocumento)||trim(ch_seriedocumento)||trim(ch_numdocumento)||trim(cli_codigo)='".trim($registroid)."' ".
             "AND nu_importetotal != nu_importesaldo";
    $sqlca->query($query);
    $numrows = $sqlca->numrows();
  return $numrows;
  }
  function eliminarArticuloDet($registroid, $articuloid)
  {
  global $sqlca;
    if($sqlca->perform('tmp_fac_ta_factura_detalle  ', ' ','delete', "ch_fac_tipodocumento='".$registroid['TipoDoc']."' AND ch_fac_seriedocumento='".$registroid['SerieDoc']."' AND ch_fac_numerodocumento='".$registroid['NroDoc']."' AND cli_codigo='".$registroid['CodCliente']."' AND trim(art_codigo)='trim($articuloid)'")>=0){
    } else { return $sqlca->get_error(); }
    return OK;
  }
  function tmListado($filtro=array(),$pp, $pagina)
  {
    global $sqlca;
    $cond = '';
    if ($filtro["codigo"] != ""){
      $cond = " AND trim(f.cli_codigo)||trim(c.cli_rsocialbreve)||trim(f.ch_fac_tipodocumento)||trim(f.ch_fac_seriedocumento)||trim(f.ch_fac_numerodocumento) ~ '".$filtro["codigo"]."' ";
    }else{
	    if($filtro["f_desde"] != "" && $filtro["f_hasta"] != ""){
	      $fechaDesdeDiv = explode('/', $filtro["f_desde"]);
	      $fechaHastaDiv = explode('/', $filtro["f_hasta"]);
	      $filtro["f_desde"] = $fechaDesdeDiv[1]."/".$fechaDesdeDiv[0]."/".$fechaDesdeDiv[2];
	      $filtro["f_hasta"] = $fechaHastaDiv[1]."/".$fechaHastaDiv[0]."/".$fechaHastaDiv[2];
	      $cond2 = " AND dt_fac_fecha BETWEEN '".$filtro["f_desde"]."' AND '".$filtro["f_hasta"]."' ";
	    }
    }
// AND dt_fac_fecha between '2005-09-30 00:00:00' AND '2006-09-30 23:59:59'    
//and CH_FAC_TIPODOCUMENTO = '10'    and ch_fac_seriedocumento = '001' 
// and  ch_fac_numerodocumento like  '%0000009%'  ORDER BY dt_fac_fecha LIMIT 20 OFFSET 0
		$query = "SELECT ".
						"f.ch_fac_tipodocumento, ".
						"f.ch_fac_seriedocumento, ".
						"f.ch_fac_numerodocumento, ".
						"f.cli_codigo, ".
						"c.cli_rsocialbreve, ".
						"to_char(f.dt_fac_fecha,'dd/mm/yyyy') as dt_fac_fecha, ".
						"f.ch_punto_venta, ".
						//"ch_almacen, ".
						"f.ch_fac_moneda, ".
						"f.nu_tipocambio, ".
						"f.nu_fac_valorbruto, ".
						"f.nu_fac_impuesto1, ".
						"f.nu_fac_valortotal, ".
						"f.ch_fac_credito, ".
					   
						"f.ch_fac_anulado, ".
						//"ch_fac_impreso, ".
						"f.ch_fac_anticipo, ".
						"f.ch_liquidacion as liqui, ".
						"f.ch_fac_cab_identidad, ".
						"trim(f.ch_fac_tipodocumento)||trim(f.ch_fac_seriedocumento)||trim(f.ch_fac_numerodocumento)||trim(f.cli_codigo) as codigo,  ".
						 "c.cli_mantenimiento, f.nu_fac_recargo2 ".
				  "FROM tmp_fac_ta_factura_cabecera f, int_clientes c ".
				  "WHERE f.cli_codigo = c.cli_codigo ".
				  "AND (f.ch_almacen='001' OR f.ch_almacen='501') ".
			  " ".$cond." ".
			  " ".$cond2." ".
			  "ORDER BY f.ch_fac_tipodocumento, f.ch_fac_seriedocumento, f.ch_fac_numerodocumento ";
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
	    
    //echo "QUERY : $query \n";
    print_r($query);
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    
    $listado= array();
    //$listado['datos_pag']
    //if ($filtro["codigo"] != "" and $filtro["f_desde"]!='' and $filtro['f_hasta']!='')
	//{
		while( $reg = $sqlca->fetchRow())
		{
			$listado['datos'][] = $reg;
		}    
	//}
    
    
    $listado['paginacion'] = $listado2;
    print_r($listado);
    
    return $listado;
  }
  function ListadosVarios($Dato)
  {
    global $sqlca;
    $sqlca->query("BEGIN");
    $sqlca->functionDB("util_fn_combos('".$Dato."','ret')");
    $sqlca->query("FETCH ALL IN ret", 'registros');
    $sqlca->query("CLOSE ret");
    $sqlca->query("END");
    $cbArray = array();
    $x=0;
    while($reg = $sqlca->fetchRow('registros'))
    {
      if($reg[0]!="000000")
      {
       $cbArray[trim($reg[0])] = trim($reg[0])." ".$reg[1];
      }
    }    
	ksort($cbArray);
    return $cbArray;
  }
  function TiposSeriesCBArray($condicion='',$codigo)
  {
    global $sqlca;
    $cbArray = array();
    $query = "SELECT ".
                    "trim(doc.num_seriedocumento) as serie, ".
		    "trim(doc.num_descdocumento) AS descripcion, ".
		    "trim(doc.num_tipdocumento) as cod_documento, ".
		    "lpad((cast(trim(doc.num_numactual) as integer)+1),7,'0') as numactual ,
		    trim(doc.ch_almacen) || ' - ' || trim(ts.ch_nombre_sucursal) as almacen ".
             "FROM int_num_documentos doc, ".
		 /* "int_tabla_general tab, int_ta_sucursales ts ".*/
		 " int_ta_sucursales ts".
             /*"WHERE tab.tab_tabla='08' ".*/
             " where ".
             "trim(doc.num_tipdocumento)='".$codigo."' ".
            /* "AND tab.tab_elemento<>'000000' ".*/
           
            /* "AND tab.tab_car_03 is not null ".*/
            /* "AND substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1)=doc.num_tipdocumento */
             "and ts.ch_sucursal=doc.ch_almacen ".
             //"ORDER BY doc.num_descdocumento";
             
    $query .= ($condicion!=''?' AND '.$condicion:'').' ORDER BY doc.num_descdocumento';
    //echo "QUERY : $query \n";
    
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result["serie"])] = $result["descripcion"];
      $cbArray['Numeros'][trim($result["serie"])] = $result["numactual"];
      $cbArray['Almacen'][trim($result["serie"])] = $result["almacen"];
    }

    ksort($cbArray);
    return $cbArray;
  }
  function FormaPagoCBArray($condicion='', $codigo){
  global $sqlca;
    $codigo=="N"?$codigo='05':$codigo='96';
    $cbArray = array();
    $query = "SELECT ".
                    //"tab_elemento, ".
                    "substring(tab_elemento for 2 from length(tab_elemento)-1 ) AS tab_elemento, ".
                    "tab_descripcion, ".
                    "cast(tab_num_01 as int) as dias ".
             "FROM int_tabla_general ".
             "WHERE tab_tabla = '".$codigo."' ".
             "AND tab_elemento<>'000000'";

    $query .= ($condicion!=''?' AND '.$condicion:'').' ORDER BY 1';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
      $cbArray['Dias'][trim($result["tab_elemento"])] = $result["dias"];
    }
    ksort($cbArray);
    return $cbArray;
  }
  function DescuentosCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT ".
                    "substring(tab.tab_elemento for 2 from length(tab_elemento)-1 ) AS cod_descuento, ".
                    "tab.tab_descripcion AS des_descuento, ".
                    "round((tab_num_01/100),6) AS porc_descuento ".
                    "FROM  int_tabla_general tab ".
                    "WHERE tab_tabla= 'DESC' ".
                    "AND tab_elemento<>'000000' ".
    
    $query .= ($condicion!=''?' AND '.$condicion:'').' ORDER BY des_descuento DESC';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result["cod_descuento"])] = $result["cod_descuento"].' '.$result["des_descuento"];
      $cbArray['Desc'][trim($result["cod_descuento"])] = $result["porc_descuento"];
    }
    ksort($cbArray);
    return $cbArray;
  }
  function ArticulosCBArray($condicion='', $codigo){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT ".
                    "art.art_codigo, ".
		    "art.art_descripcion, ".
		    "fac.pre_precio_act1, art.art_modifica_articulo ".
	     "FROM int_articulos art, ".
	          "fac_lista_precios fac ".
	     "WHERE art.art_codigo=fac.art_codigo ".
	     "AND fac.pre_lista_precio= '".$codigo."' ".
	     
    $query .= ($condicion!=''?' AND '.$condicion:'').' ORDER BY art.art_codigo';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
      
    while($result = $sqlca->fetchRow()){
      $cbArray['DATOS_VER'][trim($result["art_codigo"])] = $result["art_codigo"].' '.$result["art_descripcion"];
      $cbArray['DESCRIPCION'][trim($result["art_codigo"])] = $result["art_descripcion"];
      $cbArray['PRECIO'][trim($result["art_codigo"])] = $result["pre_precio_act1"];
      $cbArray['EDITABLE'][trim($result["art_codigo"])] = $result["art_modifica_articulo"];
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
  function ClientesCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT cli_codigo,cli_razsocial,cli_rsocialbreve FROM int_clientes ".
             //"WHERE tab_tabla = '02' AND tab_elemento<>'000000'";
    $query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_rsocialbreve"];
    }
    ksort($cbArray);
    return $cbArray;
  }
  function AgregarArticulo($DatosArray, $contador)
  {
  $datos = $GLOBALS['ARTICULOS'];
  //print_r($datos);
  //print_r($DatosArray);
    if($DatosArray['codigo']!='' && trim($DatosArray['cantidad'])!='' && trim($DatosArray['cantidad'])>0)
    {
        //echo " CONTADOR : ".($contador)."\n";
        for($i=0;$i<$contador+1;$i++)
        {
	    	$T = $datos[$i];
	    	if($T['cod_articulo']==$DatosArray['codigo'])
		    	{ 	
			    //$agregar = false;
			    $mensaje = "Este articulo ya ha sido ingresado antes";
			    //echo "MENSAJE : $mensaje \n";
	            }
		}
	if(!$mensaje)
	{
		$datos[$DatosArray['codigo']]['cod_articulo']       = $DatosArray['codigo'];
	    $datos[$DatosArray['codigo']]['desc_articulo']      = $DatosArray['descripcion'];
	    $datos[$DatosArray['codigo']]['cant_articulo']      = $DatosArray['cantidad'];
	    $datos[$DatosArray['codigo']]['precio_articulo']    = $DatosArray['precio'];
	    $datos[$DatosArray['codigo']]['neto_articulo']      = $DatosArray['neto'];
	    $datos[$DatosArray['codigo']]['igv_articulo']       = $DatosArray['igv'];
	    $datos[$DatosArray['codigo']]['dscto_articulo']     = $DatosArray['dscto'];
	    $datos[$DatosArray['codigo']]['total_articulo']     = $DatosArray['total'];
	    $datos[$DatosArray['codigo']]['pre_lista_precio']   = $_REQUEST['articulos']['pre_lista_precio'];
	}
    }
    
    return $datos;
  }
  function CalcularTotales($DatosArray)
  {
  $datos = $GLOBALS['ARTICULOS'];
  //print_r($datos);
  //print_r($DatosArray);
  
  if(empty($datos) && !$_REQUEST["registroid"])
  {
  //print_r($DatosArray);
  //echo "ENTRO DATOS VACIO \n";
  	//$datos['total_recargo'] += 
	$datos['total_cant_articulo']      += round($DatosArray["cantidad"],3);
	$datos['total_precio_articulo']    += round($DatosArray["precio"],3);
	//print_r($DatosArray['igv']);
	$datos['total_neto_articulo']      += $DatosArray["neto"]*(1+$_REQUEST['recargo']/100);
	$datos['total_total_articulo']     += $DatosArray["total"]*(1+$_REQUEST['recargo']/100);
	//$datos['total_neto_articulo']      = round($datos['total_total_articulo']/(1+VariosModel::ObtIgv()),2);
	$datos['total_igv_articulo']       += $DatosArray["igv"]*(1+$_REQUEST['recargo']/100);
	//$datos['total_igv_articulo']       = round($datos['total_neto_articulo']*VariosModel::ObtIgv(),2);
	$datos['total_dscto_articulo']     += $DatosArray["dscto"];
	
	
  return $datos;
  }elseif(!empty($datos) && $_REQUEST["registroid"]){
  //print_r($DatosArray);
  //echo "ENTRO DATOS EXISTE 1\n";
    foreach($datos as $llave => $DatosSesion)
    {
	$datos['total_cant_articulo']      += round($DatosSesion["cant_articulo"],3);
	$datos['total_precio_articulo']    += round($DatosSesion["precio_articulo"],3);
	$datos['total_neto_articulo']      += $DatosSesion["neto_articulo"]*(1+$_REQUEST['recargo']/100);
	$datos['total_igv_articulo']       += $DatosSesion["igv_articulo"]*(1+$_REQUEST['recargo']/100);
	$datos['total_dscto_articulo']     += $DatosSesion["dscto_articulo"];
	$datos['total_total_articulo']     += $DatosSesion["total_articulo"]*(1+$_REQUEST['recargo']/100);
	
    }
  return $datos;
  }elseif(!empty($datos) || $_REQUEST["registroid"]){
  //echo "ENTRO DATOS EXISTE 2\n";
   if(!empty($_REQUEST["registroid"]))
   {
        $datos = $DatosArray;
   }
    foreach($datos as $llave => $DatosSesion)
    {
	$datos['total_cant_articulo']      += round($DatosSesion["cant_articulo"],3);
	$datos['total_precio_articulo']    += round($DatosSesion["precio_articulo"],3);
	$datos['total_neto_articulo']      += $DatosSesion["neto_articulo"]*(1+$_REQUEST['recargo']/100);
	$datos['total_igv_articulo']       += $DatosSesion["igv_articulo"]*(1+$_REQUEST['recargo']/100);
	$datos['total_dscto_articulo']     += $DatosSesion["dscto_articulo"];
	$datos['total_total_articulo']     += $DatosSesion["total_articulo"]*(1+$_REQUEST['recargo']/100);
	
    }

    return $datos;
  }elseif(empty($datos) || $_REQUEST["registroid"]){
  //echo "ENTRO DATOS EXISTE y REGISTRO\n";
   if(!empty($_REQUEST["registroid"]))
   {
        $datos = $DatosArray;
   }
    foreach($datos as $llave => $DatosSesion)
    {
	$datos['total_cant_articulo']      += round($DatosSesion["cant_articulo"],3);
	$datos['total_precio_articulo']    += round($DatosSesion["precio_articulo"],3);
	$datos['total_neto_articulo']      += $DatosSesion["neto_articulo"]*(1+$_REQUEST['recargo']/100);
	$datos['total_igv_articulo']       += $DatosSesion["igv_articulo"]*(1+$_REQUEST['recargo']/100);
	$datos['total_dscto_articulo']     += $DatosSesion["dscto_articulo"];
	$datos['total_total_articulo']     += $DatosSesion["total_articulo"]*(1+$_REQUEST['recargo']/100);
	
    }

    return $datos;
  }
  }

}
