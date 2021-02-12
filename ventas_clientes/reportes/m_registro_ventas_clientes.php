<?php

class RegistroVentasClientesModel extends Model {

	function getAlmacenes() {
		global $sqlca;

		$sql = "
		SELECT
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
    
    function getListaDocumentosManualesVentas($nu_almacen, $fe_inicial, $fe_final, $iTipoVenta, $iFormaPago, $nu_documento_identidad, $sRazSocial) {
		global $sqlca;

		$dataRows = array();
		$condTipoVenta = NULL;
		$condFormaPago = NULL;

		if($iTipoVenta == "C")//Combustible
			$condTipoVenta = "AND FD.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
		else if($iTipoVenta == "M")//Market
			$condTipoVenta = "AND FD.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";

		if($iFormaPago == "N")//Contado
			$condFormaPago = "AND FC.ch_fac_credito = 'N'";
		else if($iFormaPago == "S")//Credito
			$condFormaPago = "AND FC.ch_fac_credito = 'S'";

		$condCliente = (!empty($nu_documento_identidad) && !empty($sRazSocial) ? "AND CLI.cli_codigo = '" . $nu_documento_identidad . "'" : NULL);

		if ($sqlca->query("
SELECT
 TO_CHAR(FC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_emision,
 TRIM(TD.tab_car_03) || ' - ' || TD.tab_descripcion AS tipo_documento,
 TRIM(FC.ch_fac_seriedocumento) AS serie_documento,
 TRIM(FC.ch_fac_numerodocumento) AS numero_documento,
 TRIM(CLI.cli_ruc) AS nu_documento_identidad,
 CLI.cli_razsocial AS no_razsocial,
 TRIM(PRO.art_codigo) AS nu_codigo_producto,
 PRO.art_descripcion AS no_producto,
 FD.nu_fac_cantidad AS ss_cantidad,
 FD.nu_fac_precio AS ss_precio,
 FC.nu_tipocambio AS ss_tipo_cambio,
 FD.nu_fac_importeneto AS ss_valor_bruto,
 FD.nu_fac_descuento1 AS ss_descuento,
 (FD.nu_fac_importeneto - FD.nu_fac_descuento1) AS ss_valor_venta,
 FD.nu_fac_impuesto1 AS ss_igv,
 FD.nu_fac_valortotal AS ss_total,
 FC.ch_liquidacion AS nu_liquidacion_vales
FROM
 fac_ta_factura_cabecera AS FC
 JOIN int_clientes AS CLI
  USING(cli_codigo)
 JOIN int_tabla_general AS TD
  ON(FC.ch_fac_tipodocumento = SUBSTRING(TRIM(TD.tab_elemento) FOR 2 FROM LENGTH(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
 JOIN fac_ta_factura_detalle AS FD
  USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_articulos AS PRO
  USING(art_codigo)
WHERE
 FC.ch_fac_tipodocumento IN ('10','35','11','20')
 AND FC.ch_almacen = '" . $nu_almacen . "'
 AND FC.dt_fac_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
 " . $condTipoVenta . "
 " . $condFormaPago . "
 " . $condCliente . "
ORDER BY
 FC.dt_fac_fecha,2,3,4
		") < 0) {
			return FALSE;
		}

		$countRows = $sqlca->numrows();
		$dataRows = $sqlca->fetchAll();
		return $dataRows;
    }
}
