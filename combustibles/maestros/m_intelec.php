<?php
  // Modelo para Tarjetas Magneticas

Class IntelecModel extends Model{

  function ModelReportePDF($filtro = array()){
   global $sqlca;
    $cond = '';
	$fil=strtoupper($filtro["parametro"]);
   
    if (!empty($fil)){
     $cond = " where ch_nombre1 like trim('".pg_escape_string($fil)."%') OR 
		     ch_nombre2 like trim('".pg_escape_string($fil)."%') OR 
		      ch_apellido_paterno like trim('".pg_escape_string($fil)."%') OR 
		      ch_apellido_materno like trim('".pg_escape_string($fil)."%')";  
 	}
	
    /*$query = "select ch_codigo_trabajador,trim(ch_nombre1) || ' '||trim(ch_nombre2) as nom,ch_apellido_paterno, ch_apellido_materno,ch_sexo,ch_direccion,ch_telefono1,ch_documento_identidad,
 	dt_fecha_nacimiento from pla_ta_trabajadores ".$cond. " order by ch_apellido_paterno ";*/

	$query = "select id,dispositivo,tipo,sleep,maxsleep from comb_ta_interfases order by id;";
	
	var_dump($query);
         if ($sqlca->query($query) < 0) return null;
	$resultado = array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$fila = $sqlca->fetchRow();
		$resultado[$i] = $fila;
	}
    return $resultado;
  }
	

  function guardarRegistro($dispositivo,$tipo,$sleep,$maxsleep){
    global $sqlca;
	
//	settype($id,"integer");
	settype($sleep,"integer");
	settype($maxsleep,"integer");

	$query = "Insert into comb_ta_interfases(dispositivo,tipo,sleep,maxsleep)
	values('".pg_escape_string($dispositivo)."','".pg_escape_string($tipo)."',$sleep,$maxsleep)";
echo $query;
	
	print_r($query);
	if ($sqlca->query($query) < 0) 
	return '0';
	
	//$result = $sqlca->query($query);	
	return '';
	
  } 
	function actualizarRegistro($id,$dispositivo,$tipo,$sleep,$maxsleep){
    global $sqlca;

	settype($id,"integer");
	settype($sleep,"integer");
	settype($maxsleep,"integer");
	
	$query = "Update comb_ta_interfases set dispositivo='"
	.pg_escape_string($dispositivo). "',tipo ='"
	.pg_escape_string($tipo)."', sleep = $sleep, maxsleep = $maxsleep"
	.pg_escape_string($fechaNac)." where id = $id";echo $query;

	//print_r($query);
	$result = $sqlca->query($query);
	return '';
	
 	 }

  function recuperarRegistroArray($id){
    global $sqlca;
		
	settype($id,"integer");

    $registro = array();
    $query = "Select id,dispositivo,tipo,sleep,maxsleep FROM comb_ta_interfases WHERE id= $id";
	 
	 //print_r($query);
    $sqlca->query($query);

    while( $reg = $sqlca->fetchRow()){
		$registro = $reg;
	}
    return $registro;
  }

  /*function recuperarDetalledeClientenTarjetasMagneticas($cliente){
  	global $sqlca;
    $registro = array();
    $query=" ";
    
  }*/
  
  function eliminarRegistro($id){
    global $sqlca;
	settype($id,"integer");
    $query = "DELETE FROM comb_ta_interfases WHERE id = $id;";
    $sqlca->query($query);
    return OK;
  }


  //Otras funciones para consultar la DB

  function tmListado($filtro=array(),$pp, $pagina){
    global $sqlca;
    $cond = '';
	$fil=strtoupper($filtro["parametro"]);
   
    /*if (!empty($fil)){
     $cond = " where ch_nombre1 like trim('".pg_escape_string($fil)."%') OR 
		     ch_nombre2 like trim('".pg_escape_string($fil)."%') OR 
		     ch_apellido_paterno like trim('".pg_escape_string($fil)."%') OR 
		     ch_apellido_materno like trim('".pg_escape_string($fil)."%')";  
 	}*/
   	
    
    
    $query = "select id,dispositivo,tipo,sleep,maxsleep
 	from comb_ta_interfases ".$cond. " order by id ";
	
	
	$resultado_1 = $sqlca->query($query);
         $numrows = $sqlca->numrows();
	if($pp && $pagina){
		echo "ENTRO 2\n REGPP : $pp \n PAG : $pagina\n";
		$paginador = new paginador($numrows,$pp, $pagina);
	}else{
		echo "ENTRO 2 ELSE\n REGPP : $pp \n PAG : $pagina\n";
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
    return $listado;
  }

  
}
