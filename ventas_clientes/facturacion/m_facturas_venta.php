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


/**
 * Información de Tablas
 * int_num_documentos - Series de ventas manuales
 * fac_ta_factura_cabecera - Factura de ventas manuales CABECERA
 * fac_ta_factura_detalle - Factura de ventas manuales DETALLE
 * fac_ta_factura_complemento - Factura de ventas manuales COMPLEMENTO
 * pos_transYYYYMM - Ventas en playa CABECERA y DETALLE, la opción no debe de depender de esta tabla, ya que cuando se instala solo para oficina no se cuenta con dicha tabla.
 **/
class modelSalesInvoice {
	function array_debug($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	function begin_transaction() {
    	global $sqlca;
		$iStatus = $sqlca->query("BEGIN");

	    $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error al iniciar transacción SQL - function begin_transaction()');
	    if ((int)$iStatus >= 0)
	    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Iniciando transacción');
	    return $arrResponse;
  	}

	function commit_transaction() {
		global $sqlca;
		$iStatus = $sqlca->query("COMMIT");

    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error al procesar transacción SQL - function commit_transaction()');
    	if ((int)$iStatus >= 0)
      		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Procesando transacción');
    	return $arrResponse;
  	}

	function rollback_transaction() {
		global $sqlca;
		$iStatus = $sqlca->query("ROLLBACK");

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error al cancelar transacción SQL - function rollback_transaction()');
		if ((int)$iStatus >= 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Cancelando transacción');
		return $arrResponse;
	}

	public function get_all_sales_invoice($arrPost, $jqGridModel){
		global $sqlca;

	    $cond_almacen = (!empty($arrPost['iAlmacen']) ? "AND VC.ch_almacen = '" . $arrPost['iAlmacen'] . "'" : '');
	    $cond_tipo_documento = (!empty($arrPost['iTipoDocumento']) ? "AND VC.ch_fac_tipodocumento = '" . $arrPost['iTipoDocumento'] . "'" : '');
	    $cond_serie_documento = (!empty($arrPost['iSerieDocumento']) ? "AND VC.ch_fac_seriedocumento = '" . $arrPost['iSerieDocumento'] . "'" : '');
	    $cond_numero_documento = (!empty($arrPost['iNumeroDocumento']) ? "AND VC.ch_fac_numerodocumento LIKE '%" . $arrPost['iNumeroDocumento'] . "%'" : '');
	    $cond_estado_documento = ( $arrPost['iEstado'] != "" ? "AND VC.nu_fac_recargo3 = '" . $arrPost['iEstado'] . "'" : '');
	    $cond_cliente = ((!empty($arrPost['iIdCliente']) && !empty($arrPost['sNombreCliente'])) ? "AND VC.cli_codigo = '" . $arrPost['iIdCliente'] . "'" : '');

		$dInicial = trim($arrPost['dInicial']);
		$dInicial = strip_tags(stripslashes($dInicial));
		$dInicial = explode("/", $dInicial);
		$dInicial = $dInicial[2] . "-" . $dInicial[1] . "-" . $dInicial[0];

		$dFinal = trim($arrPost['dFinal']);
		$dFinal = strip_tags(stripslashes($dFinal));
		$dFinal = explode("/", $dFinal);
		$dFinal = $dFinal[2] . "-" . $dFinal[1] . "-" . $dFinal[0];

		$sqlca->query("
		SELECT
			COUNT(VC.*) AS total
		FROM
 			fac_ta_factura_cabecera AS VC
			JOIN int_clientes AS CLI
				USING (cli_codigo)
			LEFT JOIN fac_ta_factura_complemento AS VCOM
				USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN int_tabla_general AS TDOCU
				ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
			LEFT JOIN int_tabla_general AS TDOCUREFE
				ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
			JOIN int_tabla_general AS TMONE
				ON (SUBSTRING(TMONE.tab_elemento, 5) = VC.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
			LEFT JOIN int_tipo_cambio AS TC
				ON (TC.tca_fecha = VC.dt_fac_fecha AND TC.tca_moneda='02')
			LEFT JOIN int_tabla_general AS FPAGO
				ON (SUBSTRING(FPAGO.tab_elemento, 5) = VC.nu_tipo_pago AND FPAGO.tab_tabla ='05' AND FPAGO.tab_elemento != '000000')
		WHERE
			VC.dt_fac_fecha BETWEEN '" . $dInicial . "' AND '" . $dFinal . "'
			" . $cond_almacen . "
			" . $cond_tipo_documento . "
			" . $cond_serie_documento . "
			" . $cond_numero_documento . "
			" . $cond_estado_documento . "
			" . $cond_cliente);

		$cantidad_registros = $sqlca->fetchRow();
		$paginador = $jqGridModel->Config($cantidad_registros["total"]);

    	$sql = "
SELECT
 VC.ch_almacen AS nu_codigo_almacen,
 VC.ch_fac_tipodocumento AS nu_tipo_documento,
 TDOCU.tab_desc_breve AS no_tipo_documento,
 VC.ch_fac_seriedocumento AS no_serie_documento,
 VC.ch_fac_numerodocumento AS nu_numero_documento,
 CLI.cli_codigo AS nu_codigo_cliente,
 CLI.cli_ruc AS nu_documento_identidad_cliente,
 CLI.cli_razsocial AS no_razsocial_breve_cliente,
 VC.dt_fac_fecha AS fe_emision,
 TMONE.tab_desc_breve AS no_signo_moneda,
 TC.tca_venta_oficial AS ss_tipo_cambio,
 VC.nu_fac_valorbruto AS ss_valor_venta,
 VC.nu_fac_descuento1 AS ss_descuento,
 VC.nu_fac_impuesto1 AS ss_impuesto,
 VC.nu_fac_valortotal AS ss_total,
 VC.ch_fac_tiporecargo2 AS no_codigo_impuesto,
 VC.ch_fac_credito AS no_credito,
 VC.ch_fac_anticipo AS no_anticipo,
 VC.ch_fac_anulado AS no_anulado,
 VC.ch_fac_cd_impuesto3 AS no_despacho_perdido,
 VC.ch_descargar_stock AS no_descargar_stock,
 VC.ch_liquidacion AS nu_liquidacion,
 VC.nu_fac_recargo3 AS nu_estado_documento_sunat,
 VCOM.ch_fac_observacion1 AS txt_observaciones,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AS nu_tipo_documento_referencia,
 TDOCUREFE.tab_desc_breve AS no_tipo_documento_referencia,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[2] AS no_serie_documento_referencia,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[1] AS nu_numero_documento_referencia,
 VCOM.ch_fac_observacion3 AS fe_emision_referencia,
 FPAGO.tab_desc_breve AS no_forma_pago
FROM
 fac_ta_factura_cabecera AS VC
 JOIN int_clientes AS CLI
  USING (cli_codigo)
 LEFT JOIN fac_ta_factura_complemento AS VCOM
  USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 LEFT JOIN int_tabla_general AS TDOCUREFE
  ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
 JOIN int_tabla_general AS TMONE
  ON (SUBSTRING(TMONE.tab_elemento, 5) = VC.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
 LEFT JOIN int_tipo_cambio AS TC
  ON (TC.tca_fecha = VC.dt_fac_fecha AND TC.tca_moneda='02')
 LEFT JOIN int_tabla_general AS FPAGO
  ON (SUBSTRING(FPAGO.tab_elemento, 5) = VC.nu_tipo_pago AND FPAGO.tab_tabla ='05' AND FPAGO.tab_elemento != '000000')
WHERE
 VC.dt_fac_fecha BETWEEN '" . $dInicial . "' AND '" . $dFinal . "'
 " . $cond_almacen . "
 " . $cond_tipo_documento . "
 " . $cond_serie_documento . "
 " . $cond_numero_documento . "
 " . $cond_estado_documento . "
 " . $cond_cliente . "
ORDER BY
 1 DESC, 9 DESC, 2 DESC, 4 DESC, 5 DESC
LIMIT
 " . $paginador["limit"] . "
OFFSET
 " . $paginador["start"];

		$iStatus = $sqlca->query($sql);

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar registros (SQL)', 'arrData' => NULL);
    	if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No hay registros', 'arrData' => 0);
    	else if ((int)$iStatus > 0){
			$data = array();
			foreach ($sqlca->fetchAll() as $row) {
				$sMessage = '';
				if ($row['nu_tipo_documento'] == '20' || $row['nu_tipo_documento'] == '11') {// if nota de credito / débito
          			$sMessage = 'Faltan completar datos';
          			if (
						strlen(trim($row['no_serie_documento_referencia'])) >= 3
						&& strlen(trim($row['nu_numero_documento_referencia'])) > 6
						&& strlen(trim($row['fe_emision_referencia'])) == 10
						&& strlen(trim($row['txt_observaciones'])) > 0
          			) {
						$arrCond = array(
							'dFechaEmision' => trim($row['fe_emision_referencia']),
							'iTipoDocumento' => trim($row['nu_tipo_documento_referencia']),
							'sSerieDocumento' => trim($row['no_serie_documento_referencia']),
							'iNumeroDocumento' => trim($row['nu_numero_documento_referencia']),
							'iIdCliente' => trim($row['nu_codigo_cliente']),
							'iRucCliente' => trim($row['nu_documento_identidad_cliente']),							
						);
						$arrResponseReferencia = $this->verify_reference_sales_invoice_document($arrCond);
						$sMessage = $arrResponseReferencia["sMessage"];
					}
				}// ./ if nota de credito / débito

		        $rows = array();
		        $rows['nu_codigo_almacen'] = trim($row['nu_codigo_almacen']);
		        $rows['nu_tipo_documento'] = $row['nu_tipo_documento'];
		        $rows['no_tipo_documento'] = $row['no_tipo_documento'];
		        $rows['no_serie_documento'] = $row['no_serie_documento'];
		        $rows['nu_numero_documento'] = $row['nu_numero_documento'];
		        $rows['nu_codigo_cliente'] = trim($row['nu_codigo_cliente']);
		        $rows['no_razsocial_breve_cliente'] = $row['no_razsocial_breve_cliente'];
		        $rows['fe_emision'] = $row['fe_emision'];
		        $rows['no_signo_moneda'] = $row['no_signo_moneda'];
		        $rows['ss_tipo_cambio'] = $row['ss_tipo_cambio'];
		        $rows['ss_valor_venta'] = $row['ss_valor_venta'];
		        $rows['ss_descuento'] = $row['ss_descuento'];
		        $rows['ss_impuesto'] = $row['ss_impuesto'];
		        $rows['ss_total'] = $row['ss_total'];
		        $rows['no_codigo_impuesto'] = trim($row['no_codigo_impuesto']);
		        $rows['no_credito'] = $row['no_credito'];
		        $rows['no_anticipo'] = $row['no_anticipo'];
		        $rows['no_anulado'] = $row['no_anulado'];
		        $rows['no_despacho_perdido'] = trim($row['no_despacho_perdido']);
		        $rows['no_descargar_stock'] = trim($row['no_descargar_stock']);
		        $rows['nu_liquidacion'] = $row['nu_liquidacion'];
		        $rows['nu_estado_documento_sunat'] = trim($row['nu_estado_documento_sunat']);
		        $rows['txt_observaciones'] = $row['txt_observaciones'];
		        $rows['nu_tipo_documento_referencia'] = $row['nu_tipo_documento_referencia'];
		        $rows['no_tipo_documento_referencia'] = $row['no_tipo_documento_referencia'];
		        $rows['no_serie_documento_referencia'] = $row['no_serie_documento_referencia'];
		        $rows['nu_numero_documento_referencia'] = $row['nu_numero_documento_referencia'];
		        $rows['fe_emision_referencia'] = $row['fe_emision_referencia'];
		        $rows['txt_mensaje_referencia_documento'] = $sMessage;
		        $rows['no_forma_pago'] = $row['no_forma_pago'];
		        $data[] = $rows;
    		}// ./ foreach
      		$jqGridModel->DataSource($data);
      		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $jqGridModel);
    	}
    	return $arrResponse;
	}

	public function verify_reference_sales_invoice_document($arrCond){
		global $sqlca;

// AAG - Omite la validacion si el parametro invoiceRefUnchecked esta presente y == 1
		$iStatus = $sqlca->query("SELECT par_valor FROM int_parametros WHERE par_nombre = 'invoiceRefUnchecked';");
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'problemas al buscar documento de referencia');
		$row = $sqlca->fetchRow();
		if (isset($row['par_valor']) && $row['par_valor'] == "1")
			return Array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Validacion de referencia desactivada', 'iData' => 1);

		//Campo td pos_transYYYYMM el valor es diferente F = Factura y B = Boleta
		$iTipoDocumentoPT = "B";

		//Verificar nota de crédito o débito con el cliente solo si es factura.
		$cond_cliente_fc = '';
		$cond_cliente_pt = '';
		if ($arrCond['iTipoDocumento'] == '10') {
			$cond_cliente_fc = " AND CLI.cli_ruc = '" . $arrCond['iRucCliente'] . "'";
			$cond_cliente_pt = " AND ruc = '" . $arrCond['iRucCliente'] . "'";
			$iTipoDocumentoPT = "F";
		}

		$iStatus = $sqlca->query("
SELECT
 COUNT(*) AS existe_documento_venta_oficina
FROM
 fac_ta_factura_cabecera
 JOIN int_clientes AS CLI
  USING (cli_codigo)
WHERE
 dt_fac_fecha = '" . $arrCond['dFechaEmision'] . "'
 AND ch_fac_tipodocumento = '" . $arrCond['iTipoDocumento'] . "'
 AND ch_fac_seriedocumento = '" . $arrCond['sSerieDocumento'] . "'
 AND ch_fac_numerodocumento = '" . $arrCond['iNumeroDocumento'] . "'
 " . $cond_cliente_fc);

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'problemas al buscar documento de referencia');

		$row = $sqlca->fetchRow();
		if ((int)$row['existe_documento_venta_oficina'] >= 1)//Existe registro en la tabla fact_fa_factura_cabecera
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Documento de oficina encontrado', 'iData' => 1);
    	else if ($row['existe_documento_venta_oficina'] == '0') {//Si no existe registro en la tabla fact_fa_factura_cabecera, entonces buscamos en la tabla pos_transYYYYMM
      		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No existe documento de referencia', 'iData' => 0);

	    	//Formamos tabla pos_transYYYYMM
	    	$arrFecha = explode('-', $arrCond['dFechaEmision']);
	    	$table_pos_transym = 'pos_trans' . $arrFecha[0] . $arrFecha[1];

			if( $arrFecha[1] != '01' ){ //Si no es Enero
			    $sYearAnt = $arrFecha[0];
			    $sMonthAnt = $arrFecha[1] - 1; //En este proceso le quita el 0 por delante con el que viene, por ejemplo si el mes es Septiembre '09', al restarlo resultaria un 8
				 if($sMonthAnt < 10){ //Solo se agrega el 0 por delante cuando la resta resultante es menor a 10
					$sMonthAnt = '0'.$sMonthAnt;
				 }
			} else { //Si es Enero
			    $sYearAnt = $arrFecha[0] - 1;
			    $sMonthAnt = '12';
			}
	    	$table_pos_transym_ant = 'pos_trans' . $sYearAnt . $sMonthAnt;

			//Verificar si existe tabla pos_transYYYYMM
			$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table_pos_transym."'");
			error_log("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table_pos_transym."'");

			if ( $iStatusTable == 1 ){ //Existe tabla
    			$iStatus = $sqlca->query("
SELECT
 COUNT(*) AS existe_documento_venta_playa
FROM
 " . $table_pos_transym . "
WHERE
 fecha::DATE = '" . $arrCond['dFechaEmision'] . "'
 AND td = '" . $iTipoDocumentoPT . "'
 AND usr = '" . $arrCond['sSerieDocumento'] . "-" . $arrCond['iNumeroDocumento'] ."'
 AND tm = 'V'
 AND grupo != 'D'
 " . $cond_cliente_pt
				);

				error_log("
SELECT
 COUNT(*) AS existe_documento_venta_playa
FROM
 " . $table_pos_transym . "
WHERE
 fecha::DATE = '" . $arrCond['dFechaEmision'] . "'
 AND td = '" . $iTipoDocumentoPT . "'
 AND usr = '" . $arrCond['sSerieDocumento'] . "-" . $arrCond['iNumeroDocumento'] ."'
 AND tm = 'V'
 AND grupo != 'D'
 " . $cond_cliente_pt
				);

				$row = $sqlca->fetchRow();
				if ((int)$row['existe_documento_venta_playa'] >= 1)//Existe registro en la tabla pos_transYM
					$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Documento de playa encontrado', 'iData' => 1);	
			}

			//Verificar si existe tabla pos_transYYYYMM
			$iStatusTableant = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table_pos_transym_ant."'");
			error_log("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table_pos_transym_ant."'");

			if ( $iStatusTableant == 1 ){ //Existe tabla
				$iStatus = $sqlca->query("
SELECT
 COUNT(*) AS existe_documento_venta_playa
FROM
 " . $table_pos_transym_ant . "
WHERE
 fecha::DATE = '" . $arrCond['dFechaEmision'] . "'
 AND td = '" . $iTipoDocumentoPT . "'
 AND usr = '" . $arrCond['sSerieDocumento'] . "-" . $arrCond['iNumeroDocumento'] ."'
 AND tm = 'V'
 AND grupo != 'D'
 " . $cond_cliente_pt
				);

				error_log("
SELECT
 COUNT(*) AS existe_documento_venta_playa
FROM
 " . $table_pos_transym_ant . "
WHERE
 fecha::DATE = '" . $arrCond['dFechaEmision'] . "'
 AND td = '" . $iTipoDocumentoPT . "'
 AND usr = '" . $arrCond['sSerieDocumento'] . "-" . $arrCond['iNumeroDocumento'] ."'
 AND tm = 'V'
 AND grupo != 'D'
 " . $cond_cliente_pt
				);

				$row = $sqlca->fetchRow();
				if ((int)$row['existe_documento_venta_playa'] >= 1)//Existe registro en la tabla pos_transYM
					$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Documento de playa encontrado', 'iData' => 1);
			}
    	}
    	return $arrResponse;
	}

	public function get_sales_serial($arrPost){ //
			//error_log(json_encode($_SESSION));

			//Filtro de almacen con el que el usuario se logueo
			$where_almacen = "";
			if (isset($_SESSION["almacen"])) {
				$where_almacen = "AND ch_almacen = '". $_SESSION['almacen'] ."'";
			}

   		global $sqlca;

   		$sql = "
SELECT
 *,
 num_seriedocumento AS id,
 num_descdocumento AS name
FROM
 int_num_documentos
WHERE
 num_tipdocumento = '" . $arrPost['iTipoDocumento'] . "'
 $where_almacen
ORDER BY
 2
    	";

    	$iStatus = $sqlca->query($sql);
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function get_sales_serial()', 'arrData' => NULL);
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'arrData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $sqlca->fetchAll());
		return $arrResponse;
	}

	public function get_number_by_sale_serial($arrPost){
    	global $sqlca;

		$sql = "
SELECT
 LPAD((NUM.num_numactual::INTEGER + 1)::TEXT, 7, '0') AS nu_numero_documento,
 ALMA.ch_almacen || ' - ' || ALMA.ch_nombre_almacen AS no_almacen
FROM
 int_num_documentos AS NUM
 JOIN inv_ta_almacenes AS ALMA
  USING (ch_almacen)
WHERE
 num_tipdocumento = '" . pg_escape_string($arrPost['iTipoDocumento']) . "'
 AND num_seriedocumento = '" . pg_escape_string($arrPost['iSerieDocumento']) . "'
LIMIT
 1
		";

    	$iStatus = $sqlca->query($sql);
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'No se encontró número');
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'rowData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'rowData' => $sqlca->fetchRow());
		return $arrResponse;
  	}

  	public function get_customer_price_list($arrPost){
    	global $sqlca;

		$iStatus = $sqlca->query("
SELECT
 TRIM(tab_elemento) AS id,
 TRIM(tab_descripcion) AS name
FROM
 int_tabla_general
WHERE
 tab_tabla = 'LPRE'
 AND tab_elemento = (SELECT TRIM(cli_lista_precio) FROM int_clientes WHERE cli_codigo = '" . pg_escape_string($arrPost['iIdCliente']) . "')
LIMIT 1;
		");

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function get_customer_price_list()', 'rowData' => NULL);
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin lista de precio', 'rowData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Precio de cliente configurado encontrado', 'rowData' => $sqlca->fetchRow());
		return $arrResponse;
	}

	public function get_customer_credit_days($arrPost){
    	global $sqlca;

		$iStatus = $sqlca->query("
SELECT
 tab_num_01 AS id
FROM
 int_tabla_general
WHERE
 tab_tabla = '96'
 AND tab_elemento != '000000'
 AND SUBSTRING(tab_elemento, 5) = (SELECT cli_fpago_credito FROM int_clientes WHERE cli_codigo = '" . pg_escape_string($arrPost['iIdCliente']) . "' LIMIT 1)
LIMIT 1;
		");

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function get_customer_credit_days()', 'arrData' => NULL);
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin días de crédito', 'arrData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Días de crédito de cliente configurado encontrado', 'arrData' => $sqlca->fetchRow());
		return $arrResponse;
	}

	public function get_other_customer_fields($arrPost){
    	global $sqlca;

    	$iStatus = $sqlca->query("SELECT * FROM int_clientes WHERE cli_codigo = '" . pg_escape_string($arrPost['iIdCliente']) . "' LIMIT 1;");
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function get_other_customer_fields()', 'rowData' => NULL);
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin otros datos', 'rowData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Cliente encontrado', 'rowData' => $sqlca->fetchRow());
		return $arrResponse;
	}

	public function get_item_sale_price($arrPost){
    	global $sqlca;

	    //Consultar, faltaba considerar la moneda dolarés y otros si que tuviera, en el antiguo programa no lo comtemplaba.
	    $iStatus = $sqlca->query("
SELECT
 LPRE.pre_precio_act1 AS ss_precio_venta_igv_item
FROM 
 int_articulos AS ITEM
 JOIN fac_lista_precios AS LPRE ON (ITEM.art_codigo = LPRE.art_codigo)
WHERE
 ITEM.art_codigo = '" . pg_escape_string($arrPost['iIdItem']) . "'
 AND LPRE.pre_lista_precio = '" . pg_escape_string($arrPost['iIdListaPrecio']) . "'
LIMIT 1;
		");

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function get_item_sale_price()', 'arrData' => NULL);
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Precio de Venta', 'arrData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Item con Precio de venta con IGV encontrado', 'arrData' => $sqlca->fetchRow());
		return $arrResponse;
	}

	public function get_other_item_fields($arrPost){
    	global $sqlca;

		$iStatus = $sqlca->query("SELECT SUBSTRING(art_impuesto1, 5) AS nu_codigo_impuesto_item, TRIM(art_plutipo) AS nu_codigo_tipo_plu FROM int_articulos WHERE art_codigo = '" . pg_escape_string($arrPost['iIdItem']) . "' LIMIT 1;");
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function get_other_item_fields()', 'rowData' => NULL);
		if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'rowData' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Item encontrado', 'rowData' => $sqlca->fetchRow());
		return $arrResponse;
	}

	function verify_register($arrPost){
    	global $sqlca;

    	if ( $arrPost['sNombreModulo'] == 'ventas-manuales') {
			$iStatus = $sqlca->query("
SELECT
 COUNT(*) AS existe
FROM
 fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento = '" . $arrPost['iTipoDocumento'] . "'
 AND ch_fac_seriedocumento = '" . $arrPost['sSerieDocumento'] . "'
 AND ch_fac_numerodocumento = '" . $arrPost['iNumeroDocumento'] . "'
 --AND ch_almacen = '" . $arrPost['iAlmacen'] . "'
LIMIT 1
			");

			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function verify_register()');
			if ($iStatus > 0) {
	        	$row = $sqlca->fetchRow();//0 = no existe registro
	        	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'El comprobante se puede registrar');
	        	if ( (int)$row["existe"] >= 1 )//1 >= existe registro
	          		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'El comprobante ya fue registrado');
      		}
    	} else if ( $arrPost['sNombreModulo'] == 'movimiento-inventario') {
			$arrPost['iAlmacen'] = $iTipoDocumento;
			$arrPost['iTipoDocumento'] = $iTipoDocumento;//Tran_codigo
			$arrPost['sSerieDocumento'] = $sSerieDocumento;
			$arrPost['iNumeroDocumento'] = $iTipoDocumento;
			$arrPost['dFechaEmision'] = $dFechaEmision;
			$arrPost['iIdItem'] = $iIdItem;

			$iStatus = $sqlca->query("
SELECT
 COUNT(*) AS existe
FROM
 inv_movialma
WHERE
 mov_almacen = '" . $arrPost['iAlmacen'] . "'
 AND tran_codigo = '" . $arrPost['iTipoDocumento'] . "'
 AND mov_fecha::DATE = '" . $arrPost['dFechaEmision'] . "'
 AND mov_numero = '" . $arrPost['sSerieDocumento'] . $arrPost['iNumeroDocumento'] . "'
 AND art_codigo = '" . $arrPost['iIdItem'] . "'
LIMIT 1
			");

      		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function verify_register()');
			if ( $iStatus > 0 ) {
				$row = $sqlca->fetchRow();//0 = no existe registro
				$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'El movimiento de inventario se puede registrar');
				if ( (int)$row["existe"] >= 1 )//1 >= existe registro
					$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'El movimiento de inventario ya fue registrado, se puede actualizar');
			}
		}
		// error_log("****** Analisis para guardar documentos, etapa 2 ******");		
        // error_log(json_encode($arrResponse));
    	return $arrResponse;
  	}

  	function add_sales_invoice($arrPost, $arrUserIp, $dHoraActual){ //GUARDAR O MODIFICAR 
  		// Verificando si el código de impuesto será inafecto
  		// Según lo acordado, no interesa si hay productos con IGV / EXO / ETC, basta con que haya un item de inafecto
  		// Todo el comprobante se vuelve como inafecto
	  	$sEstadoInafecto = "N";
	  	foreach ($arrPost['arrDetailSaleInvoice'] as $row) {
	  		if ( empty($row['iCodigoImpuestoItem']) )
	  			$sEstadoInafecto = "S"; // INAFECTAS //
		}

		//OPENSOFT-23	
		//Facturas de Venta. No debe permitir guardar la operación si sus distintos items tienen distinto tipo de afectación. Esto con el fin de evitar que hayan inafectaciones involuntarias de facturas.
		//En caso los distintos artículos tengan distinta afectación, debe mostrarse un mensaje de error.
		$verificacion_afectacion = "";
		foreach ($arrPost['arrDetailSaleInvoice'] as $row) {
			$verificacion_afectacion .= $row['iCodigoImpuestoItem']."|";
		}		
		$verificacion_afectacion = substr($verificacion_afectacion, 0, -1);		
		$array_verificacion_afectacion = explode("|", $verificacion_afectacion);
		$array_unique_verificacion_afectacion = array_unique($array_verificacion_afectacion);
		error_log("Verificacion de afectacion en productos");
		error_log(json_encode(array($arrPost, $arrUserIp, $dHoraActual, $verificacion_afectacion, $array_verificacion_afectacion, $array_unique_verificacion_afectacion, count($array_unique_verificacion_afectacion))));
		if(count($array_unique_verificacion_afectacion) > 1){
			return array('iStatus' => 1, 'sStatus' => 'warning', 'sMessage' => 'No se permite guardar items con distintos tipos de afectación', 'iData' => 0);
		}

	    $arrResponseReferencia = array('iStatus' => -2, 'sStatus' => 'success', 'sMessage' => 'No debemos buscar documento de referencia', 'iData' => 0);

	    // Verificar si existe documento de referencia para (N/C ó N/D), solo para el caso de factura, deberá de contemplar que el cliente sean iguales.
	    // parametros: fecha emisión referencia, tipo de referencia, serie de referencia, número de referencia e ID del Cliente.
	    if (strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '20' || strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '11') {// if nota de credito / débito
	    	$arrCond = array(
		        'dFechaEmision' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaEmisionReferencia'])),
		        'iTipoDocumento' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumentoReferencia'])),
		        'sSerieDocumento' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['sSerieDocumentoReferencia'])),
		        'iNumeroDocumento' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoReferencia'])),
		        'iIdCliente' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])),
		        'iRucCliente' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoIdentidadCliente'])),
	      	);
	      	$arrResponseReferencia = $this->verify_reference_sales_invoice_document($arrCond);
	    }

	    if ( $arrResponseReferencia['sStatus'] == 'success' ) {//Documento encontrado oficina / playa
	  		// start the transaction
	  		$this->begin_transaction();

	  	  // add sale invoice - Header - table fac_ta_factura_cabecera
	  	  $arrResponseHeader = $this->insert_sales_invoice_header($arrPost, $sEstadoInafecto); //INSERT CABECERA

	  	  if ($arrResponseHeader['sStatus'] == 'danger') {
	  	  	$this->rollback_transaction();
	  	  	return $arrResponseHeader;
	  	  }

	  	  // add sale invoice - Detail - table fac_ta_factura_detalle
	  	  $arrResponseDetail = $this->insert_sales_invoice_detail($arrPost,  $sEstadoInafecto); //INSERT DETALLE

	  	  if ($arrResponseDetail['sStatus'] == 'danger') {
	  	  	$this->rollback_transaction();
	  	  	return $arrResponseDetail;
	  	  }

	  	  // add sale invoice - Complementary - table fac_ta_factura_complemento
	  	  $arrResponseComplementary = $this->insert_sales_invoice_complementary($arrPost, $arrUserIp); //INSERT COMPLEMENTO

	  	  if ($arrResponseComplementary['sStatus'] == 'danger') {
	  	  	$this->rollback_transaction();
	  	  	return $arrResponseComplementary;
	  	  }

	  	  // upd increase correlative number - table int_num_documentos
	  		$arrResponseCorrelative = $this->upd_increase_correlative_number($arrPost, 'insert');
	  	  if ($arrResponseCorrelative['sStatus'] == 'danger') {
	  	  	$this->rollback_transaction();
	  	  	return $arrResponseCorrelative;
	  	  }

	  	  // add inventory movement - table inv_movialma
	  	  if ( $arrPost['arrHeaderSaleInvoice']['sDescargarStock'] == 'S' ) {
	  			$arrResponseInventory = $this->insert_inventory_movement($arrPost, $dHoraActual, $arrUserIp);
				if ($arrResponseInventory['sStatus'] == 'danger') {
		  		  	$this->rollback_transaction();
		  		  	return $arrResponseInventory;
				}
	  		}

	  		if (
	  			$arrResponseHeader['sStatus'] == 'success' &&
	  			$arrResponseDetail['sStatus'] == 'success' &&
	  			$arrResponseComplementary['sStatus'] == 'success' &&
	  			$arrResponseCorrelative['sStatus'] == 'success' &&
	  			($arrPost['arrHeaderSaleInvoice']['sDescargarStock'] == 'N' || ($arrPost['arrHeaderSaleInvoice']['sDescargarStock'] == 'S' && $arrResponseInventory['sStatus'] == 'success'))
	  		) {
				// commit the changes
				$this->commit_transaction();
	  			$arrResponse = array('iStatus' => 8, 'sStatus' => 'success', 'sMessage' => 'Registro guardado', 'sNameFunction' => 'add_sales_invoice()');
	  	  		return $arrResponse;
	  		}
	    } else {
	    	return $arrResponseReferencia;
	    }// ./ if nota de credito / débito (buscando documento de referencia)
	}

	function modify_sales_invoice($arrPost, $arrUserIp, $dHoraActual){
		// Verificando si el código de impuesto será inafecto
		// Según lo acordado, no interesa si hay productos con IGV / EXO / ETC, basta con que haya un item de inafecto
		// Todo el comprobante se vuelve como inafecto
		$sEstadoInafecto = "N";
		foreach ($arrPost['arrDetailSaleInvoice'] as $row) {
			if ( empty($row['iCodigoImpuestoItem']) )
				$sEstadoInafecto = "S"; // INAFECTAS //
	    }

	    $arrResponseReferencia = array('iStatus' => -2, 'sStatus' => 'success', 'sMessage' => 'No debemos buscar documento de referencia', 'iData' => 0);

	    // Verificar si existe documento de referencia para (N/C ó N/D), solo para el caso de factura, deberá de contemplar que el cliente sean iguales.
	    // parametros: fecha emisión referencia, tipo de referencia, serie de referencia, número de referencia e ID del Cliente.
	    if (strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '20' || strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '11') {// if nota de credito / débito
		    $arrCond = array(
			  'dFechaEmision' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaEmisionReferencia'])),
			  'iTipoDocumento' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumentoReferencia'])),
			  'sSerieDocumento' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['sSerieDocumentoReferencia'])),
			  'iNumeroDocumento' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoReferencia'])),
			  'iIdCliente' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])),
			  'iRucCliente' => strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoIdentidadCliente'])),
			);
			$arrResponseReferencia = $this->verify_reference_sales_invoice_document($arrCond);
	    }

	    if ( $arrResponseReferencia['sStatus'] == 'success' ) {//Documento encontrado oficina / playa
			// start the transaction
			$this->begin_transaction();

			$arrPostDelete = array(
				'sAction' => 'eliminar',
				'iTipoDocumento' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])),
				'sSerieDocumento' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sSerieDocumento'])),
				'iNumeroDocumento' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento'])),
				'iIdCliente' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])),
				'iNumeroLiquidacion' => strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroLiquidacion'])),
				'iTipoFormaVales' => '1',//1=Sin soltar vales
			);
	
			$arrResponseDelete = $this->cancel_or_delete_sales_invoice($arrPostDelete);
			if ( $arrResponseDelete['sStatus']=='success' ) {
				// add sale invoice - Header - table fac_ta_factura_cabecera
				$arrResponseHeader = $this->insert_sales_invoice_header($arrPost, $sEstadoInafecto); //INSERT CABECERA

				if ($arrResponseHeader['sStatus'] == 'danger') {
				$this->rollback_transaction();
				return $arrResponseHeader;
				}

				// add sale invoice - Detail - table fac_ta_factura_detalle
				$arrResponseDetail = $this->insert_sales_invoice_detail($arrPost,  $sEstadoInafecto); //INSERT DETALLE

				if ($arrResponseDetail['sStatus'] == 'danger') {
					$this->rollback_transaction();
					return $arrResponseDetail;
				}

				// add sale invoice - Complementary - table fac_ta_factura_complemento
				$arrResponseComplementary = $this->insert_sales_invoice_complementary($arrPost, $arrUserIp); //INSERT COMPLEMENTO

				if ($arrResponseComplementary['sStatus'] == 'danger') {
					$this->rollback_transaction();
					return $arrResponseComplementary;
				}

				// upd increase correlative number - table int_num_documentos
				if($arrPost['action'] == "modify"){
					$arrResponseCorrelative['sStatus'] = 'success';
				}else{
					$arrResponseCorrelative = $this->upd_increase_correlative_number($arrPost, 'insert');
					if ($arrResponseCorrelative['sStatus'] == 'danger') {
						$this->rollback_transaction();
						return $arrResponseCorrelative;
					}
				}

				// add inventory movement - table inv_movialma
				if ( $arrPost['arrHeaderSaleInvoice']['sDescargarStock']=='S' ) {
					$arrResponseInventory = $this->insert_inventory_movement($arrPost, $dHoraActual, $arrUserIp);
					if ($arrResponseInventory['sStatus']=='danger') {
						$this->rollback_transaction();
						return $arrResponseInventory;
					}
				}

				if (
					$arrResponseHeader['sStatus'] == 'success' &&
					$arrResponseDetail['sStatus'] == 'success' &&
					$arrResponseComplementary['sStatus'] == 'success' &&
					$arrResponseCorrelative['sStatus'] == 'success' &&
					($arrPost['arrHeaderSaleInvoice']['sDescargarStock'] == 'N' || ($arrPost['arrHeaderSaleInvoice']['sDescargarStock'] == 'S' && $arrResponseInventory['sStatus'] == 'success'))
				) {
					// commit the changes
					$this->commit_transaction();
					$arrResponse = array('iStatus' => 8, 'sStatus' => 'success', 'sMessage' => 'Registro guardado', 'sNameFunction' => 'add_sales_invoice()');
					return $arrResponse;
				}
			} else {
				return $arrResponseDelete;
			}// ./ Delete
        } else {
		    return $arrResponseReferencia;
	    }// ./ if nota de credito / débito (buscando documento de referencia)
  	}

	function edit_sales_invoice($arrGet) {
		global $sqlca;
		$iStatus = $sqlca->query("
SELECT
 TRIM(VC.ch_almacen) AS nu_codigo_almacen,
 TRIM(ALMA.ch_nombre_almacen) AS no_nombre_almacen,
 VC.ch_fac_tipodocumento AS nu_tipo_documento,
 VC.ch_fac_seriedocumento AS no_serie_documento,
 VC.ch_fac_numerodocumento AS nu_numero_documento,
 TO_CHAR(VC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_emision,
 VC.nu_tipo_pago,
 VC.ch_fac_anticipo AS no_anticipo,
 DIAS.tab_num_01 AS nu_codigo_dias_vencimiento,
 TO_CHAR(VC.fe_vencimiento, 'DD/MM/YYYY') AS fe_vencimiento,
 TRIM(VC.ch_fac_credito) AS no_credito,
 VC.ch_fac_moneda AS nu_codigo_moneda,
 VC.nu_tipocambio AS ss_tipo_cambio,
 VC.ch_descargar_stock AS no_descargar_stock,
 TRIM(CLI.cli_codigo) AS nu_codigo_cliente,
 TRIM(CLI.cli_ruc) AS nu_ruc_cliente,
 CLI.cli_direccion AS txt_direccion_cliente,
 TRIM(CLI.cli_anticipo) AS no_anticipo_cliente,
 TRIM(CLI.cli_razsocial) AS no_nombre_cliente,
 TRIM(LPRE.tab_elemento) AS nu_codigo_precio_cliente,
 TRIM(LPRE.tab_descripcion) AS no_nombre_precio_cliente,
 TRIM(VC.ch_fac_tiporecargo2) AS no_codigo_impuesto,
 TRIM(VC.ch_fac_cd_impuesto3) AS no_despacho_perdido,
 VCOM.ch_fac_observacion1 AS txt_observaciones,
 VCOM.ch_fac_observacion2 AS numero_serie_tipo_documento_referencia,
 VCOM.ch_fac_observacion3 AS fe_emision_referencia,
 CASE 
 	WHEN VCOM.nu_fac_complemento_direccion = '' OR VCOM.nu_fac_complemento_direccion IS NULL OR 
	     (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[2] = '' OR (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[2] IS NULL THEN '' --Aqui valida la Detraccion o Retencion
	ELSE VCOM.nu_fac_complemento_direccion 
 END AS numcuenta_importe_porcentaje_codigoimpuestoservicio_detraccion,
 ITEM.art_codigo AS id_item,
 ITEM.art_descripcion AS no_nombre_item,
 ROUND(VD.nu_fac_cantidad, 3) AS qt_cantidad_item,
 ROUND(VD.nu_fac_precio, 3) AS ss_precio_venta_item,
 ROUND(VD.nu_fac_importeneto, 2) AS ss_valor_venta_item,
 ROUND(VD.nu_fac_impuesto1, 2) AS ss_impuesto_item,
 ROUND(VD.nu_fac_descuento1, 2) AS ss_descuento_item,
 ROUND(VD.nu_fac_valortotal, 2) AS ss_total_item,
 ROUND(VC.nu_fac_valorbruto, 2) AS ss_valor_venta,
 ROUND(VC.nu_fac_impuesto1, 2) AS ss_impuesto,
 ROUND(VC.nu_fac_recargo1, 2) AS ss_gratuita,
 ROUND(VC.nu_fac_descuento1, 2) AS ss_descuento,
 ROUND(VC.nu_fac_valortotal, 2) AS ss_total,
 VC.nu_fac_recargo3 AS nu_estado_documento_sunat,
 VC.ch_liquidacion AS nu_liquidacion,
 TRIM(VCOM.ch_cat_sunat) AS ch_cat_sunat,
 CASE WHEN CLI.cli_anticipo = 'S' AND CLI.cli_ndespacho_efectivo = 1 THEN 'S' ELSE 'N' END AS no_venta_adelantada_cliente
FROM
 fac_ta_factura_cabecera AS VC
 JOIN inv_ta_almacenes AS ALMA
  USING (ch_almacen)
 LEFT JOIN fac_ta_factura_detalle AS VD
  USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 LEFT JOIN fac_ta_factura_complemento AS VCOM
  USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_clientes AS CLI
  USING (cli_codigo)
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 LEFT JOIN int_tabla_general AS TDOCUREFE
  ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
 JOIN int_tabla_general AS TMONE
  ON (SUBSTRING(TMONE.tab_elemento, 5) = VC.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
 JOIN int_tabla_general AS LPRE
  ON (LPRE.tab_elemento = TRIM(CLI.cli_lista_precio) AND LPRE.tab_tabla = 'LPRE' AND LPRE.tab_elemento != '000000')
 LEFT JOIN int_tabla_general AS DIAS
  ON (SUBSTRING(DIAS.tab_elemento, 5) = TRIM(CLI.cli_fpago_credito) AND DIAS.tab_tabla = '96' AND DIAS.tab_elemento != '000000')
 LEFT JOIN int_articulos AS ITEM
  USING (art_codigo)
WHERE
 vc.ch_almacen = '" . strip_tags(stripslashes($arrGet['iCodigoAlmacen'])) . "'
 AND vc.dt_fac_fecha = '" . strip_tags(stripslashes($arrGet['dFechaEmision'])) . "'
 AND ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrGet['iTipoDocumento'])) . "'
 AND ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrGet['sSerieDocumento'])) . "'
 AND ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrGet['iNumeroDocumento'])) . "'
 AND cli_codigo = '" . strip_tags(stripslashes($arrGet['iIdCliente'])) . "'
		");

	    $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar documento (SQL)', 'arrData' => NULL);
	    if ($iStatus == 0)
	    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No se encontró el documento', 'arrData' => 0);
	    else if ((int)$iStatus > 0)
	    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $sqlca->fetchAll());
	    return (object)$arrResponse;
	}

	function insert_sales_invoice_header($arrPost, $sEstadoInafecto){
   		global $sqlca;
/**
* Reglas de tipos de impuestos por OCS:
El orden que se esta llevando de los estados es (abecedario), en caso de que se desee crear mas, seguir con la misma lógica o consultar
      Impuesto                  | ch_fac_tiporecargo2    |    Valor de impuesto (S / N)
----------------------------------------------------------------------------------------
- Op. Gravadas                  =   vacío 				 =		S
- Op. Exoneradas                =   S 				 	 =		N
- Op. Gratuitas                 =   T 				 	 =		S
- Op. Gratuitas + Exoneradas    =   U  				 	 =		N
- Op. Inafectas                 =   V 				  	 =		N
- Op. Gratuitas + Inafectas     =   W 				  	 =		N
*/
		$fImpuesto = strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotIGV']));
	    $sCodigoImpuestoOCS = "";

	    if (
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "N" &&
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "S" &&
	      $sEstadoInafecto == "N"
	    ) {
	      $sCodigoImpuestoOCS = "T";//Gratuita
		  error_log("Gratuita");
		}

	    if (
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "S" &&
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "N" &&
	      ($sEstadoInafecto == "N" || $sEstadoInafecto == "S")
	    ) {
	      $sCodigoImpuestoOCS = "S";//Exonerada
	      $fImpuesto = 0.00;
		  error_log("Exonerada");
	    }

	    if (
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "S" &&
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "S" &&
	      ($sEstadoInafecto == "N" || $sEstadoInafecto == "S")
	    ) {
	      $sCodigoImpuestoOCS = "U";//Gratuita + Exonerada
	      $fImpuesto = 0.00;
		  error_log("Gratuita + Exonerada");
	    }

	    if (
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "N" &&
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "N" &&
	    	$sEstadoInafecto == "S"
	    ) {
	      $sCodigoImpuestoOCS = "V";//Inafecta
	      $fImpuesto = 0.00;
		  error_log("Inafecta");
	    }

	    if (
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "N" &&
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "S" &&
	    	$sEstadoInafecto == "S"
	    ) {
	      $sCodigoImpuestoOCS = "W";//Gratuita + Inafecta
	      $fImpuesto = 0.00;
		  error_log("Gratuita + Inafecta");
	    }

	    // Verificar si existe días de vencimiento
	    $iDiasVencimiento = 0;
	    if ( isset($arrPost['arrHeaderSaleInvoice']['iDiasVencimiento']) )
	    	$iDiasVencimiento = $arrPost['arrHeaderSaleInvoice']['iDiasVencimiento'];

	    $sql_cabecera = "
INSERT INTO fac_ta_factura_cabecera (
 ch_fac_tipodocumento,
 ch_fac_seriedocumento,
 ch_fac_numerodocumento,
 dt_fac_fecha,
 ch_almacen,
 ch_punto_venta,
 ch_fac_cd_impuesto1,
 nu_tipo_pago,
 ch_fac_anticipo,
 ch_fac_forma_pago,
 fe_vencimiento,
 ch_fac_credito,
 ch_fac_moneda,
 nu_tipocambio,
 ch_descargar_stock,
 cli_codigo,
 ch_fac_tiporecargo2,
 ch_fac_cd_impuesto3,
 nu_fac_valorbruto,
 nu_fac_impuesto1,
 nu_fac_recargo1,
 nu_fac_descuento1,
 nu_fac_valortotal,
 flg_replicacion,
 fecha_replicacion,
 nu_fac_recargo3,
 ch_fac_anulado,
 ch_liquidacion
) VALUES (
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sSerieDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['dFechaEmision'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iAlmacen'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iAlmacen'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iCodigoImpuestoItem'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iFormaPago'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sAnticipado'])) . "',
 '" . strip_tags(stripslashes($iDiasVencimiento)) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['dFechaVencimiento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sCredito'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sMoneda'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTipoCambio'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sDescargarStock'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])) . "',
 '" . strip_tags(stripslashes($sCodigoImpuestoOCS)) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sDespachoPerdido'])) . "', //
 " . ( strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotGravada'])) + strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotExonerada'])) + strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotInafecta'])) ) . ",
 " . $fImpuesto . ",
 " . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotGratuita'])) . ",
 " . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotDescuento'])) . ",
 " . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['fTotTotal'])) . ",
 0,
 now(),
 0,
 'N',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroLiquidacion'])) . "'
);
		";

		$arrResponse = array('iStatus' => 1, 'sStatus' => 'success', 'sMessage' => 'success SQL - Header', 'sNameFunction' => 'insert_sales_invoice_header()');
		$iStatusHeader = $sqlca->query($sql_cabecera);
		if ($iStatusHeader < 0)
			$arrResponse = array(
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al guardar cabecera',
				'sNameFunction' => 'insert_sales_invoice_header()',
				'sMessageSQL' => $sqlca->get_error(),
				'SQL' => $sql_cabecera,
			);
  		return $arrResponse;
	}

	function insert_sales_invoice_detail($arrPost, $sEstadoInafecto){
    	global $sqlca;

		$sCodigoImpuestoOCS = "";

	    if (
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "S" &&
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "N" &&
	      ($sEstadoInafecto == "N" || $sEstadoInafecto == "S")
	    ) {
		  $sCodigoImpuestoOCS = "S";//Exonerada
		  error_log("Exonerada");
		}

	    if (
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "N" &&
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "S" &&
	      $sEstadoInafecto == "N"
	    ) {		
	      $sCodigoImpuestoOCS = "T";//Gratuita
		  error_log("Gratuita");
		}

	    if (
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "S" &&
	      strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "S" &&
	      ($sEstadoInafecto == "N" || $sEstadoInafecto == "S")
	    ) {		
	      $sCodigoImpuestoOCS = "U";//Gratuita + Exonerada
		  error_log("Gratuita + Exonerada");
		}

	    if (
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "N" &&
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "N" &&
	    	$sEstadoInafecto == "S"
	    ) {
	      $sCodigoImpuestoOCS = "V";//Inafecta
		  error_log("Inafecta");
		}

	    if (
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sExonerado'])) == "N" &&
			strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sTransferenciaGratuita'])) == "S" &&
	    	$sEstadoInafecto == "S"
	    ) {
	      $sCodigoImpuestoOCS = "W";//Gratuita + Inafecta
		  error_log("Gratuita + Inafecta");
		}

		$arrSQLDetail = array();
		foreach ($arrPost['arrDetailSaleInvoice'] as $row) {
			$fSubtotal = strip_tags(stripslashes($row['fSubtotal']));
			$fImpuesto = strip_tags(stripslashes($row['fImpuesto']));
			$fTotal = strip_tags(stripslashes($row['fTotal']));

			if ( $sCodigoImpuestoOCS != "" ) {
				$fSubtotal = strip_tags(stripslashes($row['fTotal']));
				if ( $sCodigoImpuestoOCS != "T" ) // Si no es gratuita, no se guarda el impuesto
					$fImpuesto = 0;

				if ( $sCodigoImpuestoOCS == "T" || $sCodigoImpuestoOCS == "U" || $sCodigoImpuestoOCS == "W" ){// Grauita || (Gratuita + Exonerada) || (Gratuita + Inafecta)
					$fSubtotal = 0;
					$fTotal = 0;
				}
			}

			$arrSQLDetail[] = "(
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sSerieDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iListaPrecioCliente'])) . "',
 '" . $sCodigoImpuestoOCS . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iCodigoImpuestoItem'])) . "',
 '" . strip_tags(stripslashes($row['iIdItem'])) . "',
 '" . strip_tags(stripslashes($row['sNombreItem'])) . "',
  " . strip_tags(stripslashes($row['fCantidad'])) . ",
  " . strip_tags(stripslashes($row['fPrecioVenta'])) . ",
  " . $fSubtotal . ",
  " . $fImpuesto . ",
  " . strip_tags(stripslashes($row['fDescuento'])) . ",
  " . $fTotal . ")
  			";
		}// /. Foreach - Detalle de items

    	$sql_detalle = "
