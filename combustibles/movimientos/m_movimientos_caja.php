<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class MovimientosCajaModel extends Model {

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = "AND ch_almacen = '".$alm."'"; 
	
		$sql = "
SELECT
 ch_almacen,
 ch_almacen||' - '||ch_nombre_almacen
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
 ".$cond."
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

	function Caja() {
		global $sqlca;
		
		$sql="
		SELECT
			c_cash_id,
			ware_house ||' - '|| name
		FROM
			c_cash
		ORDER BY
			1;
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

	function CuentasBancarias() {
		global $sqlca;
		
		$sql="
		SELECT
			c_bank_account_id,
			B.initials || ' - ' || C.c_bank_account_id banco
		FROM
			c_bank_account C
			JOIN c_bank B ON(C.c_bank_id = B.c_bank_id)
		ORDER BY
			B.initials;
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

	function getMediosPago() {
		global $sqlca;

		$arrResult = array();
		$status = $sqlca->query("SELECT c_cash_mpayment_id AS nu_id, name AS no_descripcion FROM c_cash_mpayment");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getMediosPago';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;
		}
		return $arrResult;	
	}

	function getSaldoInicial($iAlmacen, $fe_inicial, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria) {
		global $sqlca;

		$condTipoMovimientoCaja = ($iTipoMovimientoCaja != "" ? "AND c.type = " . $iTipoMovimientoCaja : "");
		$condMedioPago = ($iMedioPago != 0 ? "AND i.c_cash_mpayment_id = " . $iMedioPago : "");
		$condCuentaBancaria = ($iCuentaBancaria != 0 ? "AND i.c_bank_account_id = '" . $iCuentaBancaria . "'" : "");

		$arrResult = array();
		$status = $sqlca->query("
		SELECT
			SUM(
				(CASE WHEN i.c_currency_id = '2' THEN
					(CASE
						WHEN c.type = '0' THEN ROUND(i.amount * c.rate,2)
						WHEN c.type = '1' THEN ROUND(-i.amount * c.rate,2)
					END)
				ELSE
					(CASE
						WHEN c.type = '0' THEN ROUND(i.amount, 2)
						WHEN c.type = '1' THEN ROUND(-i.amount, 2)
					END)
				END)
			) ss_saldo_inicial
		FROM
			c_cash_transaction AS c
			JOIN c_cash_transaction_payment AS i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		WHERE
			c.ware_house = '" . $iAlmacen . "'
			AND c.c_cash_id = " . $iCaja . "
			AND i.created <= (date '" . $fe_inicial . "' - integer '1')
			" . $condTipoMovimientoCaja . "
			" . $condMedioPago . "
			" . $condCuentaBancaria . "
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getSaldoInicial';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;
		}
		return $arrResult;	
	}

    function getMovimientosIngresosEgresos($iAlmacen, $fe_inicial, $fe_final, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria) {
		global $sqlca;

		$condTipoMovimientoCaja = ($iTipoMovimientoCaja != "" ? "AND c.type = " . $iTipoMovimientoCaja : "");
		$condMedioPago = ($iMedioPago != 0 ? "AND i.c_cash_mpayment_id = " . $iMedioPago : "");
		$condCuentaBancaria = ($iCuentaBancaria != 0 ? "AND i.c_bank_account_id = '" . $iCuentaBancaria . "'" : "");

		$arrResult = array();
		$status = $sqlca->query("
		SELECT
			TO_CHAR(c.d_system, 'DD/MM/YYYY') AS fe_emision,
			c.transaction AS nu_recibo,
			i.pay_number AS no_documento,
			TRIM(cli.cli_razsocial) AS no_cliente,
			TRIM(pro.pro_rsocialbreve) AS no_proveedor,
			c.reference AS txt_glosa,
			m.name AS no_medio_pago,
			c.type AS nu_tipo_operacion,
			o.name AS no_tipo_operacion,
			i.c_currency_id AS nu_tipo_moneda,
			c.rate AS ss_tipo_cambio,
			i.amount AS ss_total
		FROM
			c_cash_transaction AS c
			INNER JOIN c_cash_transaction_payment AS i ON (c.c_cash_transaction_id = i.c_cash_transaction_id)
			INNER JOIN c_cash_mpayment AS m ON (i.c_cash_mpayment_id = m.c_cash_mpayment_id)
			LEFT JOIN int_clientes AS cli ON (c.bpartner = cli.cli_codigo)
			LEFT JOIN int_proveedores AS pro ON (pro.pro_codigo = c.bpartner)
			INNER JOIN c_cash_operation AS o ON (c.c_cash_operation_id = o.c_cash_operation_id)
			INNER JOIN c_cash AS cash ON (c.c_cash_id = cash.c_cash_id)
		WHERE
			c.ware_house = '" . $iAlmacen . "'
			AND c.c_cash_id = " . $iCaja . "
			AND i.created BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condTipoMovimientoCaja . "
			" . $condMedioPago . "
			" . $condCuentaBancaria . "
		ORDER BY
			1;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getMovimientosIngresosEgresos';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;
		}
		return $arrResult;
    }
}
