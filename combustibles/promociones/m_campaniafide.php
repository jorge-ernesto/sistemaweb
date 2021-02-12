<?php

class CampaniaFideModel extends Model {

	function llamadaRemota($procedimiento, $parametros) {
    	global $sqlca;

    	//echo "<h2>funcionamrd</h2>";

    	$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'master_puntos';";
    	$sqlca->query($sql);
    	$row = $sqlca->fetchRow();
    	$ip = $row[0];
    	$url = "http://".$ip."/sistemaweb/puntos/index.php?action=campaniafide&proc=".urlencode($procedimiento);
//var_dump($url);
    	foreach($parametros as $parametro=>$valor) {
        	$url .= "&".$parametro."=".urlencode($valor);
    	}
    	
        $fh = fopen($url,"rb");
    	if ($fh===FALSE)
			return FALSE;

    	$res = '';
    	while (!feof($fh)) {
        	$res .= fread($fh, 8192);
    	}
		fclose($fh);
		return unserialize($res);
    }

	function nuevoIdCampania() {
		return CampaniaFideModel::llamadaRemota("nuevoIdCampania",[]);
	}

	function ingresarCampania($idcampania,$descripcion,$fechaini,$fechafin,$diasven,$objetivo,$usuario,$sucursal,$repeticiones,$slogan,$saludacumple){
		$params = array(
		"idcampania"=> $idcampania,
		"campaniadescripcion"=> $descripcion,
		"campaniafechaini"=> $fechaini,
		"campaniafechafin"=> $fechafin,
		"campaniadiasven"=> $diasven,
		"campaniaobjetivo"=> $objetivo,
		"usuario"=> $usuario,
		"sucursal"=> $sucursal,
		"repeticiones"=> $repeticiones,
		"slogan"=>$slogan,
		"saludacumple"=>$saludacumple);
		//print_r($params);
	    	return CampaniaFideModel::llamadaRemota("ingresarCampania", $params);
	}

	function actualizarCampania($idcampana,$descripcion/*,$fechaini*/,$fechafin,$diasven,$objetivo,$usuario,$sucursal,$repeticiones,$slogan,$saludacumple) {
	    	$params = array(
		"idcampania"=> $idcampana,
		"campaniadescripcion"=> $descripcion,
		//"campaniafechaini"=> $fechaini,
		"campaniafechafin"=> $fechafin,
		"campaniadiasven"=> $diasven,
		"campaniaobjetivo"=> $objetivo,
		"usuario"=> $usuario,
		"sucursal"=> $sucursal,
		"repeticiones"=> $repeticiones,
		"slogan"=>$slogan,
		"saludacumple"=>$saludacumple);
	    
	    	return CampaniaFideModel::llamadaRemota("actualizarCampania", $params);
	}

	function obtenerCampania($idcampania) {
		$params = array(
		"idcampania"=> $idcampania);
	    
	    	return CampaniaFideModel::llamadaRemota("obtenerCampania", $params);
	}

	function listarCampanias($filtro) {
		$params = array(
		"filtro"	=> $filtro);    
	    	return CampaniaFideModel::llamadaRemota("listarCampanias", $params);
	}

	function listarCampaniasTipo($tipo,$filtro) {
		$params = array(
		"tipo"		=> $tipo,
		"filtro"	=> $filtro);
	    
	    	return CampaniaFideModel::llamadaRemota("listarCampaniasTipo", $params);
	}
			
	function listarTipoCuentas($filtro) {
	    	$params = array(
		"filtro"	=> $filtro);
	    
	    	return CampaniaFideModel::llamadaRemota("listarTipoCuentas", $params);
	}

	function tmListado($filtro,$pp, $pagina) {
	    	$params = array(
		"filtro"	=> $filtro,
		"pp"		=> $pp,
		"pagina"	=> $pagina);
	    
	    	return CampaniaFideModel::llamadaRemota("tmListado", $params);
	}
	
	function ingresarTipoCuenta($idcampania,$idtipocuenta){
	    	$params = array(
		"idcampania"	=> $idcampania,
		"idtipocuenta"	=> $idtipocuenta);
	    
	    	return CampaniaFideModel::llamadaRemota("ingresarTipoCuenta", $params);
	}  
}
