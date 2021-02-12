<?php

class PuntosxProductoModel extends Model{

        function llamadaRemota($procedimiento, $parametros){

            global $sqlca;

            $sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
            $sqlca->query($sql);
            $row = $sqlca->fetchRow();
            $ip = $row[0];
            $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=puntosxproducto&proc=" . urlencode($procedimiento);

            foreach($parametros as $parametro=>$valor) {
                $url .= "&" . $parametro . "=" . urlencode($valor);
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

	function ingresarPuntosxProducto($idcampania,$idarticulo,$puntossol,$puntosunidad){
	    $params = array(
		"idcampania"	=> $idcampania,
		"idarticulo"	=> $idarticulo,
		"puntossol"	=> $puntossol,
		"puntosunidad"	=> $puntosunidad
	    );
	    
	    return PuntosxProductoModel::llamadaRemota("ingresarPuntosxProducto", $params);
	}

	function actualizarPuntosxProducto($idcampania,$idarticulo,$puntossol,$puntosunidad){
	    $params = array(
		"idcampania"	=> $idcampania,
		"idarticulo"	=> $idarticulo,
		"puntossol"	=> $puntossol,
		"puntosunidad"	=> $puntosunidad
	    );
	    
	    return PuntosxProductoModel::llamadaRemota("actualizarPuntosxProducto", $params);
	}

	function eliminarPuntosxProducto($idcampania,$idarticulo){
	    $params = array(
		"idcampania"	=> $idcampania,
		"idarticulo"	=> $idarticulo
	    );
	    
	    return PuntosxProductoModel::llamadaRemota("eliminarPuntosxProducto", $params);
	}

	function tmListado($filtro,$tipo,$pp, $pagina){
	    $params = array(
		"filtro"	=> $filtro,
		"tipo"		=> $tipo,	
		"pp"		=> $pp,
		"pagina"	=> $pagina
	    );
	    
	    return PuntosxProductoModel::llamadaRemota("tmListado", $params);
	}  
}
