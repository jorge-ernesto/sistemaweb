<?php
  // Modelo para Tarjetas Magneticas

Class LadosModel extends Model{

	function guardarRegistro($lado, $prod1, $prod2, $prod3, $prod4, $ndec_cantidad, $ndec_precio, $ndec_importe, $ndec_contometro_cantidad, $ndec_contometro_importe, $idinterfase, $ladointerfase){
	
		global $sqlca;
		if(strlen($lado)>0)
		{
			$okgraba=true;

			$sql_busc = "select lado from pos_cmblados where trim(lado)='$lado'";
	
			$sqlca->query($sql_busc);
			if($sqlca->fetchRow()>0)
			{
				$okgraba=false; $mensaje="Error!! \\n Codigo ya Existe";
			}

			if($okgraba==true)
			{
				$sql_insert = "Insert into pos_cmblados (lado, prod1, prod2, prod3, prod4, ndec_cantidad, ndec_precio, ndec_importe, ndec_contometro_cantidad, ndec_contometro_importe, idinterfase,ladointerfase) values
				('".pg_escape_string($lado)."','".
				pg_escape_string($prod1)."','".
				pg_escape_string($prod2)."','".
				pg_escape_string($prod3)."','".
				pg_escape_string($prod4)."','".
				pg_escape_string($ndec_cantidad)."','".
				pg_escape_string($ndec_precio)."','".
				pg_escape_string($ndec_importe)."','".
				pg_escape_string($ndec_contometro_cantidad)."','".
				pg_escape_string($ndec_contometro_importe)."','".
				pg_escape_string($idinterfase)."','".
				pg_escape_string($ladointerfase)."')";
				
				echo $sql_insert;

				$sqlca->query($sql_insert);
				return '';	

			}else{
				return '0';
			}
		}else{
			return '0';
		}
	
	}
	
	function actualizarRegistro($lado, $prod1, $prod2, $prod3, $prod4, $ndec_cantidad, $ndec_precio, $ndec_importe, $ndec_contometro_cantidad, $ndec_contometro_importe, $idinterfase, $ladointerfase){

		global $sqlca;
		
		$query = "Update pos_cmblados set
		prod1 ='".pg_escape_string($prod1)."', 
		prod2 ='".pg_escape_string($prod2)."', 
		prod3 ='".pg_escape_string($prod3)."', 
		prod4 ='".pg_escape_string($prod4)."', 
		ndec_cantidad = '".pg_escape_string($ndec_cantidad)."', 
		ndec_precio ='".pg_escape_string($ndec_precio)."', 
		ndec_importe ='".pg_escape_string($ndec_importe)."', 
		ndec_contometro_cantidad = '".pg_escape_string($ndec_contometro_cantidad)."', 
		ndec_contometro_importe ='".pg_escape_string($ndec_contometro_importe)."' , 
		idinterfase ='".pg_escape_string($idinterfase)."' , 
		ladointerfase ='".pg_escape_string($ladointerfase)."'
		
		where lado ='".pg_escape_string($lado)."'";
	
		$result = $sqlca->query($query);
		return '';

	}

	function recuperarRegistroArray($registroid){

		global $sqlca;	
		$registro = array();
		$query = "SELECT lado, prod1, prod2, prod3, prod4, ndec_cantidad, ndec_precio, ndec_importe, ndec_contometro_cantidad, ndec_contometro_importe, idinterfase, ladointerfase
		FROM pos_cmblados
		WHERE lado= '". pg_escape_string($registroid) . "'";
	
		$sqlca->query($query);
	
		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}
	
		return $registro;

	}

	function eliminarRegistro($idregistro){
		global $sqlca;
		$query = "DELETE FROM pos_cmblados WHERE lado = '" . pg_escape_string($idregistro) . "';";
		$sqlca->query($query);
		return OK;
	}

  //Otras funciones para consultar la DB

	function tmListado($filtro=array(),$pp, $pagina){

		global $sqlca;
		$cond = '';
		$fil=strtoupper($filtro["parametro"]);
	
		if (!empty($fil)){
			$cond = " WHERE prod1 like trim('".pg_escape_string($fil)."%') OR 
			prod2 like trim('".pg_escape_string($fil)."%') OR 
			prod3 like trim('".pg_escape_string($fil)."%') OR 
			prod4 like trim('".pg_escape_string($fil)."%')";
		}
		
		$query = "SELECT lado,c1.ch_codigocombex,c2.ch_codigocombex,c3.ch_codigocombex,c4.ch_codigocombex,ndec_cantidad,ndec_precio,ndec_importe,ndec_contometro_cantidad,ndec_contometro_importe, (it.dispositivo || ' - ' || it.tipo), ladointerfase
		FROM pos_cmblados la 
		LEFT JOIN comb_ta_interfases it ON (la.idinterfase = it.id) 
		LEFT JOIN comb_ta_combustibles c1 ON (la.prod1 = c1.ch_codigocombex)
		LEFT JOIN comb_ta_combustibles c2 ON (la.prod2 = c2.ch_codigocombex)
		LEFT JOIN comb_ta_combustibles c3 ON (la.prod3 = c3.ch_codigocombex)
		LEFT JOIN comb_ta_combustibles c4 ON (la.prod4 = c4.ch_codigocombex)".$cond. " ORDER BY lado ";

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
	
		if ($pp > 0){$query .= "LIMIT " . pg_escape_string($pp) . " ";}
		if ($pagina > 0){$query .= "OFFSET " . pg_escape_string($paginador->partir());}
//echo $query;
		if ($sqlca->query($query)<=0){return $sqlca->get_error();}
	
		$listado[] = array();
	
		while( $reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
		}
		$listado['paginacion'] = $listado2;
//var_dump($listado);
		return $listado;

	}

//PARA COMBO ID INTERFASES
  function ListadoInterfases(){
    global $sqlca;
    $sqlca->query("SELECT id, dispositivo, tipo FROM comb_ta_interfases ORDER BY dispositivo;");
    $cbArray = array();
    $x=0;
    while($reg = $sqlca->fetchRow())
    {
       $cbArray[$reg[0]] = $reg[1] . " - " . $reg[2];
    }    
    return $cbArray;
  }

//PARA COMBO PRODUCTOS
  function ListadoProductos(){
    global $sqlca;
    $sqlca->query("SELECT ch_codigocombex FROM comb_ta_combustibles ORDER BY ch_codigocombex;");
    $cbArray = array();
    $cbArray[''] = '';
    $x=0;
    while($reg = $sqlca->fetchRow())
    {
       $cbArray[$reg[0]] = $reg[0];
    }    
    return $cbArray;
  }

}
