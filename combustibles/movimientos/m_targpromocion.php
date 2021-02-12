<?php


Class TargpromocionModel extends Model{
	function llamadaRemota($procedimiento, $parametros)
	{
	    global $sqlca;
	    
	    $sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
	    $sqlca->query($sql);
	    $row = $sqlca->fetchRow();
	    $ip = $row[0];
	    
	    $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=tarjetascuentas&proc=" . urlencode($procedimiento);
	    

	    foreach($parametros as $parametro=>$valor) {
		$url .= "&" . $parametro . "=" . urlencode($valor);
	    }

	    $fh = fopen($url,"rb");
	    
	    $res = '';
	    
	    while (!feof($fh)) {
		$res .= fread($fh, 8192);
	    }
	    
	    fclose($fh);
	   // echo $res;	    
	    return unserialize($res);
	}
	
	function ingresarCuenta($numero,$nombres,$apellidos,$dni,$ruc,$direccion,$telefono,$telefono2,$email,$tipocuenta,$puntos,$usuario){
	    $params = array(
		"numero"	=> $numero,
		"nombres"	=> $nombres,
		"apellidos"	=> $apellidos,
		"dni"		=> $dni,
		"ruc"		=> $ruc,
		"direccion"	=> $direccion,
		"telefono"	=> $telefono,
		"telefono2"	=> $telefono2,
		"email"		=> $email,
		"tipocuenta" => $tipocuenta,
		"puntos"	=> $puntos,
		"usuario"	=> $usuario
	    );
	    
	    return TargpromocionModel::llamadaRemota("ingresarCuenta", $params);
	}

	function actualizarCuenta($idcuenta,$numero,$nombres,$apellidos,$dni,$ruc,$direccion,$telefono,$telefono2,$email,$tipocuenta,$usuario){
	    $params = array(
		"idcuenta"	=> $idcuenta,
		"numero"	=> $numero,
		"nombres"	=> $nombres,
		"apellidos"	=> $apellidos,
		"dni"		=> $dni,
		"ruc"		=> $ruc,
		"direccion"	=> $direccion,
		"telefono"	=> $telefono,
		"telefono2"	=> $telefono2,
		"email"		=> $email,
		"tipocuenta" => $tipocuenta,
		"usuario"	=> $usuario
	    );
	    
	    return TargpromocionModel::llamadaRemota("actualizarCuenta", $params);
	}

	function obtenerCuenta($campovalor,$tipocampo){
	    $params = array(
		"campovalor"	=> $campovalor,
		"tipocampo"	=> $tipocampo
	    );
	    
	    return TargpromocionModel::llamadaRemota("obtenerCuenta", $params);
	}
	
	function modificarTarjeta($idtarjeta,$numero,$descripcion,$placa,$fechaven,$flatcuenta,$flattitular,$usuario){
	    $params = array(
		"idtarjeta"	=> $idtarjeta,
		"numero"	=> $numero,
		"descripcion"	=> $descripcion,
		"placa"		=> $placa,
		"fechaven"	=> $fechaven,
		"flatcuenta"	=> $flatcuenta,
		"flattitular"	=> $flattitular,
		"usuario"	=> $usuario
	    );
	    
	    return TargpromocionModel::llamadaRemota("modificarTarjeta", $params);
	}
	
	function insertarTarjeta($idcuenta,$numero,$descripcion,$placa,$fechaven,$puntos,$flatcuenta,$flattitular,$usuario){
	    $params = array(
		"idcuenta"	=> $idcuenta,
		"numero"	=> $numero,
		"descripcion"	=> $descripcion,
		"placa"		=> $placa,
		"fechaven"	=> $fechaven,
		"puntos"	=> $puntos,
		"flatcuenta"	=> $flatcuenta,
		"flattitular"	=> $flattitular,
		"usuario"	=> $usuario
	    );
	    
	    return TargpromocionModel::llamadaRemota("insertarTarjeta", $params);
	}

	function eliminarTarjeta($idcuenta,$idtarjeta){
	    $params = array(
		"idcuenta"	=> $idcuenta,
		"idtarjeta"	=> $idtarjeta
	    );
	    
	    return TargpromocionModel::llamadaRemota("eliminarTarjeta", $params);
	}
	
	function eliminarCuenta($idcuenta){
	    $params = array(
		"idcuenta"	=> $idcuenta
	    );
	    
	    return TargpromocionModel::llamadaRemota("eliminarCuenta", $params);
	}
		
	function listarTarjetas($filtro,$tipo){
	    $params = array(
		"filtro"	=> $filtro,
		"tipo"		=> $tipo
	    );
	    
	    return TargpromocionModel::llamadaRemota("listarTarjetas", $params);
	}

	function listarTiposCuenta($filtro,$tipo){
	    $params = array(
		"filtro"	=> $filtro,
		"tipo"		=> $tipo
	    );
	   
	    return TargpromocionModel::llamadaRemota("listarTiposCuenta", $params);
	}
	
	function tmListado($filtro,$tipo,$pp, $pagina){
	    $params = array(
		"filtro"	=> $filtro,
		"tipo"		=> $tipo,
		"pp"		=> $pp,
		"pagina"	=> $pagina
	    );
	    
	    return TargpromocionModel::llamadaRemota("tmListado", $params);
	}

  
}

