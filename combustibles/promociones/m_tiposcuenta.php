<?php

class TiposCuentaModel extends Model {
	function llamadaRemota($procedimiento, $parametros) {
		global $sqlca;

		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='master_puntos';";
		$sqlca->query($sql);
		$row 	= $sqlca->fetchRow();
		$ip 	= $row[0];
		$url	= "http://" . $ip . "/sistemaweb/puntos/index.php?action=tiposcuenta&proc=" . urlencode($procedimiento);

		foreach($parametros as $parametro=>$valor) {
			$url .= "&" . $parametro . "=" . urlencode($valor);
		}

		$fh = fopen($url,"rb");
		if($fh===FALSE) 
			return FALSE;

		$res = '';
		while (!feof($fh)) {
			$res .= fread($fh, 8192);
		}
		fclose($fh);

		return unserialize($res);
	}

	function ingresartiposcuenta($idtipocuenta,$descripcion) {
		global $usuario;

		$params = array(
				"idtipocuenta"	=> $idtipocuenta,
				"descripcion"	=> $descripcion,
				"usuario"	=> $usuario->obtenerUsuario(),
				"sucursal"	=> $usuario->obtenerAlmacenActual()
				);

		return TiposCuentaModel::llamadaRemota("ingresartiposcuenta", $params);
	}

	function actualizartiposcuenta($idtipocuenta,$descripcion) {
		$params = array(
			"idtipocuenta"	=> $idtipocuenta,
			"descripcion"	=> $descripcion
				);

		return TiposCuentaModel::llamadaRemota("actualizartiposcuenta", $params);
	}

	function eliminartiposcuenta($idtipocuenta) {
		$params = array(
			"idtipocuenta"	=> $idtipocuenta
		);

		return TiposCuentaModel::llamadaRemota("eliminartiposcuenta", $params);
	}

	function tmListado($filtro,$tipo) {
		$params = array(
			"filtro"	=> $filtro,
			"tipo"	=> $tipo
		);

		return TiposCuentaModel::llamadaRemota("tmListado", $params);
	}
}