INSERT INTO fac_ta_factura_detalle (
 ch_fac_tipodocumento,
 ch_fac_seriedocumento,
 ch_fac_numerodocumento,
 cli_codigo,
 pre_lista_precio,
 ch_fac_tiporecargo2,
 ch_fac_cd_impuesto1,
 art_codigo,
 ch_art_descripcion,
 nu_fac_cantidad,
 nu_fac_precio,
 nu_fac_importeneto,
 nu_fac_impuesto1,
 nu_fac_descuento1,
 nu_fac_valortotal
) VALUES " . implode(',', $arrSQLDetail);

		$arrResponse = array('iStatus' => 2, 'sStatus' => 'success', 'sMessage' => 'success SQL - Detail', 'sNameFunction' => 'insert_sales_invoice_detail()');
		$iStatusDetail = $sqlca->query($sql_detalle);
		if ($iStatusDetail < 0)
    		$arrResponse = array('iStatus' => $iStatusDetail, 'sStatus' => 'danger', 'sMessage' => 'Problemas al guardar detalle', 'sNameFunction' => 'insert_sales_invoice_detail()');
    	return $arrResponse;
	}

	function insert_sales_invoice_complementary($arrPost, $arrUserIp){
    	global $sqlca;

		$sNumeroSerieTipoReferencia = '';
		$dFechaEmisionReferencia = '';
	    if (
	    	(
	    	!empty($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoReferencia']) &&
	    	!empty($arrPost['arrComplementarySaleInvoice']['sSerieDocumentoReferencia']) &&
	    	!empty($arrPost['arrComplementarySaleInvoice']['iTipoDocumentoReferencia']) &&
	    	!empty($arrPost['arrComplementarySaleInvoice']['dFechaEmisionReferencia'])
	    	) && (strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '11' || strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '20')
	    ){
	    	$sNumeroSerieTipoReferencia = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoReferencia'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['sSerieDocumentoReferencia'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumentoReferencia']));
	    	$dFechaEmisionReferencia = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaEmisionReferencia']));
	    }

		 //Detraccion
	    $sNumeroCuentaImportePorcentajeCodigoBienesServicio = '';
	    if ( strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iDetraccion'])) == 'S' ) { //Combo SPOT para las operaciones: 'Ninguna', 'Detraccion', 'Retencion'
	    	$sNumeroCuentaImportePorcentajeCodigoBienesServicio = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroCuentaDetraccion'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['fImporteDetraccion'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iPorcentajeDetraccion'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iCodigoBienServicioDetraccion']));
	    }

		 //Retencion
		 $sImporteRetencion = '';
		 if ( strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iDetraccion'])) == 'R' ) { //Combo SPOT para las operaciones: 'Ninguna', 'Detraccion', 'Retencion'
			$sImporteRetencion = strip_tags(stripslashes('R')) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['fImporteRetencion']));

			//Asignamos data de retencion a la variable que se usa para insertar la informacion SPOT en fac_ta_factura_complemento
			$sNumeroCuentaImportePorcentajeCodigoBienesServicio = $sImporteRetencion;
		 }

		$cat_sunat = NULL;
		if( strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '20' ){ //SI ES NC
			$cat_sunat = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['catalogo09Sunat_NC']));
		}else if( strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) == '11' ){ //SI ES ND
			$cat_sunat = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['catalogo10Sunat_ND']));
		}

    $sql_complemento = "
