<?php
  // Modelo para Eliminacion de Cuentas por cobrar

class EliminacionModel extends Model{
  
  //Otras funciones para consultar la DB

	  function tmListado($filtro=array()){
	  	global $sqlca;
		$cond = ' and 2 ';

		if ($filtro["codigo"] != ""){
			$cond = " AND trim(cl.cli_razsocial)||''||trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(ch_numdocumento) ~ '".pg_escape_string($filtro["codigo"])."' ";
		}

		$query = "SELECT ".
							"cc.ch_tipdocumento, ".
							"cc.ch_seriedocumento, ".
							"cc.ch_numdocumento, ".
							"cc.ch_tipmovimiento, ".
							"cc.cli_codigo, ".
							"cl.cli_razsocial, ".
							"cc.dt_fechamovimiento, ".
							"cc.dt_fecha_actualizacion, ".
							"cc.ch_moneda, ".
							"cc.nu_importemovimiento, ".
							"ch_tipdocreferencia, ".
							"cc.ch_numdocreferencia, cc.ch_identidad, cc.tipocanje ".
					 "FROM ccob_ta_detalle cc, int_clientes cl ".
					 "WHERE cc.cli_codigo=cl.cli_codigo AND cc.ch_tipmovimiento = '2' ".
					 " ".$cond." ".
				 "ORDER BY  cc.dt_fechamovimiento desc ";
		  echo $query;

		    $resultado_1 = $sqlca->query($query);
		    $numrows = $sqlca->numrows();
	
		    if ($sqlca->query($query)<=0){
		      return $sqlca->get_error();
		    }
		    if($filtro["codigo"] != "")
			{
				while( $reg = $sqlca->fetchRow())
				{
					$listado['datos'][] = $reg;
				}    
		    }
	    
	   	   // $listado['paginacion'] = $listado2;
	    
	    return $listado;
	  }

	  function eliminarRegistro($idregistro,$tipoMov,$codigo){
	    global $sqlca;

	    if ($sqlca->functionDB("ccobrar_fn_inserta_temporal_ccobrar('".$idregistro."', '".$tipoMov."')")){
	    	print_r('grabo');
	    }
		if($tipoMov == '2'){
			if ($sqlca->perform('ccob_ta_detalle  ', ' ', 'delete', "trim(cli_codigo)||''||trim(ch_tipdocumento)||''||trim(ch_seriedocumento)||''||trim(ch_numdocumento)||''||trim(ch_identidad)='$idregistro'")>=0){
			} else { return $sqlca->get_error(); }
		}
		//echo $sqlca;
	    return OK;
	  }
  
	function ClientesCBArray($condicion=''){
	    global $sqlca;
	    $cbArray = array();
	    $query = "SELECT cli_codigo,cli_razsocial,cli_rsocialbreve FROM int_clientes ".
	    $query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';
	    if ($sqlca->query($query)<=0)
	      return $cbArray;
	    while($result = $sqlca->fetchRow()){
	      $cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_rsocialbreve"];
	    }
	    ksort($cbArray);
	    return $cbArray;
	  }

	function EliminarCuenta($codigo, $tipo, $serie, $numero, $importe){
		global $sqlca;

		$sql="DELETE FROM
				ccob_ta_detalle
			WHERE
				cli_codigo = '$codigo' AND
				ch_tipdocumento = '$tipo' AND
				ch_seriedocumento = '$serie' AND
				ch_numdocumento = '$numero' AND
				nu_importemovimiento = '$importe' ";
		echo $sql;

		$sqlca->query($sql);

		return ok;
	}

}
