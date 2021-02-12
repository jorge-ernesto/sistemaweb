<?php

//print 'entrando a model canje';
Class CanjeitemModel extends Model{
        function llamadaRemota($procedimiento, $parametros)
        {
            global $sqlca;

            $sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
            $sqlca->query($sql);
            $row = $sqlca->fetchRow();
            $ip = $row[0];

            $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=itemscanje&proc=" . urlencode($procedimiento);
         
            foreach($parametros as $parametro=>$valor) {
                $url .= "&" . $parametro . "=" . urlencode($valor);
            }

            $fh = fopen($url,"rb");

            $res = '';

            while (!feof($fh)) {
                $res .= fread($fh, 8192);
            }

            fclose($fh);
          //echo $res;
            return unserialize($res);
        }

	function ingresarItem($codarticulo,$descripcion,$fechaven,$puntos,$observacion,$usuario,$sucursal){
	    $params = array(
		"codarticulo"	=> $codarticulo,
		"descripcion"	=> $descripcion,
		"fechaven"	=> $fechaven,
		"puntos"	=> $puntos,
		"observacion"	=> $observacion,
		"usuario"	=> $usuario,
		"sucursal"	=> $sucursal
	    );
	    
	    return CanjeitemModel::llamadaRemota("ingresarItem", $params);
	}

	function actualizarItem($iditem,$codarticulo,$descripcion,$fechaven,$puntos,$observacion,$usuario,$sucursal){
	    $params = array(
		"iditem"	=> $iditem,
		"codarticulo"	=> $codarticulo,
		"descripcion"	=> $descripcion,
		"fechaven"	=> $fechaven,
		"puntos"	=> $puntos,
		"observacion"	=> $observacion,
		"usuario"	=> $usuario,
		"sucursal"	=> $sucursal
	    );
	    
	    return CanjeitemModel::llamadaRemota("actualizarItem", $params);
	}

	function eliminarItem($iditem){
	    $params = array(
		"iditem"	=> $iditem
	    );
	    
	    return CanjeitemModel::llamadaRemota("eliminarItem", $params);
	}
	
	function listarItems($filtro){
		$params = array(
		"filtro"	=> $filtro
	    );
	    
	    return CanjeitemModel::llamadaRemota("listarItems", $params);
	}
			
	function listarArticulos($filtro){
	    $params = array(
		"filtro"	=> $filtro
	    );
	    
	    return CanjeitemModel::llamadaRemota("listarArticulos", $params);
	}
	
	function obtenerArticulo($campovalor,$tipocampo){
	    $params = array(
		"campovalor"	=> $campovalor,
		"tipocampo"	=> $tipocampo
	    );
	    
	    return CanjeitemModel::llamadaRemota("obtenerArticulo", $params);
	}
	
	function tmListado($filtro,$pp, $pagina){
	    $params = array(
		"filtro"	=> $filtro,
		"pp"		=> $pp,
		"pagina"	=> $pagina
	    );
	    
	    return CanjeitemModel::llamadaRemota("tmListado", $params);
	}

  
}