INSERT INTO fac_ta_factura_complemento (
 ch_fac_tipodocumento,
 ch_fac_seriedocumento,
 ch_fac_numerodocumento,
 dt_fac_fecha,
 cli_codigo,
 ch_fac_ruc,
 nu_fac_direccion,
 ch_fac_observacion1,
 ch_fac_observacion2,
 ch_fac_observacion3,
 nu_fac_complemento_direccion,
 dt_fechactualizacion,
 ch_fac_nombreclie,
 flg_replicacion,
 ch_usuario,
 ch_auditorpc,
 ch_cat_sunat
) VALUES (
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sSerieDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['dFechaEmision'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])) . "',
 '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoIdentidadCliente'])) . "',
 '" . strip_tags(stripslashes($this->text_clean_bd($arrPost['arrComplementarySaleInvoice']['sDireccionCliente']))) . "',
 '" . strip_tags(stripslashes($this->text_clean_bd_fe($arrPost['arrComplementarySaleInvoice']['sObservacion']))) . "',
 '" . $sNumeroSerieTipoReferencia . "',
 '" . $dFechaEmisionReferencia . "',
 '" . $sNumeroCuentaImportePorcentajeCodigoBienesServicio . "',
 now(),
 '" . strip_tags(stripslashes($this->text_clean_bd($arrPost['arrComplementarySaleInvoice']['sNombreCliente']))) . "',
 0,
 '" . strip_tags(stripslashes(substr($arrUserIp['sNombreUsuario'], -10))) . "',
 '" . strip_tags(stripslashes($arrUserIp['sIp'])) . "',
 '" . $cat_sunat . "'
);
		";

		$arrResponse = array('iStatus' => 3, 'sStatus' => 'success', 'sMessage' => 'success SQL - Complementary', 'sNameFunction' => 'insert_sales_invoice_complementary()');
		$iStatusComplementary = $sqlca->query($sql_complemento);
		if ($iStatusComplementary < 0)
	    	$arrResponse = array('iStatus' => $iStatusComplementary, 'sStatus' => 'danger', 'sMessage' => 'Problemas al guardar datos complementarios', 'sNameFunction' => 'insert_sales_invoice_complementary()');
	    return $arrResponse;
  	}

	function upd_increase_correlative_number($arrPost, $sAccion) {
  		global $sqlca;

		$arrResponse = array('iStatus' => 4, 'sStatus' => 'danger', 'sMessage' => 'Problemas al actualizar correlativo', 'sNameFunction' => 'upd_increase_correlative_number()');
		if ($sqlca->functionDB("util_fn_corre_docs_fecha('" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento'])) . "', '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sSerieDocumento'])) . "', '" . $sAccion . "', '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['dFechaEmision'])) . "')"))
			$arrResponse = array('iStatus' => 4, 'sStatus' => 'success', 'sMessage' => 'Correlativo actualizado', 'sNameFunction' => 'upd_increase_correlative_number()');
		return $arrResponse;
	}

	function insert_inventory_movement($arrPost, $dHoraActual, $arrUserIp) {
	  	global $sqlca;

	  	$iTipoDocumento = strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iTipoDocumento']));
	  	$sSerieDocumento = strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['sSerieDocumento']));
	  	$iNumeroDocumento = strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento']));

	  	//Consultar, ya que el número de formulario es character(10), el sistema antiguo lo compone por serie y número pero realiza un substr
		if(strlen($sSerieDocumento) > 3)
			$iNumeroDocumento = substr($iNumeroDocumento, -6);

  		$iAlmacen = strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iAlmacen']));
  		$iStatus = $sqlca->query("SELECT TRIM(tran_naturaleza) AS nu_tipo_naturaleza, tran_destino AS nu_codigo_almacen_destino FROM inv_tipotransa WHERE tran_codigo = '" . $iTipoDocumento . "';");

  		if ((int)$iStatus < 0) {
  			$arrResponse = array('iStatus' => 5, 'sStatus' => 'danger', 'sMessage' => 'Problemas al obtener naturaleza y almacén destino', 'sNameFunction' => 'insert_inventory_movement()');
  			return $arrResponse;
  		}

		// Tabla inv_tipotransa
		$row = $sqlca->fetchRow();
		$iTipoNaturaleza = $row['nu_tipo_naturaleza'];
		$iCodigoAlmacenDestino = $row['nu_codigo_almacen_destino'];

		// Obtener año y mes de la fecha de emisión
		$dFechaEmision = strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['dFechaEmision']));
		$arrFecha = explode("-", $dFechaEmision);
		$dFechaEmisionAnio = $arrFecha[0];
		$dFechaEmisionMes = $arrFecha[1];

  		// Tabla inv_movialma
    	$arrSQLInventory = array();
    	foreach ($arrPost['arrDetailSaleInvoice'] as $row) {
			$iIdItem = strip_tags(stripslashes($row['iIdItem']));
			$fCantidad = strip_tags(stripslashes($row['fCantidad']));
	    	if (strip_tags(stripslashes($row['iCodigoTipoPlu'])) == '1') { //Estandar
		    	//Obtener stk_costo(mes) costo promedio, el programa antiguo lo tomaba desde una funcion DB util_fn_costo_promedio tabla inv_saldoalma
		   		//Parametros (YYYY, MM, Código de Item, Código de Almacén)
		    	$fCostoPromedio = $this->get_average_cost($dFechaEmisionAnio, $dFechaEmisionMes, $iIdItem, $iAlmacen);
		    	if ( $fCostoPromedio == 0 )// obtiene el campo art_costoreposicion de la tabla int_articlos
	    			$fCostoPromedio = $this->get_replacement_cost($dFechaEmisionAnio, $dFechaEmisionMes, $iIdItem, $iAlmacen);
	    		//TO_DATE('2018-06-18', 'YYYY-MM-DD') + current_time,

				$arrSQLDetail[] = "(
 '" . $sSerieDocumento . $iNumeroDocumento . "',
 '" . $iTipoDocumento . "',
 '" . $iTipoDocumento . "',
 '" . $sSerieDocumento . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento'])) . "',
 '" . $dFechaEmision . " " . $dHoraActual . "',
 'C',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])) . "',
 '" . $iAlmacen . "',
 '" . $iTipoNaturaleza . "',
 '" . $iAlmacen . "',
 '" . $iCodigoAlmacenDestino . "',
 '" . $iIdItem . "',
 " . $fCantidad . ",
 " . $fCostoPromedio . ",
 " . $fCostoPromedio . ",
 " . ($fCantidad * $fCostoPromedio) . ",
 current_timestamp,
 '" . strip_tags(stripslashes(substr($arrUserIp['sNombreUsuario'], -15))) . "'
)
				";
			} else { // Fin de PLU ESTANDAR E INICIA PLU SALIENTE
				$iStatus = $sqlca->query("SELECT ch_item_estandar AS nu_codigo_item, nu_cantidad_descarga AS qt_cantidad FROM int_ta_enlace_items WHERE art_codigo = '" . $iIdItem . "'");
  				if ((int)$iStatus < 0) {
					$arrResponse = array('iStatus' => 6, 'sStatus' => 'danger', 'sMessage' => 'Error al obtener lo(s) enlace(s) del item "' . $iIdItem . '" tipo PLU Saliente', 'sNameFunction' => 'insert_inventory_movement()');
					return $arrResponse;
					break;
  				}

				$arrRows = $sqlca->fetchAll();
				foreach ($arrRows as $rowPluSaliente) {
					$iIdItemEstandar = trim($rowPluSaliente['nu_codigo_item']);
					$fCantidadEstandar = (double)$rowPluSaliente['qt_cantidad'];
					//Obtener stk_costo(mes) costo promedio, el programa antiguo lo tomaba desde una funcion DB util_fn_costo_promedio tabla inv_saldoalma
		   			//Parametros (YYYY, MM, Código de Item, Código de Almacén)
		    		$fCostoPromedio = $this->get_average_cost($dFechaEmisionAnio, $dFechaEmisionMes, $iIdItemEstandar, $iAlmacen);
		    		if ($fCostoPromedio == 0 )// obtiene el campo art_costoreposicion de la tabla int_articlos
		    			$fCostoPromedio = $this->get_replacement_cost($dFechaEmisionAnio, $dFechaEmisionMes, $iIdItemEstandar, $iAlmacen);

					$arrSQLDetail[] = "(
 '" . $sSerieDocumento . $iNumeroDocumento . "',
 '" . $iTipoDocumento . "',
 '" . $iTipoDocumento . "',
 '" . $sSerieDocumento . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iNumeroDocumento'])) . "',
 '" . $dFechaEmision . " " . $dHoraActual . "',
 'C',
 '" . strip_tags(stripslashes($arrPost['arrHeaderSaleInvoice']['iIdCliente'])) . "',
 '" . $iAlmacen . "',
 '" . $iTipoNaturaleza . "',
 '" . $iAlmacen . "',
 '" . $iCodigoAlmacenDestino . "',
 '" . $iIdItemEstandar . "',
 " . $fCantidadEstandar * (double)$fCantidad . ",
 " . $fCostoPromedio . ",
 " . $fCostoPromedio . ",
 " . ($fCantidadEstandar * $fCostoPromedio) . ",
 current_timestamp,
 '" . strip_tags(stripslashes(substr($arrUserIp['sNombreUsuario'], -15))) . "'
)
					";
				}// /. Foreach PLU Saliente
			}// ./ Verificación si es PLU Estándar o Saliente
   		}// ./ Foreach insert de PLU Estándar o Saliente

	    // En el programa antiguo no se contemplaba estos datos
	    // mov_tipdocuref          | character(2)
		// mov_docurefe            | character(12)

    	$sql_inventarios = "
