<?php
  // Modelo para Tarjetas Magneticas

Class TrabajadorModel extends Model{

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
	
    $query = "select ch_codigo_trabajador,trim(ch_nombre1) || ' '||trim(ch_nombre2) as nom,ch_apellido_paterno, ch_apellido_materno,ch_sexo,ch_direccion,ch_telefono1,ch_documento_identidad,
 	dt_fecha_nacimiento from pla_ta_trabajadores ".$cond. " order by ch_apellido_paterno ";
	
	var_dump($query);
         if ($sqlca->query($query) < 0) return null;
	$resultado = array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$fila = $sqlca->fetchRow();
		$resultado[$i] = $fila;
	}
    return $resultado;
  }
	

  	function guardarRegistro($codigo,$nombre,$nombre2,$apellidoPat, $apellidoMat,$sexo,$direccion,$telefono,$dni,$fechaNac, $s_estado_trabajador){
		global $sqlca;
	
		$query = "Insert into pla_ta_trabajadores(ch_codigo_trabajador,ch_nombre1,ch_nombre2,ch_apellido_paterno,
		ch_apellido_materno,ch_sexo,ch_direccion,ch_telefono1,ch_documento_identidad, 
		dt_fecha_nacimiento,ch_tipo_contrato) values('".pg_escape_string($codigo)."','".pg_escape_string($nombre)."','".pg_escape_string($nombre2)."','".
                  pg_escape_string($apellidoPat)."','".pg_escape_string($apellidoMat)."','".
		  pg_escape_string($sexo)."','".pg_escape_string($direccion)."','".
		  pg_escape_string($telefono)."','".pg_escape_string($dni)."', to_date('".
		  pg_escape_string($fechaNac)."','dd/mm/yyyy'),'".pg_escape_string($s_estado_trabajador)."')";
	
		print_r($query);
		if ($sqlca->query($query) < 0) 
		return '0';
		
		$result = $sqlca->query($query);	
		return '';
	} 

	function actualizarRegistro($codigo,$nombre,$nombre2,$apellidoPat, $apellidoMat,$sexo,$direccion,$telefono,$dni,$fechaNac, $s_estado_trabajador){
    	global $sqlca;
	
		$query = "
UPDATE
 pla_ta_trabajadores
SET
 ch_nombre1 = '" . pg_escape_string($nombre) . "',
 ch_nombre2 ='" . pg_escape_string($nombre2) . "',
 ch_apellido_paterno = '" . pg_escape_string($apellidoPat) . "',
 ch_apellido_materno ='" . pg_escape_string($apellidoMat) . "',
 ch_sexo ='" . pg_escape_string($sexo) . "',
 ch_direccion = '" . pg_escape_string($direccion) . "',
 ch_telefono1='" . pg_escape_string($telefono) . "',
 ch_documento_identidad = '" . pg_escape_string($dni) . "',
 dt_fecha_nacimiento = to_date('" . pg_escape_string($fechaNac) . "','dd/mm/yyyy'),
 ch_tipo_contrato = '" . pg_escape_string($s_estado_trabajador) . "'
WHERE
 ch_codigo_trabajador='" . pg_escape_string($codigo) . "'
 		";
 		
		print_r($query);
		$result = $sqlca->query($query);
		return '';
 	}

	function recuperarRegistroArray($registroid){
    	global $sqlca;
		
	    $registro = array();
	    $query = "
	    SELECT
	    	ch_codigo_trabajador,
	    	ch_nombre1,
	    	ch_nombre2,
	    	ch_apellido_paterno,
			ch_apellido_materno,
			ch_sexo,
			ch_direccion,
			ch_telefono1,
			ch_documento_identidad,
	 	 	dt_fecha_nacimiento,
	 	 	ch_tipo_contrato
	 	FROM
	 		pla_ta_trabajadores
	 	WHERE
	 		ch_codigo_trabajador='".pg_escape_string($registroid)."'
	 	";
		 
		//print_r($query);
	    $sqlca->query($query);

	    while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}	    
	    return $registro;
	}

  function recuperarDetalledeClientenTarjetasMagneticas($cliente){
  	global $sqlca;
    $registro = array();
    $query=" ";
    
  }
  
  function eliminarRegistro($idregistro){
    global $sqlca;
/*    $sql = "DELETE FROM pla_ta_asistencia WHERE ch_codigo_trabajador = '" . pg_escape_string($idregistro) . "';";
    $sqlca->query($sql);*/
    $query = "DELETE FROM pla_ta_trabajadores WHERE ch_codigo_trabajador = '" . pg_escape_string($idregistro) . "';";
    $sqlca->query($query);
    return OK;
  }


  //Otras funciones para consultar la DB

  function tmListado($filtro=array(),$pp, $pagina){
    global $sqlca;
    $cond = '';
	$fil=strtoupper($filtro["parametro"]);
   
    if (!empty($fil)){
     $cond = " where ch_nombre1 like trim('".pg_escape_string($fil)."%') OR 
		     ch_nombre2 like trim('".pg_escape_string($fil)."%') OR 
		     ch_apellido_paterno like trim('".pg_escape_string($fil)."%') OR 
		     ch_apellido_materno like trim('".pg_escape_string($fil)."%')";  
 	}
   	
    
    
    $query = "select ch_codigo_trabajador,ch_nombre1 ||' '|| ch_nombre2 , ch_apellido_paterno
	|| ' ' || ch_apellido_materno,ch_sexo,ch_direccion,ch_telefono1,ch_documento_identidad,
 	dt_fecha_nacimiento, (CASE WHEN ch_tipo_contrato IS NULL OR ch_tipo_contrato = '0' THEN 'ACTIVO' ELSE 'INACTIVO' END) AS no_estado_trabajador from pla_ta_trabajadores ".$cond. " order by ch_codigo_trabajador ";
	
	
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
