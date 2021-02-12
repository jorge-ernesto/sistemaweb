<?php

class DiarioinventarioYCajaModel extends Model {

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
    
    function getCuadreDiarioInventarioYCaja($nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		if ($sqlca->query("
		SELECT
			TANQUE.ch_codigocombustible AS nu_codigo_combustible,
			FIRST(ch_nombrebreve) AS no_producto,
			VARILLA.ch_tanque AS nu_tanque
		FROM
			comb_ta_mediciondiaria AS VARILLA
			JOIN comb_ta_tanques AS TANQUE USING(ch_tanque)
			JOIN comb_ta_combustibles AS PRO USING(ch_codigocombustible)
		WHERE
			VARILLA.ch_sucursal = '" . $nu_almacen . "'
			AND VARILLA.dt_fechamedicion BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		GROUP BY
			TANQUE.ch_codigocombustible,
			VARILLA.ch_tanque
		") < 0) {
			return FALSE;
		}

		$cantidadRows = $sqlca->numrows();
		$row = $sqlca->fetchAll();

		$cantidad_inicial_kardex_entrada 	= 0.0000;
		$cantidad_inicial_kardex_salida		= 0.0000;
		$cantidad_inicial_kardex 			= 0.0000;
		$cantidad_varilla_ayer 				= 0.0000;
		$cantidad_compra 					= 0.0000;
		$cantidad_varilla_hoy 				= 0.0000;

		$arrResponse = Array();
		$arrVentaCombustible = Array();

		for ($i = 0; $i < $cantidadRows; $i++) {
			$cantidad_inicial_kardex_entrada 		= $this->getCantidadInventarioInicialLibrosEntrada($nu_almacen, $row[$i]["nu_codigo_combustible"], $fe_inicial);
			$cantidad_inicial_kardex_salida 		= $this->getCantidadInventarioInicialLibrosSalida($nu_almacen, $row[$i]["nu_codigo_combustible"], $fe_inicial);
			$cantidad_inicial_kardex 				= $cantidad_inicial_kardex_entrada - $cantidad_inicial_kardex_salida;

			$cantidad_varilla_ayer					= $this->getCantidadInventarioInicialFisico($nu_almacen, $row[$i]["nu_tanque"], $fe_inicial);
			$cantidad_compra 						= $this->getCompraCombustible($nu_almacen, $row[$i]["nu_codigo_combustible"], $fe_inicial, $fe_final);
			$arrVentaCombustible 					= $this->getVentaCombustible($nu_almacen, $row[$i]["nu_codigo_combustible"], $fe_inicial, $fe_final);
			$cantidad_varilla_hoy 					= $this->getCantidadVarilla($nu_almacen, $row[$i]["nu_tanque"], $fe_inicial, $fe_final);

			$arrResponse[$i]["no_producto"] 							= $row[$i]["no_producto"];
			$arrResponse[$i]["nu_cantidad_inventario_inicial_kardex"] 	= $cantidad_inicial_kardex;
			$arrResponse[$i]["nu_cantidad_inventario_inicial_varilla"] 	= $cantidad_varilla_ayer;
			$arrResponse[$i]["nu_cantidad_compra"] 						= $cantidad_compra;
			$arrResponse[$i]["nu_cantidad_venta"] 						= $arrVentaCombustible[0];//Cantidad
			$arrResponse[$i]["nu_soles_venta"] 							= $arrVentaCombustible[1];//Soles
			$arrResponse[$i]["nu_cantidad_inventario_final_kardex"] 	= (($cantidad_inicial_kardex + $cantidad_compra) - $arrVentaCombustible[0]);
			$arrResponse[$i]["nu_cantidad_inventario_final_varilla"] 	= (($cantidad_varilla_ayer + $cantidad_compra) - $arrVentaCombustible[0]);
			$arrResponse[$i]["nu_cantidad_varilla"] 					= $cantidad_varilla_hoy;
			$arrResponse[$i]["nu_cantidad_diferencia_kardex"] 			= ($cantidad_varilla_hoy - (($cantidad_inicial_kardex + $cantidad_compra) - $arrVentaCombustible[0]));
			$arrResponse[$i]["nu_cantidad_diferencia_varilla"] 			= ($cantidad_varilla_hoy - (($cantidad_varilla_ayer + $cantidad_compra) - $arrVentaCombustible[0]));
		}
		return array(TRUE, $arrResponse);
    }

    function getCantidadInventarioInicialLibrosEntrada($nu_almacen, $nu_codigo_combustible, $fe_inicial){
		global $sqlca;

    	if ($sqlca->query("
		SELECT
			ROUND(SUM(mov_cantidad), 4)
		FROM
			inv_movialma
		WHERE
			mov_almacen = '" . $nu_almacen . "'
			AND mov_naturaleza IN ('1', '2')
			AND art_codigo = '" . $nu_codigo_combustible . "'
			AND mov_fecha < '" . $fe_inicial . "';
		") < 0 ){
			return FALSE;
		}
		$row = $sqlca->fetchRow();
    	return $row[0];
    }

    function getCantidadInventarioInicialLibrosSalida($nu_almacen, $nu_codigo_combustible, $fe_inicial){
		global $sqlca;

    	if ($sqlca->query("
		SELECT
			ROUND(SUM(mov_cantidad), 4)
		FROM
			inv_movialma
		WHERE
			mov_almacen = '" . $nu_almacen . "'
			AND mov_naturaleza IN ('3', '4')
			AND art_codigo = '" . $nu_codigo_combustible . "'
			AND mov_fecha < '" . $fe_inicial . "';
		") < 0 ){
			return FALSE;
		}
		$row = $sqlca->fetchRow();
    	return $row[0];
    }

    function getCantidadInventarioInicialFisico($nu_almacen, $nu_tanque, $fe_inicial){
		global $sqlca;

    	if ($sqlca->query("
		SELECT
			nu_medicion
		FROM
			comb_ta_mediciondiaria
		WHERE
			ch_sucursal = '" . $nu_almacen . "'
			AND ch_tanque = '" . $nu_tanque . "'
			AND dt_fechamedicion = (date '" . $fe_inicial . "' - integer '1');
		") < 0 ){
			return FALSE;
		}
		$row = $sqlca->fetchRow();
    	return $row[0];
    }

    function getCompraCombustible($nu_almacen, $nu_codigo_combustible, $fe_inicial, $fe_final){
    	global $sqlca;

    	if ($sqlca->query("
    	SELECT
			ROUND(SUM(mov_cantidad), 4)
		FROM
			inv_movialma
		WHERE
			mov_almacen = '" . $nu_almacen . "'
			AND tran_codigo = '21'
			AND art_codigo = '" . $nu_codigo_combustible . "'
			AND mov_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		") < 0 ){
			return FALSE;
		}
		$row = $sqlca->fetchRow();
    	return $row[0];
    }

    function getVentaCombustible($nu_almacen, $nu_codigo_combustible, $fe_inicial, $fe_final){
    	global $sqlca;

    	/* get Parte de Venta Combustible */
    	if ($sqlca->query("
    	SELECT
			ROUND(SUM(nu_ventagalon), 3),
			ROUND(SUM(nu_ventavalor), 2),
			ROUND(SUM(nu_descuentos), 2)
		FROM
			comb_ta_contometros
		WHERE
			ch_sucursal = '" . $nu_almacen . "'
			AND ch_codigocombustible = '" . $nu_codigo_combustible . "'
			AND dt_fechaparte BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		") < 0 ){
			return FALSE;
		}
		$row = $sqlca->fetchRow();

		/* get Afericiones */
    	if ($sqlca->query("
    	SELECT
    		SUM(cantidad),
    		SUM(importe)
    	FROM
    		pos_ta_afericiones
    	WHERE
    		es = '" . $nu_almacen . "'
    		AND codigo = '" . $nu_codigo_combustible . "'
    		AND dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		") < 0 ){
			return FALSE;
		}
		$row2 = $sqlca->fetchRow();

		$cantidad_contometros_afericion = $row[0] - $row2[0];
		$soles_contometros_afericion 	= ($row[1] + $row[2]) - $row2[1];

    	return array($cantidad_contometros_afericion, $soles_contometros_afericion);
    }

    function getCantidadVarilla($nu_almacen, $nu_tanque, $fe_inicial, $fe_final){
		global $sqlca;

    	if ($sqlca->query("
		SELECT
			ROUND(SUM(nu_medicion), 3)
		FROM
			comb_ta_mediciondiaria
		WHERE
			ch_sucursal = '" . $nu_almacen . "'
			AND ch_tanque = '" . $nu_tanque . "'
			AND dt_fechamedicion BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "';
		") < 0 ){
			return FALSE;
		}
		$row = $sqlca->fetchRow();
    	return $row[0];
    }
    
    function getGastosVarios($nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		if ($sqlca->query("
		SELECT
			ROUND(SUM(CCTP.amount), 4)
		FROM
			c_cash_transaction AS CCT
			JOIN c_cash_transaction_payment AS CCTP USING(c_cash_transaction_id)
			JOIN c_cash_operation AS CCO USING(c_cash_operation_id)
		WHERE
			CCT.ware_house = '" . $nu_almacen . "'
			AND CCT.type = 1--EGRESO
			AND CCO.accounts = 0--GASTOS VARIOS
			AND CCTP.created::DATE BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "';
		") < 0) {
			return FALSE;
		}
		$row = $sqlca->fetchRow();
		return $row[0];
    }
    
    function getCreditosClientes($nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		if ($sqlca->query("SELECT ROUND(SUM(nu_importe), 2) FROM val_ta_cabecera WHERE ch_sucursal = '" . $nu_almacen . "' AND dt_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "';") < 0)
			return FALSE;
		$row = $sqlca->fetchRow();
		return $row[0];
    }
    
    function getDepositosBancarios($nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		if ($sqlca->query("
		SELECT
			ROUND(SUM(CCTP.amount), 4)
		FROM
			c_cash_transaction AS CCT
			JOIN c_cash_transaction_payment AS CCTP USING(c_cash_transaction_id)
		WHERE
			CCT.ware_house = '" . $nu_almacen . "'
			AND CCT.type = 0--INGRESO
			AND CCTP.c_cash_mpayment_id = 1--DEPOSITO BANCARIO
			AND CCTP.created::DATE BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "';
		") < 0) {
			return FALSE;
		}
		$row = $sqlca->fetchRow();
		return $row[0];
    }
    
    function getTarjetasCreditos($nu_almacen, $fe_inicial, $fe_final, $pos_trans) {
		global $sqlca;

		if ($sqlca->query("
		SELECT
			PT.at,
			FIRST(TARCRE.tab_descripcion) AS no_tipo_tarjeta_credito,
			ROUND(SUM(PT.importe), 4) AS nu_soles_tarjeta_credito
		FROM
			" . $pos_trans . " AS PT
			LEFT JOIN int_tabla_general AS TARCRE ON (TARCRE.tab_tabla = '95' AND TARCRE.tab_elemento != '000000' AND TRIM(PT.at) = SUBSTRING(TARCRE.tab_elemento, 6, 6))
		WHERE
			PT.es = '" . $nu_almacen . "'
			AND PT.fpago = '2'
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		GROUP BY
			PT.at
		ORDER BY
			1;
		") < 0) {
			return FALSE;
		}

		$cantidadRows 	= $sqlca->numrows();
		$row 			= $sqlca->fetchAll();
		$arrResponse 	= Array();

		for ($i = 0; $i < $cantidadRows; $i++) {
			$arrResponse[$i]["no_tipo_tarjeta_credito"] = $row[$i]["no_tipo_tarjeta_credito"];
			$arrResponse[$i]["nu_soles_tarjeta_credito"] = $row[$i]["nu_soles_tarjeta_credito"];
		}
		return array($arrResponse);
    }
    
    function getDepositosPOS($nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		if ($sqlca->query("SELECT ROUND(SUM(nu_importe), 4) FROM pos_depositos_diarios WHERE ch_almacen = '" . $nu_almacen . "' AND dt_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "';") < 0)
			return FALSE;
		$row = $sqlca->fetchRow();
		return $row[0];
    }
}