INSERT INTO inv_movialma (
 mov_numero,
 tran_codigo,
 mov_tipdocuref,
 mov_docurefe,
 mov_fecha,
 mov_tipoentidad,
 mov_entidad,
 mov_almacen,
 mov_naturaleza,
 mov_almaorigen,
 mov_almadestino,
 art_codigo,
 mov_cantidad,
 mov_costounitario,
 mov_costopromedio,
 mov_costototal,
 mov_fecha_actualizacion,
 mov_usuario
) VALUES " . implode(',', $arrSQLDetail);

		$arrResponse = array('iStatus' => 7, 'sStatus' => 'success', 'sMessage' => 'success SQL - inv_movialma', 'sNameFunction' => 'insert_inventory_movement()');
		$iStatusInventory = $sqlca->query($sql_inventarios);
		if ((int)$iStatusInventory < 0)
    		$arrResponse = array('iStatus' => $iStatusInventory, 'sStatus' => 'danger', 'sMessage' => 'Error al insertar movimientos en el kardex', 'sNameFunction' => 'insert_inventory_movement()');
    	return $arrResponse;
	}

	function get_average_cost($dFechaEmisionAnio, $dFechaEmisionMes, $iIdItem, $iAlmacen) {
		global $sqlca;
		//Si no existe registro igual devuelve un dato -> 0
		return $sqlca->functionDB("util_fn_costo_promedio('" . $dFechaEmisionAnio . "','" . $dFechaEmisionMes . "', '" . $iIdItem . "', '" . $iAlmacen . "')");
	}

	function get_replacement_cost($dFechaEmisionAnio, $dFechaEmisionMes, $iIdItem, $iAlmacen) {
  		global $sqlca;
		//Si no existe item igual devuelve un dato -> 0
		//Función extraña util_fn_costo_promedio_articulos, te solicita 4 parametros, pero si revisamos que realiza la función en DB
		//Solo ejecuta un SELECT art_costoreposicion AS costo FROM int_articulos WHERE art_codigo=id_item
		//Es decir; utiliza solo utiliza solo el parámetro de id_item
		//Consultar
  		return $sqlca->functionDB("util_fn_costo_promedio_articulos('" . $dFechaEmisionAnio . "','" . $dFechaEmisionMes . "', '" . $iIdItem . "', '" . $iAlmacen . "')");
	}

  	function save_sales_invoice_complementary($arrPost, $arrUserIp){
		// start the transaction
		$this->begin_transaction();

		// upd sale invoice - Complementary - table fac_ta_factura_complemento
		$arrResponseComplementary = $this->upd_sales_invoice_complementary($arrPost, $arrUserIp);

		if ( $arrResponseComplementary['sStatus'] == 'danger' ) {
	  		$this->rollback_transaction();
	  		return $arrResponseComplementary;
		}

		if ( $arrResponseComplementary['sStatus'] == 'success' ) {
			// commit the changes
			$this->commit_transaction();
			$arrResponse = array('iStatus' => 1, 'sStatus' => 'success', 'sMessage' => 'Registro modificado', 'sNameFunction' => 'save_sales_invoice_complementary()');
	  		return $arrResponse;
		}
	}

  	function upd_sales_invoice_complementary($arrPost, $arrUserIp){
    	global $sqlca;

		$sNumeroSerieTipoReferencia = '';
		$dFechaEmisionReferencia = '';
	    if (
	    	(
	    	!empty($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoReferencia']) &&
	    	!empty($arrPost['arrComplementarySaleInvoice']['sSerieDocumentoReferencia']) &&
	    	!empty($arrPost['arrComplementarySaleInvoice']['iTipoDocumentoReferencia']) &&
	    	!empty($arrPost['arrComplementarySaleInvoice']['dFechaEmisionReferencia'])
	    	) && (strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumento'])) == '11' || strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumento'])) == '20')
	    ){
	    	$sNumeroSerieTipoReferencia = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumentoReferencia'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['sSerieDocumentoReferencia'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumentoReferencia']));
	    	$dFechaEmisionReferencia = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaEmisionReferencia']));
	    }

		 //Detraccion
	    $sNumeroCuentaImportePorcentajeCodigoBienesServicio = '';
	    if ( strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iDetraccion'])) == 'S' ) {
	    	$sNumeroCuentaImportePorcentajeCodigoBienesServicio = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroCuentaDetraccion'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['fImporteDetraccion'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iPorcentajeDetraccion'])) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iCodigoBienServicioDetraccion']));
	    }

		 //Retencion
		 $sImporteRetencion = '';
		 if ( strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iDetraccion'])) == 'R' ) { //Combo SPOT para las operaciones: 'Ninguna', 'Detraccion', 'Retencion'
			$sImporteRetencion = strip_tags(stripslashes('R')) . '*' . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['fImporteRetencion']));

			//Asignamos data de retencion a la variable que se usa para insertar la informacion SPOT en fac_ta_factura_complemento
			$sNumeroCuentaImportePorcentajeCodigoBienesServicio = $sImporteRetencion;
		 }

		$cat_sunat = NULL;
		if( strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumento'])) == '20' ){ //SI ES NC
			$cat_sunat = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['catalogo09Sunat_NC']));
		}else if( strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumento'])) == '11' ){ //SI ES ND
			$cat_sunat = strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['catalogo10Sunat_ND']));
		}

	    $sql_cabecera = "
