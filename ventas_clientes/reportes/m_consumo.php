<?php

class ConsumoModel extends Model
{
    function Listado($filtro=array(),$pp, $pagina)
    {
	global $sqlca;
	if(!$filtro['desde']) $filtro['desde']="01".date('/m/Y');
        if(!$filtro['hasta']) $filtro['hasta']=date('d/m/Y');
	
	$cond = '';
	if($filtro['cli_codigo']!='')
	$cond = "AND cab.ch_cliente = '".$filtro['cli_codigo']."' ";
	
	if($filtro['desde']!='' && $filtro['hasta']!='')
	$cond .= "AND det.dt_fecha >= to_date('".$filtro['desde']."', 'DD/MM/YYYY') ".
		"AND det.dt_fecha <= to_date('".$filtro['hasta']."', 'DD/MM/YYYY') ";
	
	$query = "SELECT ".
		    "cab.dt_fecha, ".
	            "cab.ch_cliente||' '||trim(cli.cli_rsocialbreve) as cliente, ".
		    "cab.ch_documento, ".
		    "cab.ch_placa, ".
		    "trim(art.art_descripcion) as articulo, ".
		    "cab.ch_liquidacion, ".
		    "cab.nu_odometro, ".
		    "cab.ch_tarjeta, ".
		    "41 as cod_sunat, ".
		    "det.nu_cantidad, ".
		    "det.nu_importe/det.nu_cantidad as nu_precio, ".
		    "det.nu_importe ".
		"FROM ".
		    "val_ta_cabecera cab, ".
		    "val_ta_detalle det, ".
		    "int_articulos art, ".
		    "int_clientes cli ".
		"WHERE ".
		    "det.ch_articulo = art.art_codigo ".
		"AND cab.ch_sucursal = det.ch_sucursal ".
		"AND cab.dt_fecha = det.dt_fecha ".
		"AND cab.ch_documento = det.ch_documento ".
		"AND cab.ch_cliente = cli.cli_codigo ".
		"".$cond."".
		"ORDER BY det.dt_fecha ";

         $resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
	if(!empty($pp) && $pagina>=0)
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
	    
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    
    $listado[] = array();
    while( $reg = $sqlca->fetchRow())
    {
        $listado['datos'][] = $reg;
    }    
    
    
    $listado['paginacion'] = $listado2;

    return $listado;
    }
    
  function ClienteCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();

    $query = "SELECT cli_codigo, cli_rsocialbreve FROM int_clientes ".
    @$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 1';

    if ($sqlca->query(@$query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_rsocialbreve"];
    }
    ksort($cbArray);
    return $cbArray;
  }

}

