<?php
class ModelFormPrueba extends Model
{
  function tmListado($filtro=array(),$pp, $pagina)
  {
    global $sqlca;
    $cond = '';
    if ($filtro["codigo"] != ""){
      $cond = " WHERE trim(cli_codigo)||''||trim(cli_razsocial)||''||trim(cli_ruc) ~ '".$filtro["codigo"]."' ";
    }
    

    $query = "SELECT  cli_codigo, ".
                     "cli_razsocial, ".
		     "cli_rsocialbreve, ".
		     "cli_direccion, ".
		     "cli_ruc, ".
		     "cli_telefono1, ".
		     "cli_moneda, ".
		     "cli_distrito ".
             "FROM int_clientes ".
	     " ".$cond." ".
	     "ORDER BY cli_razsocial, cli_codigo ";
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
    
    $listado = array();
    //$listado['datos_pag']
    while( $reg = $sqlca->fetchRow())
    {
        $reg['cli_codigo'] = trim($reg['cli_codigo']);
        if(!empty($reg['cli_codigo']))
        $listado['datos'][] = $reg;
    }    
    
    
    $listado['paginacion'] = $listado2;
    //print_r($listado);
    
    return $listado;
  }
}