UPDATE
 fac_ta_factura_cabecera
SET
 nu_tipo_pago = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iFormaPago'])) . "',
 fe_vencimiento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaVencimiento'])) . "'
WHERE
 ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumento'])) . "'
 AND ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['sSerieDocumento'])) . "'
 AND ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumento'])) . "'
 AND dt_fac_fecha = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaEmision'])) . "' 
	";
		$iStatusHeader = $sqlca->query($sql_cabecera);
		if ((int)$iStatusHeader < 0)
    		return array('iStatus' => $iStatusHeader, 'sStatus' => 'danger', 'sMessage' => 'Problemas al modificar cabecera', 'sNameFunction' => 'upd_sales_invoice_complementary()');

    	$sql_complemento = "
UPDATE
 fac_ta_factura_complemento
SET
 ch_fac_observacion1 = '" . strip_tags(stripslashes($this->text_clean_bd_fe($arrPost['arrComplementarySaleInvoice']['sObservacion']))) . "',
 ch_fac_observacion2 = '" . $sNumeroSerieTipoReferencia . "',
 ch_fac_observacion3 = '" . $dFechaEmisionReferencia . "',
 nu_fac_complemento_direccion = '" . $sNumeroCuentaImportePorcentajeCodigoBienesServicio . "',
 dt_fechactualizacion = now(),
 flg_replicacion = 0,
 ch_usuario = '" . strip_tags(stripslashes(substr($arrUserIp['sNombreUsuario'], -10))) . "',
 ch_auditorpc = '" . strip_tags(stripslashes($arrUserIp['sIp'])) . "',
 ch_cat_sunat = '" . $cat_sunat . "'
