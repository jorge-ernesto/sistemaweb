<?php
  // Modelo s

Class AgentePrinccontrModel extends Model{

  function eliminarRegistros()
  {
    global $sqlca;
    if($sqlca->perform('sunat_principal_contribuyente  ', ' ', 'delete', "ch_ruc > '0'")>=0){
    } else { return $sqlca->get_error(); }
    return OK;
  }
  
  function AgregarRegistros($datos)
  {
    global $sqlca;
    if($sqlca->perform('sunat_principal_contribuyente', $datos, 'insert')>=0){
    } else 
    { 
    //return $sqlca->get_error();
    $ErrorArray[]=$sqlca->get_error();
    return $ErrorArray;
    }
    return OK;
  }

  function tmListado($filtro=array(),$pp, $pagina)
  {
    global $sqlca;
    $cond = '';
    if ($filtro["codigo"] != ""){
      $cond = " WHERE trim(ch_ruc)||''||trim(ch_nombre_razon_social)||''||trim(nu_resolucion) ~ '".pg_escape_string($filtro["codigo"])."' ";
    }
    
//ch_ruc,ch_nombre_razon_social,dt_fecha_inicio,nu_resolucion
    $query = "SELECT  ch_ruc, ".
                     "ch_nombre_razon_social, ".
		     "dt_fecha_inicio, ".
		     "nu_resolucion ".
             "FROM sunat_principal_contribuyente ".
	     " ".$cond." ".
	     "ORDER BY ch_ruc, ch_nombre_razon_social ";
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
	    
    //echo "QUERYs : $query \n";
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

  function SubirArchivoTxt($filesunat_tmp, $filesunat)
  {
  global $sqlca;
    $ruta_local = "/sistemaweb/filessunat/";
    if(!empty($filesunat))
    {
	if (!@$ext)
	{
	    //$ext = basename($filesunat);
	    $ext = $_FILES['file_sunat']['type'];
	    //$ext = $filesunat_tmp;
	    $ext = explode(".", $filesunat);
	    $ext = $ext[1];
	}
    }else{
        return "Debe seleccionar el un archivo TXT, CSV o ZIP.";
    }
   
   if($ext=='txt' || $ext== 'csv' || $ext== 'zip')
   {
    if (is_uploaded_file($filesunat_tmp))
    {//INICIO :: IF - Verifica que el archivo se haya cargado corretamente
	
	if (copy($filesunat_tmp, $ruta_local.$filesunat))
	{//INICIO :: IF - Copiar el file al Servidor
            if($ext== 'zip')
            {
               exec('unzip -o '.$ruta_local.$filesunat.' -d '.$ruta_local.' ',$estado);
               //@@estado => arreglo de valores devueltos por el comando unzip.
               
               $nombre_real_file = $filesunat;

               $filename = explode('/', $estado[1]);
               $filesunat = trim($filename[3]);
               //@@filesunat => Obtine el nombre del archivo descomprimido.

               /* Obtiene la extensión del archivo descomprimido */
               $fz_ext = explode(".", $filesunat);
	       $fz_ext = $fz_ext[1];
	       /*------------------------------------------------*/
	       
	       /*Verificar si el zip enviado contiene un txt o cvs*/
	       if(empty($fz_ext) && ($fz_ext != 'txt' || $fz_ext != 'csv'))
	       {
	           exec('rm -fr '.$ruta_local.$filesunat.'');
	           exec('rm -fr '.$ruta_local.$nombre_real_file.'');
	           
	           return 'El archivo "'.$nombre_real_file.'" no contiene los datos correctos.';
	       }
	       /*----------------------------------------------*/

               $file = fopen($ruta_local.$filesunat, "r");
               //@@File => devuelve el archivo abierto en solo lectura.
               
            }else{
            
	       $file = fopen($ruta_local.$filesunat, "r");
	       //@@File => devuelve el archivo abierto en solo lectura.
	       
            }
            
	    while (!feof($file))
	    {
		$line = fgets($file);
		//@@line => obtiene la(s) lineas del archivo.
		$LineArray = $line;
		//@@LineArray => contiene las lineas en un arreglo.
	    }

	    $new_line = str_replace("\r", "<br>", $LineArray);
	    //echo $new_line;

	    $dividir = explode("<br>",$new_line);

	    foreach($dividir as $llave => $valor)
	    {
		if($llave!=0 && !empty($valor))
		{
		  $CampoArray[] = explode("|",$valor);
		}
	    }
	    
	    if(!is_array($CampoArray))
	    {
	       return 'Los datos son incorrectos.';
	    }
	    
	    if(!empty($CampoArray))
	    {
                AgentePrinccontrModel::eliminarRegistros();
                //@@Eliminar todos los registros de la tabla
            }
            //print_r($CampoArray);
	    foreach($CampoArray as $key => $campos)
	    {
		//echo "KEY :$key => VALUE : $campos[0] \n";
		$ip_remoto            = $_SERVER["REMOTE_ADDR"];
		$Ruc                  = $campos[0];
		$NombreRazonSocial    = pg_escape_string($campos[1]);
		$FechaInicio          = $campos[2];
		$NroResolucion        = pg_escape_string($campos[3]);
		
		$datos[$key]['ch_ruc']= $Ruc;
		$datos[$key]['ch_nombre_razon_social']= $NombreRazonSocial;
		$datos[$key]['dt_fecha_inicio']= $FechaInicio;
		$datos[$key]['nu_resolucion']= $NroResolucion;
		$datos[$key]['usuario']= $_SESSION['usuario'];
		$datos[$key]['auditor_ip']= $ip_remoto;
		$datos[$key]['fecha_actualizacion']= "now()";
		
		AgentePrinccontrModel::AgregarRegistros($datos[$key]);
	    }
        }//FIN :: IF - Copiar el file al Servidor
    return OK;
    }//FIN :: IF - Verifica que el archivo se haya cargado corretamente
    else
    {
    return "El Archivo no se ha cargado correctamente.";
    }
   }else{
    return "El archivo seleccionado no es el correcto. Debe ser TXT, CSV o ZIP.";
   }
  }
}
?>
