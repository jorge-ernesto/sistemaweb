<?php
  // Modelo para Aplicaciones de Cuentas por pagar

Class AplicacionesModel extends Model{
  
  //Otras funciones para consultar la DB

  function tmListadoCargos($filtro=array(),$pp, $pagina)
  {
  global $sqlca;
  echo "PP: $pp => PAG : $pagina\n";
    $cond = '';
    if ($filtro["codigo"] != "")
    {
      $cond = " AND trim(cc.pro_codigo)||trim(cc.pro_cab_seriedocumento)||trim(pro_cab_numdocumento)||trim(cc.pro_cab_tipdocumento) ~ '".pg_escape_string($filtro["codigo"])."' ";
    }
    
    $query = "SELECT cc.pro_codigo, ".
                    "cl.pro_razsocial, ".
                    "cc.pro_cab_tipdocumento, ".
                    "cc.pro_cab_seriedocumento, ".
                    "cc.pro_cab_numdocumento, ".
                    "cc.pro_cab_fechaemision, ".
                    "cc.pro_cab_fechasaldo, ".
                    "cc.pro_cab_moneda, ".
                    "cc.pro_cab_imptotal, ".
                    "cc.pro_cab_impsaldo ".
             "FROM cpag_ta_cabecera cc, int_proveedores cl ".
             "WHERE cc.pro_codigo=cl.pro_codigo ".
             "AND cc.pro_cab_impsaldo<>0 ".
             "AND trim(cc.pro_cab_tipcontable) = 'A' ".
             " ".$cond." ".
	     "ORDER BY cc.pro_cab_tipdocumento,cc.pro_cab_seriedocumento,cc.pro_cab_numdocumento ";
//	 echo "QUERY : $query \n\n";
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

  function tmListadoAbonos($codigo)
  {
    global $sqlca;
    $cond = '';
    if ($codigo != ""){
      $cond = " AND trim(pro_codigo) = '".pg_escape_string($codigo)."' ";
    }
    
    $query = "SELECT pro_cab_tipdocumento, ".
                    "pro_cab_seriedocumento, ".
                    "pro_cab_numdocumento, ".
                    "pro_cab_fechaemision, ".
                    "pro_cab_fechasaldo, ".
                    "pro_cab_moneda, ".
                    "pro_cab_imptotal, ".
                    "pro_cab_impsaldo ".
             "FROM cpag_ta_cabecera ".
             "WHERE pro_cab_impsaldo<>0 ".
             "AND trim(pro_cab_tipcontable) = 'C' ".
             " ".$cond." ".
	     "ORDER BY pro_cab_fechaemision DESC ";
    echo $query;
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    while( $reg = $sqlca->fetchRow())
    {
        $listado[] = $reg;
    }    
    return $listado;
  }
  
  function ActualizarCargos($CodCliente, $CodDocCargo,$NumDocCargo,$CodDocAbono,$NumDocAbono,$ImporteAplicacion)
  {
    global $sqlca;
    $FechaAplicacion = 'now()';

    echo "cod: " . $CodCliente;
    echo "cod: " . $CodDocCargo;
    echo "cod: " . $NumDocCargo;
    echo "cod: " . $CodDocAbono;
    echo "cod: " . $NumDocAbono;
    echo "imp: " . $ImporteAplicacion;
    
    $result = $sqlca->functionDB("cpag_fn_aplicaciones('".$CodDocCargo."','".$NumDocCargo."','".$CodDocAbono."','".$NumDocAbono."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");
    return OK;
  }

}
?>
