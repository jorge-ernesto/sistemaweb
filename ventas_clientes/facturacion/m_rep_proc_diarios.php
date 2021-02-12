<?php
  // Modelo para RepProcDia de Cuentas por cobrar

Class RepProcDiaModel extends Model{
  
  //Otras funciones para consultar la DB

  function ListadoTbl()
  {
  global $sqlca;
    $query = "SELECT id FROM tbl_sistemas ORDER BY 1";
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    
    while( $reg = $sqlca->fetchRow())
    {
        $listado[] = $reg;
    }    
  return $listado;
  }

  function ListadoSistemas($fecha, $id, $Almacen)
  {
  global $sqlca;
  //echo "PENDIENTE : ".$_REQUEST['busqueda']['pendiente']." \n";
  
  if($_REQUEST['busqueda']['pendiente'] == "si")
  {
    $cond .= " AND ar_replicacion[".$id."] = 'N' ";
  }
  if($fecha != "")
  {
    $cond .= " AND dt_fecha = '".$fecha."' ";
  }
    $query = "SELECT ".
                     "( ".
                        "SELECT ".
                                "descripcion ".
                        "FROM tbl_sistemas ".
                        "WHERE id=".$id." ".
                     ") as sistema, ".
                     "( ".
                        "SELECT ".
                                "tablas ".
                        "FROM tbl_sistemas ".
                        "WHERE id=".$id.") as tablas, ".
                     "ar_replicacion[".$id."] ".
              "FROM rep_ta_procesos_diarios ".
              "WHERE ch_almacen='".$Almacen."'".
              "".$cond."";

    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    $x=0;
    while( $reg = $sqlca->fetchRow())
    {
        if($reg[2]) $listado[] = $reg;
    $x++;
    }    
  return $listado;
  }

  function tmListado($filtro=array(), $pp, $pagina)
  {
  global $sqlca;
    $cond = '';
    //$cond = " AND to_char(inv.mov_fecha,'dd/mm/yyyy') = '".pg_escape_string($filtro["fecha"])."' ";
    //print_r($filtro);
    if (!$filtro['fecha_ini']) $filtro['fecha_ini'] = (date("d")-1)."/".date("m")."/".date("Y");
    if (!$filtro['fecha_fin']) $filtro['fecha_fin'] = date("d/m/Y");

    if ($filtro["codigo"] != "")
    {
      $cond = " WHERE ch_almacen ~ '".pg_escape_string($filtro["codigo"])."' ";
    }
    if($filtro["fecha_ini"] !="" && $filtro["fecha_fin"] !="" && $filtro["codigo"]=="")
    {
      $cond .= " WHERE to_char(dt_fecha, 'dd/mm/yyyy') BETWEEN '".pg_escape_string($filtro["fecha_ini"])."' AND '".pg_escape_string($filtro["fecha_fin"])."' ";
      
    }elseif($filtro["fecha_ini"] !="" && $filtro["fecha_fin"] && $filtro["codigo"]!=""){
    
      $cond .= " AND to_char(dt_fecha, 'dd/mm/yyyy') BETWEEN '".pg_escape_string($filtro["fecha_ini"])."' AND '".pg_escape_string($filtro["fecha_fin"])."' ";
    }
    
    $query = "SELECT dt_fecha, ".
                    "ch_almacen, ".
                    "ar_replicacion, ".
                    //"ar_cierre, ".
                    "fecha_actualizacion, ".
                    "usuario ".
             "FROM rep_ta_procesos_diarios ".
             " ".$cond." ".
	     "ORDER BY ch_almacen, fecha_actualizacion ";
	 //echo "QUERY : $query \n\n";
         $resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
	if($pp && $pagina)
	{
	   $paginador = new paginador($numrows,$pp, $pagina);
	}else{
	   $paginador = new paginador($numrows,100,0);
	}
	$listado2['partir'] = $paginador->partir();
	$listado2['fin'] = $paginador->fin();
	
	$listado2['numero_paginas'] = $paginador->numero_paginas();
	$listado2['pagina_previa'] = $paginador->pagina_previa();
	$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
	$listado2['pp'] = $paginador->pp;
	$listado2['paginas'] = $paginador->paginas();
	$listado2['primera_pagina'] = $paginador->primera_pagina();
	$listado2['ultima_pagina'] = $paginador->ultima_pagina();
	
        if ($pp > 0)
	    $query .= "LIMIT " . pg_escape_string($pp) . " ";
	if ($pagina > 0)
	    $query .= "OFFSET " . pg_escape_string($paginador->partir());
	    
    
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    
    while( $reg = $sqlca->fetchRow())
    {
        $listado['datos'][] = $reg;
    }    
    
    
    $listado['paginacion'] = $listado2;
    
    return $listado;
  }
  
}
