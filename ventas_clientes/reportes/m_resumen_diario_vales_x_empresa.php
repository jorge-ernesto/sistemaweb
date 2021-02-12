<?php

class ResumenDiarioValesXEmpresaModel extends Model {
	function getAlmacenes() {
		global $sqlca;

		$sql = " SELECT
						ch_almacen,
						ch_almacen||' - '||ch_nombre_almacen
					FROM
						inv_ta_almacenes
					WHERE
						ch_clase_almacen = '1'
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

    function getFechaSistemaPA() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
    }
    
    function getListaValesCredito($nu_almacen, $fe_inicial, $fe_final, $nu_documento_identidad, $sRazSocial) {
		global $sqlca;

		$arrDataRows = array();
		$condAlmacen = (!empty($nu_almacen) ? "AND VD.ch_sucursal = '" . $nu_almacen . "'" : NULL);
		$condCliente = (!empty($nu_documento_identidad) && !empty($sRazSocial) ? "AND VC.ch_cliente = '" . $nu_documento_identidad . "'" : NULL);

		if ($sqlca->query("
		SELECT
			ALMA.ch_almacen AS nu_codigo_almacen,
			ALMA.ch_nombre_almacen AS no_nombre_almacen,
			CLI.cli_codigo AS nu_documento_identidad,
			CLI.cli_razsocial AS no_razsocial,
			VCOM.ch_numeval AS nu_vale_manual,
			VC.ch_documento AS nu_trans,
			TO_CHAR(VC.dt_fecha, 'DD/MM/YYYY') AS fe_emision,
			TRIM(PRO.art_codigo) AS nu_codigo_producto,
			PRO.art_descripcion AS nu_nombre_producto,
			VD.nu_cantidad AS nu_cantidad,
			VD.nu_importe AS ss_importe
		FROM
			val_ta_cabecera AS VC
			JOIN int_clientes AS CLI ON(VC.ch_cliente = CLI.cli_codigo)
			LEFT JOIN val_ta_complemento AS VCOM USING(ch_sucursal, ch_documento, dt_fecha)
			JOIN val_ta_detalle AS VD USING(ch_sucursal, dt_fecha, ch_documento)
			JOIN inv_ta_almacenes AS ALMA ON(VD.ch_sucursal = ALMA.ch_almacen)
			JOIN int_articulos AS PRO ON(VD.ch_articulo = PRO.art_codigo)
		WHERE
			VC.dt_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condAlmacen . "
			" . $condCliente . "
		ORDER BY
			1,2,3,4,6,5
		") < 0) {
			return FALSE;
		}
		$arrDataRows = $sqlca->fetchAll();
		return $arrDataRows;
    }
}
