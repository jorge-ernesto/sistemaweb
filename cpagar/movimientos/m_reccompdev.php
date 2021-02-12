<?php
  // Modelo para Tarjetas Magneticas

Class RecCompDevModel extends Model{

  function guardarRegistro(){
    global $sqlca;
    //cpag_tipo_pago | cpag_serie_pago | cpag_num_pago
    $datos['cpag_tipo_pago']  = 'null';
    $datos['cpag_serie_pago'] = 'null';
    $datos['cpag_num_pago']   = 'null';
    
    if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"]!="") 
    {
      //actualizar registro
      $registroid = trim($_REQUEST["registroid"]);
      if ($sqlca->perform('inv_ta_compras_devolucion', $datos, 'update', "mov_almacen||mov_numero||art_codigo||to_char(mov_fecha, 'DD/MM/YYYY')='$registroid'")>=0){
      }else  return $sqlca->get_error();
      return OK;
    } 
    return '<error>Error</error>';
  }  

  //Otras funciones para consultar la DB
  function GeneraQuery($filtro=array())
  {
    $cond = '';
    $condPend = '';
    $condOpc = '';
    
    if ($filtro["fecha_ini"] != "" && $filtro["fecha_fin"] != "")
      $cond = " AND dev.mov_fecha BETWEEN to_date('".$filtro["fecha_ini"]."', 'DD/MM/YYYY') ".
              " AND to_date('".$filtro["fecha_fin"]."', 'DD/MM/YYYY') ";
    
    if ($filtro["proveedor"]!="")
      $cond .= "AND dev.mov_entidad = '".$filtro['proveedor']."' ";
      
    if ($filtro["articulo"]!="")
      $cond .= "AND dev.art_codigo = '".$filtro['articulo']."' ";
    
    if ($filtro["almacen"]!="")
      $cond .= "AND dev.mov_almacen = '".$filtro['almacen']."' ";
    
    if ($filtro["tipo_transaccion"]=="01"){
      $cond .= "AND dev.mov_naturaleza < '3' ";
      $condPend = "";
    }
    if ($filtro["tipo_transaccion"]=="05"){
      $cond .= "AND dev.mov_naturaleza >= '3' ";
      $condPend = "AND dev.tran_codigo = '".$filtro["tipo_transaccion"]."' ";
    }
    
    if($filtro["fecha_fin"]!='' && $filtro["opcion"]=='pendientes')
      $condPend .= "AND cab.pro_cab_fechaemision > '".$filtro["fecha_fin"]."' ";
      //$condOpc = "AND dev.cpag_tipo_pago is null and dev.cpag_serie_pago is null and dev.cpag_num_pago is null ";
    
    if($filtro["fecha_ini"]!='' && $filtro["fecha_fin"] != "" && $filtro["opcion"]=='atendidos')
      $condPend .= "AND cab.pro_cab_fechaemision >= '".$filtro["fecha_ini"]."' ".
                   "AND cab.pro_cab_fechaemision <= '".$filtro["fecha_fin"]."' ";
    
    $query_1 = "SELECT ".
                      "to_char(dev.mov_fecha,'dd/mm/yyyy') as mov_fecha, ".
		      "dev.com_num_compra, ".
		      "dev.mov_entidad, ". 
		      "dev.mov_tipdocuref, ".
		      "dev.mov_docurefe, ".
		      "dev.mov_almacen, ".
		      "dev.art_codigo, ".
		      "dev.mov_cantidad, ".
		      "dev.mov_costounitario, ".
		      "dev.mov_costototal, ".
		      "dev.mov_numero, ".
		      "to_char(to_date('1841-01-01','yyyy-mm-dd'), 'dd/mm/yyyy'), ".
		      "dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura, ".
		      "art.art_descripcion, ".
		      "pro.pro_razsocial ".
             "FROM ".
                   "inv_ta_compras_devoluciones dev, ".
                   "int_articulos art, ".
                   "int_proveedores pro ".
             "WHERE ".
		   "dev.art_codigo = art.art_codigo ".
             "AND dev.mov_entidad = pro.pro_codigo ".
	     "AND dev.cpag_tipo_pago is null ".
	     "AND dev.cpag_serie_pago is null ".
	     "AND dev.cpag_num_pago is null ".
	     " ".$cond." ";
	     //" ".$condPend." ";
	     
    $query_2 = "SELECT to_char(dev.mov_fecha,'dd/mm/yyyy') as mov_fecha, ".
                       "dev.com_num_compra,dev.mov_entidad, ".
                       "dev.mov_tipdocuref, ".
                       "dev.mov_docurefe, ".
                       "dev.mov_almacen, ".
                       "dev.art_codigo, ".
                       "dev.mov_cantidad, ".
                       "dev.mov_costounitario, ".
                       "dev.mov_costototal, ".
                       "dev.mov_numero, ".
                       "to_char(cab.pro_cab_fechaemision, 'dd/mm/yyyy') as pro_cab_fechaemision, ".
                       "dev.cpag_tipo_pago||dev.cpag_serie_pago||dev.cpag_num_pago as factura, ".
                       "art.art_descbreve, ".
                       "pro.pro_rsocialbreve ".
               "FROM ".
                     "inv_ta_compras_devoluciones dev, ".
                     "cpag_ta_cabecera cab, ".
                     "int_articulos art, ".
                     "int_proveedores pro ".
               "WHERE ".
                      "cab.pro_cab_tipdocumento = dev.cpag_tipo_pago ".
               "AND cab.pro_cab_seriedocumento = dev.cpag_serie_pago ".
               "AND cab.pro_cab_numdocumento = dev.cpag_num_pago ".
               "AND cab.pro_codigo = dev.mov_entidad ".
               "AND dev.art_codigo = art.art_codigo ".
               "AND dev.mov_entidad =pro.pro_codigo".
               " ".$cond." ".
               " ".$condPend." ";
  //print_r($filtro);
  switch ($filtro["opcion"])
  {
    case 'todos':
      $query = $query_1." UNION ".$query_2;
    break;
    case 'pendientes':
      $query = $query_1." UNION ".$query_2;
    break;
    case 'atendidos':
      $query = $query_2;
    break;
    default:
      $query = $query_2;
    break;
  }
  if($query=='') $query = $query_2;
  //print_r($query);
  return $query;
  }
  
  function tmListado($query,$pp, $pagina)
  {
    global $sqlca;
    /*
echo "<pre>";
var_dump($query);
echo "</pre>";
*/
         $resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
        // echo "PP : $pp => PAGINA : $pagina \n";
	if(!empty($pp) && $pagina>=0)
	{
	   //echo "ENTRO 2 IF \n REGPP : $pp \n PAG : $pagina\n";
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
  
  function ProveedorCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();

    $query = "SELECT pro_codigo, pro_rsocialbreve FROM int_proveedores ".
    @$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 1';

    if ($sqlca->query(@$query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["pro_codigo"])] = $result["pro_codigo"].' '.$result["pro_rsocialbreve"];
    }
    ksort($cbArray);
    return $cbArray;
  }

  function ArticuloCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();

    $query = "SELECT art_codigo, art_descbreve FROM int_articulos ".
    @$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 1';

    if ($sqlca->query(@$query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["art_codigo"])] = $result["art_codigo"].' '.$result["art_descbreve"];
    }
    ksort($cbArray);
    return $cbArray;
  }

  function AlmacenCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();

    $query = "SELECT ch_almacen, ch_nombre_breve_almacen FROM inv_ta_almacenes ".
    @$query .= ($condicion!=''?' WHERE ch_clase_almacen=\'1\' AND '.$condicion:'').' ORDER BY 1';

    if ($sqlca->query(@$query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["ch_almacen"])] = $result["ch_almacen"].' '.$result["ch_nombre_breve_almacen"];
    }
    ksort($cbArray);
    return $cbArray;
  }

}
?>