WHERE
 ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iTipoDocumento'])) . "'
 AND ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['sSerieDocumento'])) . "'
 AND ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['iNumeroDocumento'])) . "'
 AND dt_fac_fecha = '" . strip_tags(stripslashes($arrPost['arrComplementarySaleInvoice']['dFechaEmision'])) . "'
 		";

		$arrResponse = array('iStatus' => 2, 'sStatus' => 'success', 'sMessage' => 'success SQL - Complementary', 'sNameFunction' => 'upd_sales_invoice_complementary()');
		$iStatusComplementary = $sqlca->query($sql_complemento);
		if ((int)$iStatusComplementary < 0)
    		$arrResponse = array('iStatus' => $iStatusComplementary, 'sStatus' => 'danger', 'sMessage' => 'Problemas al modificar datos complementarios', 'sNameFunction' => 'upd_sales_invoice_complementary()');
    	return $arrResponse;
	}

	function cancel_or_delete_sales_invoice($arrPost){ //ELIMINAR O ANULAR
		error_log('cancel_or_delete_sales_invoice');
		error_log(json_encode($arrPost));

		global $sqlca;

		$sNombreAccion = ($arrPost['sAction'] == 'anular' ? 'anulado' : 'eliminado');
		$arrResponse = array('iStatus' => 1, 'sStatus' => 'success', 'sMessage' => 'Documento ' . $sNombreAccion, 'sNameFunction' => 'cancel_or_delete_sales_invoice()');

		$iTipoDocumento = strip_tags(stripslashes($arrPost['iTipoDocumento']));
		$sSerieDocumento = strip_tags(stripslashes($arrPost['sSerieDocumento']));
		$iNumeroDocumento = strip_tags(stripslashes($arrPost['iNumeroDocumento']));
		$iIdCliente = strip_tags(stripslashes($arrPost['iIdCliente']));
		$iNumeroLiquidacion = strip_tags(stripslashes($arrPost['iNumeroLiquidacion']));
		$iTipoFormaVales = strip_tags(stripslashes($arrPost['iTipoFormaVales']));//1 = Sin soltar vales y 2 = Soltando vales
		$sTipoAccionFuncionDB = ($arrPost['sAction'] == 'anular' ? 'ANULACION' : 'ELIMINACION');

		$primary_key_factura_cabecera = $iTipoDocumento . $sSerieDocumento . $iNumeroDocumento . $iIdCliente;

		if (!empty($iNumeroLiquidacion)) {
			// start the transaction
			$this->begin_transaction();

			if ( $iTipoFormaVales == 2 ) {// 2 = Soltando vales //ELIMINAR O ANULAR SOLTANDO VALES
				// upd - credit vouchers - tabla val_ta_cabecera
				$arrVales = $this->upd_credit_vouchers($arrPost);
				if ( $arrVales['sStatus'] == 'danger' ) {
					$arrResponse = array('iStatus' => -1, 'sStatus' => 'danger', 'sMessage' => 'Problemas al actualizar liquidación de vales', 'sNameFunction' => 'cancel_or_delete_sales_invoice()');
					$this->rollback_transaction();
				}

				// delete - credit vouchers - tabla val_ta_complemento_documento
				$arrValesFacturas = $this->delete_credit_vouchers_with_sales_invoice($arrPost);
				if ( $arrValesFacturas['sStatus'] == 'danger' ) {
					$arrResponse = array('iStatus' => -2, 'sStatus' => 'danger', 'sMessage' => 'Problemas al eliminar liquidación de vales', 'sNameFunction' => 'cancel_or_delete_sales_invoice()');
					$this->rollback_transaction();
				}
			}
			// die();

			$sStatus = $sqlca->functionDB("ventas_fn_eliminacion_documentos('" . $iTipoDocumento . "', '" . $sSerieDocumento . "', '" . $iNumeroDocumento . "', '" . $iIdCliente . "', '" . $iTipoFormaVales . "', '" . $sTipoAccionFuncionDB . "')");
			if (empty($sStatus)) {
				$arrResponse = array('iStatus' => -3, 'sStatus' => 'danger', 'sMessage' => 'El documento presenta cancelaciones / eliminaciones en cuentas por cobrar', 'sNameFunction' => 'cancel_or_delete_sales_invoice()');
				$this->rollback_transaction();
			}

			// Cambiar a estado ANULADO
			if ($arrPost['sAction'] == 'anular') {
				$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 2 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '" . $primary_key_factura_cabecera . "'";
				if($sqlca->query($sql) < 0) {
					$arrResponse = array('iStatus' => -4, 'sStatus' => 'danger', 'sMessage' => 'Problemas al cambiar a estado ANULADO', 'sNameFunction' => 'cancel_or_delete_sales_invoice()');
					$this->rollback_transaction();
				}
			}

		  	// commit the changes
			$this->commit_transaction();
		} else {
			// start the transaction
			$this->begin_transaction();
			$sStatus = $sqlca->functionDB("ventas_fn_eliminacion_documentos('" . $iTipoDocumento . "', '" . $sSerieDocumento . "', '" . $iNumeroDocumento . "', '" . $iIdCliente . "', '" . $iTipoFormaVales . "', '" . $sTipoAccionFuncionDB . "')");
			if (empty($sStatus)) {
				$arrResponse = array('iStatus' => -1, 'sStatus' => 'danger', 'sMessage' => 'El documento presenta cancelaciones / eliminaciones en cuentas por cobrar', 'sNameFunction' => 'cancel_or_delete_sales_invoice()');
				$this->rollback_transaction();
			}

			// Cambiar a estado ANULADO
			if ($arrPost['sAction'] == 'anular') {
				$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 2 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '" . $primary_key_factura_cabecera . "'";
				if($sqlca->query($sql) < 0) {
					$arrResponse = array('iStatus' => -2, 'sStatus' => 'danger', 'sMessage' => 'Problemas al cambiar a estado ANULADO', 'sNameFunction' => 'cancel_or_delete_sales_invoice()');
					$this->rollback_transaction();
				}
			}

		  	// commit the changes
			$this->commit_transaction();
		}// /. Verificacion de soltar vales (S / N)
		return $arrResponse;
	}

	function upd_credit_vouchers($arrPost){ //Actualiza ch_liquidacion a NULL en val_ta_cabecera
		global $sqlca;

		$sql_vales = "
UPDATE
 val_ta_cabecera
SET
 ch_liquidacion = NULL
WHERE
 ch_documento||''||DATE(dt_fecha) IN(
 SELECT
  ch_numeval||''||DATE(dt_fecha)
 FROM
  val_ta_complemento_documento
 WHERE
  ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrPost['iTipoDocumento'])) . "'
  AND ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrPost['sSerieDocumento'])) . "'
  AND ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrPost['iNumeroDocumento'])) . "'
  AND ch_cliente = '" . strip_tags(stripslashes($arrPost['iIdCliente'])) . "'
  AND ch_liquidacion = '" . strip_tags(stripslashes($arrPost['iNumeroLiquidacion'])) . "'
 );
		";
		error_log("upd_credit_vouchers");
		error_log($sql_vales);

		$arrResponse = array('iStatus' => 2, 'sStatus' => 'success', 'sMessage' => 'success SQL - Complementary', 'sNameFunction' => 'upd_credit_vouchers()');
		$iStatus = $sqlca->query($sql_vales);
		if ((int)$iStatus < 0)
    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al soltar lo(s) val(es)', 'sNameFunction' => 'upd_credit_vouchers()');
    	return $arrResponse;
	}

	function delete_credit_vouchers_with_sales_invoice($arrPost){ //Elimina registro de val_ta_complemento_documento
		global $sqlca;

		$sql_vales_facturas = "
DELETE FROM
 val_ta_complemento_documento
WHERE
  ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrPost['iTipoDocumento'])) . "'
  AND ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrPost['sSerieDocumento'])) . "'
  AND ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrPost['iNumeroDocumento'])) . "'
  AND ch_cliente = '" . strip_tags(stripslashes($arrPost['iIdCliente'])) . "'
  AND ch_liquidacion = '" . strip_tags(stripslashes($arrPost['iNumeroLiquidacion'])) . "';
		";
		error_log("delete_credit_vouchers_with_sales_invoice");
		error_log($sql_vales_facturas);

		$arrResponse = array('iStatus' => 3, 'sStatus' => 'success', 'sMessage' => 'success SQL - Complementary', 'sNameFunction' => 'delete_credit_vouchers_with_sales_invoice()');
		$iStatus = $sqlca->query($sql_vales_facturas);
		if ((int)$iStatus < 0)
    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al soltar lo(s) val(es) con relación de factura(s)', 'sNameFunction' => 'delete_credit_vouchers_with_sales_invoice()');
    	return $arrResponse;
	}

	/**
	* Datos para Facturación Electrónica y Representación Impresa
	*/
	function get_company($arrGet){
    	global $sqlca;

    	$iStatus = $sqlca->query("
SELECT
 EMPRE.ruc,
 EMPRE.razsocial,
 EMPRE.ch_direccion,
 EMPRE.ebiurl,
 EMPRE.ebiauth,
 ALMA.ch_nombre_almacen AS no_almacen,
 ALMA.ch_direccion_almacen
FROM
 inv_ta_almacenes AS ALMA
 JOIN int_ta_sucursales AS EMPRE
  USING ( ch_sucursal )
WHERE
 EMPRE.ebikey IS NOT NULL AND EMPRE.ebikey != ''
 AND ALMA.ch_clase_almacen = '1'
 AND ALMA.ch_almacen = '" . strip_tags(stripslashes($arrGet['iCodigoAlmacen'])) . "'
LIMIT 1
    	");

    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al obtener datos de la empresa', 'sNameFunction' => 'get_company_data()');

    	if ( (int)$iStatus > 0 ){
			  $row = $sqlca->fetchRow();

			  $arrEmpresaDireccion = explode('|', trim($row['ch_direccion']));
			  $arrEstablecimientoDireccion = explode('|', trim($row['ch_direccion_almacen']));

			  $arrRow = array(
					'iEmpresaRuc' => trim($row['ruc']),
					'sEmpresaRazsocial' => trim($row['razsocial']),
					'sEmpresaURL' => trim($row['ebiurl']),
					'sEmpresaAutorizacion' => trim($row['ebiauth']),
					'sEmpresaDireccion' => $arrEmpresaDireccion[1] . ' ' . $arrEmpresaDireccion[2],
					'sEmpresaDistrito' => $arrEmpresaDireccion[3],
					'sEmpresaProvincia' => $arrEmpresaDireccion[4],
					'sEmpresaDepartamento' => $arrEmpresaDireccion[5],
					'sEstablecimientoNombre' => trim($row['no_almacen']),
					'iEstablecimientoCodigo' => trim($arrEstablecimientoDireccion[0]),
					'sEstablecimientoDireccion' => $arrEstablecimientoDireccion[1],
					'sEstablecimientoZona' => $arrEstablecimientoDireccion[2],
					'sEstablecimientoDistrito' => $arrEstablecimientoDireccion[3],
					'sEstablecimientoProvincia' => $arrEstablecimientoDireccion[4],
					'sEstablecimientoDepartamento' => $arrEstablecimientoDireccion[5],
				);
				$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Datos de empresa encontrada (almacén)', 'arrRow' => $arrRow);
    	} else if ( $iStatus == 0 ) {//Si no existe por almacén, buscar por empresa
      	$iStatus = $sqlca->query("
SELECT DISTINCT
 EMPRE.ruc,
 EMPRE.razsocial,
 EMPRE.ch_direccion,
 EMPRE.ebiurl,
 EMPRE.ebiauth,
 ALMA.ch_nombre_almacen AS no_almacen,
 ALMA.ch_direccion_almacen
FROM
 inv_ta_almacenes AS ALMA
 JOIN int_ta_sucursales AS EMPRE
  USING ( ch_sucursal )
WHERE
 EMPRE.ebikey IS NOT NULL AND EMPRE.ebikey != ''
 AND ALMA.ch_clase_almacen = '1'
LIMIT 1
      		");

     		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al obtener datos de la empresa (2)', 'sNameFunction' => 'get_company_data()');

			if ( (int)$iStatus > 0 ){
				$row = $sqlca->fetchRow();

				$arrEmpresaDireccion   = explode('|', trim($row['ch_direccion']));
				$arrEstablecimientoDireccion   = explode('|', trim($row['ch_direccion_almacen']));

				$arrRowData = array(
					'iEmpresaRuc' => trim($row['ruc']),
					'sEmpresaRazsocial' => trim($row['razsocial']),
					'sEmpresaURL' => trim($row['ebiurl']),
					'sEmpresaAutorizacion' => trim($row['ebiauth']),
					'sEmpresaDireccion' => $arrEmpresaDireccion[1] . ' ' . $arrEmpresaDireccion[2],
					'sEmpresaDistrito' => $arrEmpresaDireccion[3],
					'sEmpresaProvincia' => $arrEmpresaDireccion[4],
					'sEmpresaDepartamento' => $arrEmpresaDireccion[5],
					'sEstablecimientoNombre' => trim($row['no_almacen']),
		        	'iEstablecimientoCodigo' => trim($arrEstablecimientoDireccion[0]),
					'sEstablecimientoDireccion' => $arrEstablecimientoDireccion[1],
					'sEstablecimientoZona' => $arrEstablecimientoDireccion[2],
					'sEstablecimientoDistrito' => $arrEstablecimientoDireccion[3],
					'sEstablecimientoProvincia' => $arrEstablecimientoDireccion[4],
					'sEstablecimientoDepartamento' => $arrEstablecimientoDireccion[5],
				);

        		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Datos de empresa encontrada', 'arrRow' => $arrRow);
      		}
    	}
    	return $arrResponse;
  	}

  	function get_header($arrGet){
   		global $sqlca;

		$sql = "
SELECT
 VC.ch_almacen AS nu_codigo_almacen,
 VC.ch_fac_tipodocumento AS _nu_tipo_documento, --_nu_tipo_documento
 TDOCU.tab_car_03 AS nu_tipo_documento, --nu_tipo_documento
 TDOCU.tab_descripcion AS no_tipo_documento,
 VC.ch_fac_seriedocumento AS no_serie_documento,
 VC.ch_fac_numerodocumento AS nu_numero_documento,
 TMONE.tab_car_04 AS nu_codigo_moneda_sunat,
 TMONE.tab_descripcion AS no_nombre_moneda,
 VC.dt_fac_fecha AS fe_emision,
 ROUND(VC.nu_fac_valorbruto, 2) AS ss_valor_venta,
 ROUND(VC.nu_fac_descuento1, 2) AS ss_descuento,
 (CASE WHEN TRIM(VC.ch_fac_tiporecargo2) != '' THEN 0.00 ELSE ROUND(VC.nu_fac_impuesto1, 2) END) AS ss_impuesto,
 ROUND(VC.nu_fac_valortotal, 2) AS ss_total,
 ROUND(VC.nu_fac_recargo1, 2) AS ss_gratuita,
 TRIM(CLI.cli_codigo) AS nu_codigo_cliente,
 TRIM(CLI.cli_ruc) AS nu_documento_identidad_cliente,
 CLI.cli_razsocial AS no_razsocial_cliente,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[2] AS no_serie_documento_referencia,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[1] AS nu_numero_documento_referencia,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AS nu_tipo_documento_referencia,
 TDOCUREFE.tab_car_03 AS nu_tipo_documento_referencia_sunat,
 TDOCUREFE.tab_descripcion AS no_tipo_documento_referencia_sunat,
 VCOM.ch_fac_observacion3 AS fe_emision_referencia,
 VCOM.ch_fac_observacion1 AS txt_observaciones_referencia,
 VC.ch_fac_anulado AS no_anulado,
 FPAGO.tab_descripcion AS no_nombre_forma_pago,
 FPAGO.tab_car_04 AS nu_codigo_forma_pago_sunat,
 VC.fe_vencimiento,
 (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[1] AS nu_numero_cuenta_detraccion,
 (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[2] AS ss_importe_detraccion,
 (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[3] AS nu_porcentaje_detraccion,
 (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[4] AS nu_codigo_bienes_servicio_detraccion,
 TRIM(VC.ch_fac_tiporecargo2) AS no_codigo_impuesto, --ch_fac_tiporecargo2
 VC.ch_liquidacion AS nu_liquidacion,
 VC.nu_fac_recargo3 AS nu_estado_documento_sunat,
 TRIM(VCOM.ch_cat_sunat) AS ch_cat_sunat,
 TRIM(VC.nu_tipo_pago) AS nu_tipo_pago,
 (string_to_array(VCOM.nu_fac_complemento_direccion, '*'))[2] AS ss_importe_spot, --Aqui esta o bien la Detraccion o Retencion
 CLI.cli_direccion AS txt_linea_direccion_cliente
FROM
 fac_ta_factura_cabecera AS VC
 LEFT JOIN fac_ta_factura_complemento AS VCOM
  USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_clientes AS CLI
  USING (cli_codigo)
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 JOIN int_tabla_general AS TMONE
  ON (SUBSTRING(TMONE.tab_elemento, 5) = VC.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
 JOIN int_tabla_general AS FPAGO
  ON (SUBSTRING(FPAGO.tab_elemento, 5) = VC.nu_tipo_pago AND FPAGO.tab_tabla ='05' AND FPAGO.tab_elemento != '000000')
 LEFT JOIN int_tabla_general AS TDOCUREFE
  ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
WHERE
 VC.ch_almacen = '" . strip_tags(stripslashes($arrGet['iCodigoAlmacen'])) . "'
 AND VC.dt_fac_fecha = '" . strip_tags(stripslashes($arrGet['dFechaEmision'])) . "'
 AND VC.ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrGet['iTipoDocumento'])) . "'
 AND VC.ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrGet['sSerieDocumento'])) . "'
 AND VC.ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrGet['iNumeroDocumento'])) . "'
 AND VC.cli_codigo = '" . strip_tags(stripslashes($arrGet['iIdCliente'])) . "'
LIMIT 1
		";

    	$iStatus = $sqlca->query($sql);

    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar documento - cabecera (SQL)', 'sNameFunction' => 'get_header()');
    	if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'No se encontró el documento - cabecera', 'arrRow' => 0);
    	else if ((int)$iStatus > 0) {
      		$row = $sqlca->fetchRow();
      		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Documento encontrado - cabecera', 'arrRow' => $row);
      		if ($row['nu_tipo_documento'] == '20' || $row['nu_tipo_documento'] == '11') {// if nota de credito / débito
        		$arrResponse = array('iStatus' => -100, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function verify_reference_sales_invoice_document()');
        		if (
					strlen($row['no_serie_documento_referencia']) >= 3
					&& strlen($row['nu_numero_documento_referencia']) > 6
					&& strlen($row['fe_emision_referencia']) == 10
					&& strlen($row['txt_observaciones']) > 0
        		) {
					$arrCond = array(
						'dFechaEmision' => trim($row['fe_emision_referencia']),
						'iTipoDocumento' => trim($row['nu_tipo_documento_referencia']),
						'sSerieDocumento' => trim($row['no_serie_documento_referencia']),
						'iNumeroDocumento' => trim($row['nu_numero_documento_referencia']),
						'iIdCliente' => trim($row['nu_codigo_cliente']),
						'iRucCliente' => trim($row['nu_documento_identidad_cliente']),
					);
          			$arrResponse = $this->verify_reference_sales_invoice_document($arrCond);
          			if ( $arrResponse["sStatus"] == "warning" )
            			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No existe documento de referencia');
        		}
      		}// ./ if nota de credito / débito
    	}
    	return $arrResponse;
  	}

	function get_detail($arrGet){
    	global $sqlca;

		$iStatus = $sqlca->query("
SELECT
 TRIM(VD.ch_fac_tiporecargo2) AS no_codigo_impuesto_item,
 VD.art_codigo AS nu_codigo_item,
 VD.nu_fac_cantidad AS qt_cantidad,
 UM.tab_car_03 AS nu_codigo_unidad_medida_sunat,
 VD.nu_fac_precio AS ss_precio_venta_item,
 ITEM.art_descripcion AS no_nombre_item,
 VD.nu_fac_importeneto AS ss_subtotal,
 VD.nu_fac_impuesto1 AS ss_impuesto,
 VD.nu_fac_descuento1 AS ss_descuento,
 VD.nu_fac_valortotal AS ss_total
FROM
 fac_ta_factura_detalle AS VD
 JOIN int_articulos AS ITEM
  USING (art_codigo)
 JOIN int_tabla_general AS UM
  ON (UM.tab_elemento = ITEM.art_unidad AND UM.tab_tabla ='34' AND UM.tab_elemento != '000000')
WHERE
 VD.ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrGet['iTipoDocumento'])) . "'
 AND VD.ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrGet['sSerieDocumento'])) . "'
 AND VD.ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrGet['iNumeroDocumento'])) . "'
 AND VD.cli_codigo = '" . strip_tags(stripslashes($arrGet['iIdCliente'])) . "'
    	");

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar documento - detalle (SQL)', 'sNameFunction' => 'get_detail()');
    	if ($iStatus == 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'No se encontró el documento - detalle', 'arrRow' => 0);
		else if ((int)$iStatus > 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Documento encontrado - detalle', 'arrRow' => $sqlca->fetchAll());
		return $arrResponse;
  	}

	function check_get_plates_generated_settlement_vouchers($arrGet) {
		global $sqlca;

//VTCD.ch_sucursal = '" . strip_tags(stripslashes($arrGet['iCodigoAlmacen'])) . "'
//VTCD.fecha_liquidacion = '" . strip_tags(stripslashes($arrGet['dFechaEmision'])) . "'
		$sql = "
SELECT
 DISTINCT(VTC.ch_placa) AS no_placa
FROM
 val_ta_complemento_documento AS VTCD
 LEFT JOIN val_ta_cabecera AS VTC
  ON (VTC.ch_cliente = VTCD.ch_cliente AND VTC.dt_fecha = VTCD.dt_fecha AND VTC.ch_documento = VTCD.ch_numeval)
WHERE
 VTCD.ch_fac_tipodocumento = '" . strip_tags(stripslashes($arrGet['iTipoDocumento'])) . "'
 AND VTCD.ch_fac_seriedocumento = '" . strip_tags(stripslashes($arrGet['sSerieDocumento'])) . "'
 AND VTCD.ch_fac_numerodocumento = '" . strip_tags(stripslashes($arrGet['iNumeroDocumento'])) . "'
 AND VTCD.ch_cliente = '" . strip_tags(stripslashes($arrGet['iIdCliente'])) . "'
 AND VTCD.ch_liquidacion = '" . strip_tags(stripslashes($arrGet['iNumeroLiquidacion'])) . "'
 AND LENGTH(VTC.ch_placa) > 3;
		";

		error_log($sql);

    	$iStatus = $sqlca->query($sql);
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar impuesto (SQL)', 'sNameFunction' => 'check_get_plates_generated_settlement_vouchers()');
		if ($iStatus == 0){
			return array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No se encontraron placas válidas (sql). Debe considerar las siguientes validaciones: - El primer y último caracter no puede ser (-) - La placa debe de contener desde 4 hasta 8 caracteres');
		} else if ((int)$iStatus > 0){
			$arrPlates = array();
			foreach ($sqlca->fetchAll() as $row) {
				$row['no_placa'] = trim($row['no_placa']);
				if ($row['no_placa'] != '' && substr($row['no_placa'], -1) != '-' && substr($row['no_placa'], 0, 1) != '-' && strlen($row['no_placa']) >= 3) {
					$arrPlates[] = $row['no_placa'];
				}
			}
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No se encontraron placas válidas (if). Debe considerar las siguientes validaciones: - El primer y último caracter no puede ser (-) - La placa debe de contener desde 4 hasta 8 caracteres', 'arrRow' => 0);
			if ( count($arrPlates) > 0 ) {
				$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Placas encontradas', 'arrRow' => $arrPlates);
			}
		}
		return $arrResponse;
	}

	// Hay un posible error, si ebi reponde en ese momento el nuevo estado, no validará si el documento se envió o no.
	// consultar, si debemos consultar a la tabla ebi_queue
	function verify_register_SUNAT($arrVerifyFE){
		global $sqlca;

		$sql = "
SELECT
 *
FROM
 fac_ta_factura_cabecera
WHERE
 nu_fac_recargo3=" . $arrVerifyFE["nu_fac_recargo3"] . "
 AND ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento = '" . $arrVerifyFE["ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento"] . "'
LIMIT 1
      	";

    	$iStatus = $sqlca->query($sql);
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al verificar si el documento fue enviado (ebi)');
    	if ( (int)$iStatus == 0 ) {
	        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'El comprobante no fue enviado (ebi)');
	    } else if ( (int)$iStatus >= 1 ){
	        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Comprobante pendiente de procesamiento');
		}
		return $arrResponse;
    }

	/**
	* Tabla de almacenamiento ebi_queue
	* Campos:
		- _id -> Correlativo de clave primaria
		- created -> Fecha, graba la fecha de creación del registro, fomato ISO
		- taxid -> Cadena, almacena el RUC de la empresa a la cual pertenece el documento a envíar
		- optype:
			Numérico, tipo de operación. Operaciones:
				0: Comprobante en texto estructurado
				1: Anular
				2: Envío XML UBL SUNAT no firmado
				3: Envío XML UBL SUNAT Firmado
				4: Cierre de día
				5: Control de series
			Nota: optype=0 y optype=1 siempre están disponibles. Los demás valores serán habilitados según el tipo de implementación y necesidades particulares del emisor.
		- status -> Numérico, estado del documento. Estados:
			0: Registrado
			1: Enviado
			2: Error
		- callback -> Cadena, procesos internos a ejecutar según tipo de operación
		- content -> Cadena, contenido del documento para SUNAT. Aquí van las líneas anteriormente expuestas.
	*/

	/**
	* Tabla fac_ta_factura_cabecera
	* Campos:
		- nu_fac_recargo3:
			0 = Registrado
			1 = Completado
			2 = Anulado
			3 = Completado Enviado
			4 = Completado Error (No se envió el documento a EBI -> SUNAT)
			5 = Anulado enviado
			6 = Anulado Error
	*/
	function save_document_sunat($arrData){
		global $sqlca;

		// Verificar si existe documento en EBI - Generación
		$sql = "
SELECT
 *
FROM
 ebi_queue
WHERE
 optype=0
 AND taxid='" . $arrData["taxid"] . "'
 AND content LIKE '%" . $arrData["content_like_tipo_serie_numero"] . "%'
LIMIT 1
      	";
		$iStatusSQL = $sqlca->query($sql);

		//Verificar si existe el documento en fac_ta_factura_cabecera - Generacion
		/*$sql = "
SELECT
 *
FROM
 fac_ta_factura_cabecera
WHERE 
 nu_fac_recargo3 IN ('3','2')
 AND ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento LIKE '%" . $arrData["primary_key_factura_cabecera"] . "%'
LIMIT 1
		";
		$iStatusSQL = $sqlca->query($sql);
		error_log( json_encode( $sql ) );*/
		
		// Verificar si existe documento en EBI - Anular
		$sql = "
SELECT
 *
FROM
 ebi_queue
WHERE
 optype=1
 AND taxid='" . $arrData["taxid"] . "'
 AND content LIKE '%" . $arrData["content_like_tipo_serie_numero"] . "%'
LIMIT 1
      	";
		$iStatusAnuladoSQL = $sqlca->query($sql);
		
		//Verificar si existe el documento en fac_ta_factura_cabecera - Anular
		/*$sql = "
SELECT
 *
FROM
 fac_ta_factura_cabecera
WHERE 
 nu_fac_recargo3 IN ('5')
 AND ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento LIKE '%" . $arrData["primary_key_factura_cabecera"] . "%'
LIMIT 1
		";
		$iStatusAnuladoSQL = $sqlca->query($sql);
		error_log( json_encode( $sql ) );*/

		/*** Verificar envio de decimales para precio unitario y cantidad en el detalle de las facturas ***/			
		error_log('*** Etapa 6 ***');
		error_log( json_encode( array( $iStatusSQL, $iStatusAnuladoSQL ) ) );
		// die();
		/***/

		//if ( (int)$iStatusSQL == 0 || $arrData['iEstadoDocumento'] != "3" || $arrData['iEstadoDocumento'] != "5" ) {
		if (
			($iStatusSQL==0 && $iStatusAnuladoSQL==0) ||
			($iStatusSQL==1 && $iStatusAnuladoSQL==0)
		) {
			// start the transaction
			$this->begin_transaction();
			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento enviado satisfactoriamente');

			/*
			Solo entra si tiene los siguientes estados el campo nu_fac_recargo3:
				- 4 = Completado Error
				- 6 = Anulado Error
			*/
			if ( $arrData['iEstadoDocumento'] == "4" || $arrData['iEstadoDocumento'] == "6" ) {
				$sql = "DELETE FROM ebi_queue 
						WHERE       status IN(0,2) 
						AND         taxid = '" . $arrData["taxid"] . "' 
						AND         content LIKE '%" . $arrData["content_like_tipo_serie_numero"] . "%'";
				$iStatusSQL = $sqlca->query($sql);
				if ((int)$iStatusSQL < 0) {
					// cancel the changes
					$this->rollback_transaction();
					$arrResponse = array('sStatus' => 'danger', 'sMessage' => 'problemas al eliminar documento (ebi)');
				}
			}

			$sql = "
INSERT INTO ebi_queue(
 _id,
 created,
 taxid,
 optype,
 status,
 callback,
 content
) VALUES (
 " . $arrData["_id"] . ",
 " . $arrData["created"] . ",
 '" . $arrData["taxid"] . "',
 " . $arrData["optype"] . ",
 " . $arrData["status"] . ",
 '" . $arrData["callback"] . "',
 '" . $arrData["content"] . "'
)
			";

	    	$iStatusSQL = $sqlca->query($sql);
			if ((int)$iStatusSQL < 0){
				// cancel the changes
				$this->rollback_transaction();
				$arrResponse = array('sStatus' => 'danger', 'sMessage' => 'problemas al insertar el formato de documento sunat');
			}

			// optype:
			// 1 = COMPLETADO
			// 2 = ANULADO
			$iStatusDocument = ($arrData["optype"] == 0 ? 1 : 2);
			$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = " . $iStatusDocument . " WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento = '" . $arrData["primary_key_factura_cabecera"] . "'";
			$iStatusSQL = $sqlca->query($sql);
			if( (int)$iStatusSQL < 0) {
				// cancel the changes
				$this->rollback_transaction();
				$arrResponse = array('sStatus' => 'danger', 'sMessage' => 'problemas al actualizar estado documento');
			}

			// commit the changes
			$this->commit_transaction();
			return $arrResponse;
		} else if ( (int)$iStatusSQL < 0 ){
			$this->rollback_transaction();
	    	return array('sStatus' => 'error', 'sMessage' => 'Problemas al verificar comprobante (ebi)', 'sMessageBD' => $sqlca->get_error());
		} else if ( (int)$iStatusSQL >= 1 ){
			$this->rollback_transaction();
			$sMessage='Comprobante pendiente de procesamiento';
			if ( $iStatusAnuladoSQL > 0 ) {
				$sMessage='Comprobante Anulado';
			}
	    	return array('sStatus' => 'warning', 'sMessage' => $sMessage, 'SQL' => $sql);
		}
	}

	function text_clean_bd($str) {
		return str_replace(array("'"), array("''"), $str);
	}

	function text_clean_fe($str) {
		return str_replace(array("'"), array(''), $str);
	}

	/* Limipiar Campo Observación para FE */
	function text_clean_bd_fe($str) {
        return str_replace(array("\r","\n","|"), array('','. ',''), $str);
	}
}
