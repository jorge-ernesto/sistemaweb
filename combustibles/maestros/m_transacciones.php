<?php
  // Modelo para Tarjetas Magneticas

Class TransaccionesModel extends Model{

 
   //Otras funciones para consultar la DB

  function tmListado($filtro=array(),$pp, $pagina,$paran_ano, $param_mes)
  {
	global $sqlca;
	$where = '';
	 
	foreach($filtro as $col => $val)
	{
		if (trim($where)!="") 
		{
			if (trim($val)!="") 
			{
				$where.= " AND $col like '%$val%'";
			}
		}
		else
		{
			$where.= " WHERE $col like '%$val%'";
		}

	}
/*	 
    if ( !empty( $filtro["filtrado"] ) )
	{
		$cond = " WHERE trim(trans)||trim(dia)||trim(codigo)||trim(ruc) like '%".trim($filtro["filtrado"])."%' ";
    }
*/    
    $query = "SELECT  
				ES,
				tm, 
				td,
				caja, 
				trans, 
				fecha, 
				turno,
				codigo,
				cantidad,
				precio,
				igv,
				importe,
				ruc
				FROM pos_trans".$paran_ano.$param_mes.
	     		$where." 
			    ORDER BY 
				ES,fecha,caja,trans ";
		 
         $resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
	if($pp && $pagina){
		// echo "ENTRO 2\n REGPP : $pp \n PAG : $pagina\n";
		$paginador = new paginador($numrows,$pp, $pagina);
	}else{
		// echo "ENTRO 2 ELSE\n REGPP : $pp \n PAG : $pagina\n";
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
    
    $listado[] = array();
    
    while( $reg = $sqlca->fetchRow()){
      $listado['datos'][] = $reg;
    }    
        
    $listado['paginacion'] = $listado2;
   
    print_r($listado2);
    return $listado;
  }

  
  
}
