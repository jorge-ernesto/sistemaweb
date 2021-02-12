<?php

class FormsModel extends Model{

	function Consolidacion($iAlmacen, $sFecha) {
		// echo "<script>console.log('" . json_encode(array($iAlmacen, $sFecha)) . "')</script>";
		global $sqlca;
		$turno = 0;
		$d = explode("/", $sFecha);
		$dia = $d[2]."-".$d[1]."-".$d[0];
		// echo "<script>console.log('" . json_encode(array($dia, $turno, $iAlmacen)) . "')</script>";
		$sql = "SELECT validar_consolidacion('" . $dia . "', " . $turno . ", '" . $iAlmacen . "');";
		$sqlca->query($sql);
		$estado = $sqlca->fetchRow();
		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}
	}

  	function IngRecordPendReg($ObtDatos) {
    		global $sqlca, $usuario;
    		
    		print_r($ObtDatos);
    		for($x=0; $x<count($ObtDatos); $x++) {
	      $Datos['nu_regularizacion']       = $ObtDatos[$x]['nu_regularizacion'];
	      $Datos['dt_fecha']                = "now()";
	      $Datos['ch_almacen']              = $ObtDatos[$x]['mov_almacen'];
	      $Datos['ch_tipo_regul']           = $_REQUEST['tipo_regularizacion'];
	      $Datos['ch_numero_refe']          = $ObtDatos[$x]['mov_docurefe'];
	      $Datos['ch_observacion']          = $_REQUEST['observacion'];
	      $Datos['dt_fecha_mov']            = $ObtDatos[$x]['mov_fecha'];
	      $Datos['ch_tran_inicial']         = $ObtDatos[$x]['ch_tran_inicial'];
	      $Datos['ch_numero_inicial']       = $ObtDatos[$x]['ch_numero_inicial'];
	      $Datos['ch_tran_final']           = $ObtDatos[$x]['ch_tran_final'];
	      $Datos['ch_numero_final']         = $ObtDatos[$x]['ch_numero_final'];
	      $Datos['ch_cod_articulo']         = $ObtDatos[$x]['art_codigo'];
	      $Datos['ch_estado_reg']           = "false";
	      $Datos['ch_tran_ok']              = "null";
	      $Datos['ch_numero_ok']            = "null";
	      $Datos['ch_auditor_ip']           = $_SERVER['REMOTE_ADDR'];
	      $Datos['dt_fecha_actualizacion']  = "now()";
	      $Datos['ch_usuario']              = $usuario->obtenerUsuario();
	      print_r($Datos);
	      $Result = $sqlca->perform('inv_ta_regularizacion', $Datos, 'insert', '', true);
		if ($Result['exec']>= 0){
		  //return OK;
		  echo "QUERY : ".$Result['query']."\n";
		} else { echo "Error"; }//return $sqlca->get_error(); }
	    }
  	}

  	function ObtRegistro($registroid){
	  global $sqlca;
	  
	    $cond = "WHERE tran_codigo||mov_numero||mov_almacen='".$registroid."' ";
	    if(empty($registroid)) return -1;
	    $query = "SELECT * ".
		     "FROM inv_movialma ".
		     "".$cond."";
	    echo "QUERY : $query\n";
	    if ($sqlca->query($query) < 0) return -1;
	    $registros = $sqlca->fetchAll();
	    var_dump($registros);
	    //print_r($registros);
	  return $registros;
  	}
  
  	function obtenerNuevoNumeroFormulario($tipo) {
	      global $sqlca;
	      
	      $sql = "SELECT ".
			  "tran_nform ".
		      "FROM ".
			  "inv_tipotransa ".
		      "WHERE ".
			  "tran_codigo='" . pg_escape_string($tipo) . "'";

	      if ($sqlca->query($sql) < 0) return -1;
	      
	      $array = $sqlca->fetchRow();
	      
	      $numero = $array[0];
	      
	      $sql = "UPDATE ".
			  "inv_tipotransa ".
		      "SET ".
			  "tran_nform='" . pg_escape_string($numero+1) . "' ".
		      "WHERE ".
			  "tran_codigo='" . pg_escape_string($tipo) . "'";

	      if ($sqlca->query($sql) < 0) return -1;
	      
	      return str_pad($numero, 7, '0', STR_PAD_LEFT);
  	}
  
  	function VerificarExisFactura($Codigo, $CodArticulo='')  {
	      global $sqlca;
	      if($CodArticulo!='')
	      {
		  $AddCond = "||art_codigo = '".$Codigo.$CodArticulo."'";
	      }else{
		  $AddCond = " = '".$Codigo."'";
	      }
	      
	      $query = "SELECT ".
			      "cpag_tipo_pago||cpag_serie_pago||cpag_num_pago as facturado ".
			"FROM inv_ta_compras_devoluciones ".
			"WHERE tran_codigo||mov_numero".$AddCond;
	  
	      if ($sqlca->query($query) < 0) return -1;
	      
	      $array = $sqlca->fetchRow();
	      
	      $Dato = $array[0];
	      return $Dato;
  	}
  
  	function VerificarNaturaleza($TipoTransa, $Naturaleza) {
	  global $sqlca;
	  
	      $TipoNaturaleza['1'] = "ENTRADA";
	      $TipoNaturaleza['2'] = "ENTRADA";
	      $TipoNaturaleza['3'] = "SALIDA";
	      $TipoNaturaleza['4'] = "SALIDA";

	      $query = "SELECT tran_naturaleza ".
			"FROM inv_tipotransa ".
			"WHERE tran_codigo = '".trim($TipoTransa)."'";
	  
	      if ($sqlca->query($query) < 0) return -1;
	      
	      $array = $sqlca->fetchRow();
		      
	      $Dato = $array[0];
	      //echo "DATO ANTES : $Dato \n";
	      //echo "NATURALEZA ANTES : $Naturaleza \n";
	      $Dreturn['VNAT'] = $Dato;
	      $Dato = $TipoNaturaleza[trim($Dato)];
	      $Naturaleza = $TipoNaturaleza[trim($Naturaleza)];
	      //echo "DATO : $Dato \n";
	      //echo "NATURALEZA : $Naturaleza \n";
	      if($Dato != $Naturaleza)
	      {
		  $Dreturn['RESULT'] = "NEGATIVO";
		  return $Dreturn;
	      }
	      $Dreturn['RESULT'] = "POSITIVO";
	      return $Dreturn;

  	}
  
  	function cambiarNumeroFormularioArticulo($DatosTrans, $Datos) {
	      global $sqlca;
	      global $usuario;
	      global $_SESSION;

	      $new_numero = FormsModel::obtenerNuevoNumeroFormulario($DatosTrans['new_trancodigo']);

	      if ($new_numero < 0) return -1;
	      
	      $Verificacion = FormsModel::VerificarNaturaleza($DatosTrans['new_trancodigo'], $DatosTrans['naturaleza']);
	      
	      if($Verificacion['RESULT'] == "NEGATIVO")
	      {
		  $addSql = "mov_cantidad =  mov_cantidad * -1, ";
		  
	      }elseif($Verificacion['RESULT'] == "POSITIVO"){
	      
		  $addSql = "mov_cantidad = ".$DatosTrans['cantidad'].", ";
	      }
	      
	      $sql = "UPDATE ".
			  "inv_movialma ".
		      "SET ".
			  "flg_replicacion = 0, ".
			  "mov_numero='".$usuario->obtenerAlmacenActual().pg_escape_string($new_numero)."', ".
			  "".$addSql."".
			  "mov_naturaleza = '".trim($Verificacion['VNAT'])."', ".
			  "tran_codigo='" . pg_escape_string($DatosTrans['new_trancodigo']) . "', ".
			  "mov_fecha_actualizacion = now(), ".
			  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
		      "WHERE ".
			  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
			  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' ".
			  "AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";
	//      echo "QUERY : $sql \n";
	//      VariosModel::SentenciasReplicacion($sql, $Datos);
	      if ($sqlca->query($sql) < 0) return -1;
	      if(trim($DatosTrans['new_trancodigo']) == '18')
	      {
	//        echo "ENTRO 18\n";
	echo "Almacen: " . $usuario->obtenerAlmacenActual() . "\n";
	echo "Nuevo numero: " . $new_numero . "\n";
	echo "Almacen: " . $_REQUEST['almacen'] . "\n";

		$registroid = $DatosTrans['new_trancodigo'].$usuario->obtenerAlmacenActual().pg_escape_string($new_numero).trim($_REQUEST['almacen']);
		$ObtDatos = array();
		$ObtDatos = FormsModel::ObtRegistro($registroid);
		for($x=0; $x < count($ObtDatos); $x++)
		{
		  $ObtDatos[$x]['nu_regularizacion']  = $DatosTrans['nu_regularizacion'];
		  $ObtDatos[$x]['ch_tran_inicial']    = $DatosTrans['old_trancodigo'];
		  $ObtDatos[$x]['ch_numero_inicial']  = $DatosTrans['numero'];
		  $ObtDatos[$x]['ch_tran_final']      = $DatosTrans['new_trancodigo'];
		  $ObtDatos[$x]['ch_numero_final']    = $usuario->obtenerAlmacenActual().pg_escape_string($new_numero);
		}
		FormsModel::IngRecordPendReg($ObtDatos);
	      }
	      return $new_numero;	
  	}
  
  	function cambiarDestinoFormularioArticulo($DatosTrans, $Datos)  {
	      global $sqlca;
	      global $usuario;
	      global $_SESSION;
	      
	      $sql = "UPDATE ".
			  "inv_movialma ".
		      "SET ".
			  "flg_replicacion = 0, ".
			  "mov_almadestino='".pg_escape_string($DatosTrans['new_destino'])."', ".
			  "mov_fecha_actualizacion = now(), ".
			  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
		      "WHERE ".
			  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
			  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' ".
			  "AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";
	      //echo "QUERY : $sql \n";
	//      VariosModel::SentenciasReplicacion($sql, $Datos);
	      if ($sqlca->query($sql) < 0) return -1;
	      
	      return $new_destino;
  	}

  	function cambiarOrigenFormularioArticulo($DatosTrans, $Datos)  {
	      global $sqlca;
	      global $usuario;
	      global $_SESSION;
	      
	      $sql = "UPDATE ".
			  "inv_movialma ".
		      "SET ".
			  "flg_replicacion = 0, ".
			  "mov_almaorigen='".pg_escape_string($DatosTrans['new_origen'])."', ".
			  "mov_fecha_actualizacion = now(), ".
			  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
		      "WHERE ".
			  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
			  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' ".
			  "AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";
	      //echo "QUERY : $sql \n";
	//      VariosModel::SentenciasReplicacion($sql, $Datos);
	      if ($sqlca->query($sql) < 0) return -1;
	      
	      return $new_origen;
  	}
  
  function cambiarNumeroFormulario($DatosTrans, $Datos)
  {
      global $sqlca;
      global $usuario;
      global $_SESSION;
      
      $new_numero = FormsModel::obtenerNuevoNumeroFormulario($DatosTrans['new_trancodigo']);

      if ($new_numero < 0) return -1;
      
      $Verificacion = FormsModel::VerificarNaturaleza($DatosTrans['new_trancodigo'], $DatosTrans['naturaleza']);
      
      if($Verificacion['RESULT'] == "NEGATIVO")
      {
	  $addSql = "mov_cantidad = mov_cantidad * -1, ";
	  
      }elseif($Verificacion['RESULT'] == "POSITIVO"){
      
	  $addSql = "mov_cantidad = mov_cantidad, ";
      }

      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_numero='" . $usuario->obtenerAlmacenActual().pg_escape_string($new_numero) . "', ".
		  "".$addSql."".
		  "mov_naturaleza = '".trim($Verificacion['VNAT'])."', ".
		  "tran_codigo='" . pg_escape_string($DatosTrans['new_trancodigo']) . "', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      if(trim($DatosTrans['new_trancodigo']) == '18')
      {
        //echo "ENTRO 18\n";
        $registroid = $DatosTrans['new_trancodigo'].$usuario->obtenerAlmacenActual().pg_escape_string($new_numero).trim($_REQUEST['almacen']);
	$ObtDatos = array();
	$ObtDatos = FormsModel::ObtRegistro($registroid);
	//echo "REG REC ".count($ObtDatos)."\n";
	for($x=0; $x < count($ObtDatos); $x++)
	{
	  $ObtDatos[$x]['nu_regularizacion']  = $DatosTrans['nu_regularizacion'];
	  $ObtDatos[$x]['ch_tran_inicial']    = $DatosTrans['old_trancodigo'];
	  $ObtDatos[$x]['ch_numero_inicial']  = $DatosTrans['numero'];
	  $ObtDatos[$x]['ch_tran_final']      = $DatosTrans['new_trancodigo'];
	  $ObtDatos[$x]['ch_numero_final']    = $usuario->obtenerAlmacenActual().pg_escape_string($new_numero);
	}
	//echo "ANTES\n";
	//print_r($ObtDatos);
	//echo "ANTES FIN \n";
	FormsModel::IngRecordPendReg($ObtDatos);
      }

      return $new_numero;	
  }

  function cambiarOrigenFormulario($DatosTrans, $Datos)
  {
      global $sqlca;
      global $usuario;
      global $_SESSION;
      
      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_almaorigen='".pg_escape_string($DatosTrans['new_origen'])."', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      
      return $new_origen;	
  }

  function cambiarDestinoFormulario($DatosTrans, $Datos)
  {
      global $sqlca;
      global $usuario;
      global $_SESSION;
      
      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_almadestino='".pg_escape_string($DatosTrans['new_destino'])."', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      
      return $new_destino;	
  }

	function cambiarFechaFormulario($DatosTrans, $Datos) {
      		global $sqlca;
      		global $usuario;
      		global $_SESSION;
      		echo "entra aki";
      		$new_fecha_arr = explode("/", $DatosTrans['new_fecha']);
      		$diav = $new_fecha_arr[2]."-".$new_fecha_arr[1]."-".$new_fecha_arr[0];
      		$new_fecha = $new_fecha_arr[2]."/".$new_fecha_arr[1]."/".$new_fecha_arr[0];
      		$new_fecha = $new_fecha." 00:00:00";
      		
      		$flag = FormsModel::validaDia($diav); // flag=0: Menor a fecha de inventario   flag=1: mayor a fecha actual   flag=2: correcto
     		
      		if($flag=="0") {
      			return "0";
      		} else {
      			if($flag=="1") {
      				return "1";
      			} else {
      		      		
		      		$sql = "UPDATE 
				  		inv_movialma 
			      		SET 
				  		flg_replicacion = 0, 
				  		mov_fecha='".pg_escape_string($new_fecha)."', 
				  		mov_fecha_actualizacion = now(), 
				  		mov_usuario = '".$usuario->obtenerUsuario()."' 
			      		WHERE 
				  		mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' 
				  		AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'
					  	AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";
				
				$query = "UPDATE 
				  		inv_calculo_glp 
			      		SET 
				  		mov_fecha='".pg_escape_string($new_fecha)."'
			      		WHERE 
				  		mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' 
				  		AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'
					  	AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";

		      		if ($sqlca->query($sql) < 0){ 
		      			return -1;
					if($sqlca->query($query)<0){
						return -1;
					}
				}else{
					return $new_fecha;
				}

		      	}
		}
  	}

  	function cambiarFechaFormularioArticulo($DatosTrans, $Datos)  {
      		global $sqlca;
      		global $usuario;
      		global $_SESSION;
      		
      		$new_fecha_arr = explode("/", $DatosTrans['new_fecha']);
      		$old_fecha_arr = explode("/", $DatosTrans['old_fecha']);
      		$diav = $new_fecha_arr[2]."-".$new_fecha_arr[1]."-".$new_fecha_arr[0];
      		$diao = $old_fecha_arr[2]."-".$old_fecha_arr[1]."-".$old_fecha_arr[0];
      		$new_fecha = $new_fecha_arr[2]."/".$new_fecha_arr[1]."/".$new_fecha_arr[0];
      		$new_fecha2 = $diav." 00:00:00";
      
      		$sql = "UPDATE 
			  	inv_movialma 
		     	SET 
			  	flg_replicacion = 0, 
			  	mov_fecha='".pg_escape_string($new_fecha2)."', 
			  	mov_fecha_actualizacion = now(), 
			  	mov_usuario = '".$usuario->obtenerUsuario()."' 
		      	WHERE 
			  	mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' 
			  	AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' 
			  	AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'
				AND mov_fecha::DATE='" . pg_escape_string($diao) . "'
			  	";

		$sql1 = "UPDATE 
			  	inv_calculo_glp 
		     	SET 
			  	mov_fecha='".pg_escape_string($new_fecha2)."'
		      	WHERE 
			  	mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' 
			  	AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' 
			  	AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'
				AND mov_fecha::DATE='" . pg_escape_string($diao) . "'
			  	";

		echo $sql;
		if ($sqlca->query($sql) < 0) 
			return -1;
			if ($sqlca->query($sql1) < 0) 
				return -1;
		
		return $new_fecha;   			      	

		
  	}

  function cambiarNroReferFormulario($DatosTrans, $Datos)
  {
      global $sqlca;
      global $usuario;
      global $_SESSION;
      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_docurefe='".pg_escape_string($DatosTrans['new_nrefer'])."', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      
      return $DatosTrans['new_nrefer'];	
  }

  function cambiarTipoDocFormulario($DatosTrans, $Datos)  {
  
      global $sqlca;
      global $usuario;
      global $_SESSION;
      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_tipdocuref='".pg_escape_string($DatosTrans['new_tipo_doc'])."', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      
      return $DatosTrans['new_tipo_doc'];	
  }

  function cambiarTipoDocFormularioArticulo($DatosTrans, $Datos)
  {
      global $sqlca;
      global $usuario;
      global $_SESSION;
      
      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_tipdocuref='".pg_escape_string($DatosTrans['new_tipo_doc'])."', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' ".
		  "AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      
      return $DatosTrans['new_tipo_doc'];
  }

  function cambiarNroReferFormularioArticulo($DatosTrans, $Datos)
  {
      global $sqlca;
      global $usuario;
      global $_SESSION;
      
      $sql = "UPDATE ".
		  "inv_movialma ".
	      "SET ".
	          "flg_replicacion = 0, ".
		  "mov_docurefe='".pg_escape_string($DatosTrans['new_nrefer'])."', ".
		  "mov_fecha_actualizacion = now(), ".
		  "mov_usuario = '".$usuario->obtenerUsuario()."' ".
	      "WHERE ".
		  "mov_numero='" . pg_escape_string($DatosTrans['numero']) . "' ".
		  "AND tran_codigo='" . pg_escape_string($DatosTrans['old_trancodigo']) . "' ".
		  "AND art_codigo='" . pg_escape_string($DatosTrans['art_codigo']) . "'";
      //echo "QUERY : $sql \n";
//      VariosModel::SentenciasReplicacion($sql, $Datos);
      if ($sqlca->query($sql) < 0) return -1;
      
      return $new_nrefer;
  }

  function obtenerTipoTransacciones()
  {
      global $sqlca;
      
      $sql = "SELECT
		  tran_codigo,
		  tran_descripcion
	      FROM
		  inv_tipotransa
	      ORDER BY tran_codigo ";
      if ($sqlca->query($sql) < 0) return null;
      
      $result = Array();

      for ($i = 0; $i < $sqlca->numrows(); $i++) {
	  $array = $sqlca->fetchRow();
	  $codigo = $array[0];
	  $desc = $array[1];
	  
	  $result[$codigo] = $codigo . " - " . $desc;
      }
      
      return $result;
  }
  
  function obtenerTipoAlmacen($Tipo)
  {
      global $sqlca;
      
      $sql = "SELECT
		  ch_almacen,
		  ch_nombre_almacen
	      FROM
		  inv_ta_almacenes
	      WHERE ch_clase_almacen='".$Tipo."'
	      ORDER BY ch_almacen ";
      if ($sqlca->query($sql) < 0) return null;
      
      $result = Array();

      for ($i = 0; $i < $sqlca->numrows(); $i++) {
	  $array = $sqlca->fetchRow();
	  $codigo = $array[0];
	  $desc = $array[1];
	  
	  $result[$codigo] = $codigo . " - " . $desc;
      }
      
      return $result;
  }

  function obtenerListado($fecha, $tipo, $almacen, $desde, $cuenta)
  {
      global $sqlca;
      global $usuario;
      $sql = "SELECT ".
		  "inv.tran_codigo, ".
		  "inv.mov_numero, ".
		  "inv.art_codigo, ".
		  "inv.mov_cantidad, ".
		  "inv.mov_fecha, ".
		  "ar.art_descbreve, ".
		  "inv.mov_almaorigen, ".
		  "inv.mov_almadestino, ".
		  "inv.mov_docurefe as tip_doc_ref, ".
		  "inv.mov_naturaleza, ".
		  "inv.mov_almacen, ".
		  "TD.tab_desc_breve AS no_tipo_documento, ".
		  "SUBSTR(inv.mov_docurefe, 1, 4) AS nu_serie_documento, ".
		  "SUBSTR(inv.mov_docurefe, 5, 8) AS nu_numero_documento ".
	      "FROM ".
		  "inv_movialma inv ".
		  "LEFT JOIN int_tabla_general AS TD ON(inv.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000'), ".
		  "int_articulos ar ".
	      "WHERE ".
		  "inv.art_codigo=ar.art_codigo ".
		  "AND to_char(inv.mov_fecha,'dd/mm/yyyy')='".pg_escape_string($fecha)."' ".
		  "AND inv.tran_codigo='".pg_escape_string(trim($tipo))."' ".
		  "AND inv.mov_almacen='".pg_escape_string($almacen)."'".
	      "ORDER BY inv.mov_numero, inv.art_codigo, inv.mov_fecha ";
      


      if ($desde > 0) $sql .= "OFFSET ".pg_escape_string($desde)." ";
      if ($cuenta > 0) $sql .= "LIMIT ".pg_escape_string($cuenta)." ";
      $sql .= ";";

      if ($sqlca->query($sql) < 0) return null;
      
      $result = Array();
      
      for ($i = 0; $i < $sqlca->numrows(); $i++) {
	  $array = $sqlca->fetchRow();

	  $tran_codigo = $array[0];
	  $mov_numero = $array[1];
	  $art_codigo = $array[2];
	  $art_descrip = $array[5];
	  $mov_cantidad = $array[3];
	  $mov_naturaleza = $array[9];
	  $mov_fecha = $array[4];
	  $mov_fecha = explode(" ", $mov_fecha);
	  $mov_fecha_arr = explode("-", $mov_fecha[0]);
	  $mov_fecha = $mov_fecha_arr[2]."/".$mov_fecha_arr[1]."/".$mov_fecha_arr[0];
	  $codigo = $tran_codigo . $mov_numero . "," . $art_codigo . "," . $mov_cantidad . "," . $mov_naturaleza . "," . $tran_codigo . $mov_numero . $art_codigo . $array[10] . $array[4];

	  $no_tipo_documento = $array[11];
	  $nu_serie_documento = $array[12];
	  $nu_numero_documento = $array[13];

	  $result[$codigo]['tran_codigo'] = $tran_codigo;
	  $result[$codigo]['mov_numero'] = $mov_numero;
	  $result[$codigo]['art_codigo'] = $art_codigo;
	  $result[$codigo]['mov_cantidad'] = $mov_cantidad;
	  $result[$codigo]['mov_fecha'] = $mov_fecha;
	  $result[$codigo]['art_descrip'] = $art_descrip;
	  $result[$codigo]['alm_origen'] = $array[6];
	  $result[$codigo]['alm_destino'] = $array[7];
	  $result[$codigo]['tip_doc_ref'] = $array[8];
	  $result[$codigo]['mov_naturaleza'] = $mov_naturaleza;

	  $result[$codigo]['no_tipo_documento'] = $no_tipo_documento;
	  $result[$codigo]['nu_serie_documento'] = $nu_serie_documento;
	  $result[$codigo]['nu_numero_documento'] = $nu_numero_documento;
	  //$result[$codigo]['mov_fecha_completa'] = $array[4];
      }
      
      return $result;
  }
  
  function obtieneAlmacenes()
  {
      global $sqlca;
      
      $sql = "SELECT
		  ch_almacen,
		  ch_almacen||' '||ch_nombre_almacen
	      FROM
		  inv_ta_almacenes
	      WHERE
		  ch_clase_almacen='1'
	      ORDER BY ch_almacen";
	      
      if ($sqlca->query($sql) < 0) return null;
      
      $result = Array();

      for ($i = 0; $i < $sqlca->numrows(); $i++) {
	  $array = $sqlca->fetchRow();
	  
	  $ch_almacen = $array[0];
	  $ch_nombre_almacen = $array[1];
	  
	  $result[$ch_almacen] = $ch_nombre_almacen;
      }
      
      return $result;
  }
  
  	function validaDia($dia) {
		global $sqlca;

		$sql = "SELECT p1.par_valor||'-'||p2.par_valor||'-01' FROM int_parametros p1, int_parametros p2 WHERE p1.par_nombre='inv_ano_cierre' AND p2.par_nombre='inv_mes_cierre';";
		if ($sqlca->query($sql) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		$diacierre = $a[0];

		$sql = "SELECT CASE WHEN ('$dia'<'$diacierre') THEN 0 ELSE (CASE WHEN ('$dia'>now()) THEN 1 ELSE 2 END) END;";
		if ($sqlca->query($sql) < 0) // 0: Menor a fecha de inventario   1: mayor a fecha actual   2: correcto
			return false;						
		$a = $sqlca->fetchRow();	
				
		if($a[0]=="0") {
      			return "0";
      		} else {
      			if($a[0]=="1") {
      				return "1";
      			} else {
      				return "2";
      			}
      		}
	}
    
}

