<?php

class PuntosFidelizaManualModel extends Model{

    function llamadaRemota($procedimiento, $parametros){
        global $sqlca;

        $sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='master_puntos';";
        $sqlca->query($sql);
        $row = $sqlca->fetchRow();
        $ip = $row[0];

        $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=puntosfidelizamanual&proc=" . urlencode($procedimiento);
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

    function obtenerAlmacenes() {
        global $sqlca;

        $sql = "
        SELECT
		    ch_almacen,
		    ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen;
        ";
        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[$a[0]] = $a[1];
        }
        return $result;
    }

	function ingresarpuntosfidelizamanual($tarjeta,$puntos){
        global $usuario;

	    $params = array(
		"tarjeta"	=> $tarjeta,
		"puntos"	=> $puntos,
		"usuario"	=> $usuario->obtenerUsuario(),
	    "sucursal"	=> $usuario->obtenerAlmacenActual()
	    );
	    return PuntosFidelizaManualModel::llamadaRemota("ingresarpuntosfidelizamanual", $params);
	}

	function actualizarpuntosfidelizamanual($idpunto,$puntos){
	    $params = array(
		"idpunto"	=> $idpunto,
		"puntos"	=> $puntos
	    );
	    return PuntosFidelizaManualModel::llamadaRemota("actualizarpuntosfidelizamanual", $params);
	}

	function eliminarpuntosfidelizamanual($idpunto){
	    $params = array(
		"idpunto"	=> $idpunto
	    );
	    return PuntosFidelizaManualModel::llamadaRemota("eliminarpuntosfidelizamanual", $params);
	}

	function tmListado($almacen, $filtro1, $filtro2, $tipo, $pp, $pagina){
	    $params = array(
	    "almacen"	=> $almacen,
		"filtro1"	=> $filtro1,
		"filtro2"	=> $filtro2,
		"tipo"		=> $tipo,
		"pp"		=> $pp,
		"pagina"	=> $pagina
	    );
	    return PuntosFidelizaManualModel::llamadaRemota("tmListado", $params);
	}  
}

