<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

include_once("/sistemaweb/include/dbsqlca2.php");
global $sqlca;

$sql = "
	SELECT
		p1.par_valor AS ip,
		p3.par_valor AS user,
		p4.par_valor AS password,
		p2.par_valor AS dbname
	FROM
		int_parametros p1
		LEFT JOIN int_parametros p2 ON p2.par_nombre = 'central_db'
		LEFT JOIN int_parametros p3 ON p3.par_nombre = 'central_user'
		LEFT JOIN int_parametros p4 ON p4.par_nombre = 'central_password'
	WHERE
		p1.par_nombre = 'central_ip';
";

if ($sqlca->query($sql) <= 0)
	return FALSE;

if($sqlca->numrows()==1){
	$data 			= $sqlca->fetchRow();
	if ($_SERVER['SERVER_ADDR'] == $data['ip']) {
		$data['ip'] = 'localhost';
	}
	$sqlca_central 	= new pgsqlDB($data['ip'], $data['user'], $data['password'], $data['dbname']);
}

class ModelRegistroVentasCental extends Model {
	
	function ConnectionServerCentral(){
		global $sqlca;
	
		$sql = "
			SELECT
				p1.par_valor AS ip,
				p3.par_valor AS user,
				p4.par_valor AS password,
				p2.par_valor AS dbname
			FROM
				int_parametros p1
				LEFT JOIN int_parametros p2 ON p2.par_nombre = 'central_db'
				LEFT JOIN int_parametros p3 ON p3.par_nombre = 'central_user'
				LEFT JOIN int_parametros p4 ON p4.par_nombre = 'central_password'
			WHERE
				p1.par_nombre = 'central_ip';
		";

		if ($sqlca->query($sql) <= 0)
			return FALSE;

		if($sqlca->numrows()==1){
			$data 	= $sqlca->fetchRow();
			if ($_SERVER['SERVER_ADDR'] == $data['ip']) {
				$data['ip'] = 'localhost';
			}

			$sqlca_central = new pgsqlDB($data['ip'], $data['user'], $data['password'], $data['dbname']);

			if(empty($sqlca_central->error)) {//Porque vacio, porque si la conexion fue satisfactoria no me mostrará ningun mensaje de error
				return TRUE;
			} else {
				return $sqlca_central->error;
			}
		}else{
			return FALSE;// No existe
		}
	}

	function GetOrganizaciones(){
		global $sqlca_central;

		try {

			$sql = "
			SELECT
				c_org_id AS Nu_Id_Org,
				name AS No_Organizacion
			FROM
				c_org
			WHERE
				isactive = 1
			ORDER BY
				name;
			";

			$_query = $sqlca_central->query($sql);
			if($_query <= 0){
				throw new Exception("Error Get Organization");
			}

			while($rows = $sqlca_central->fetchRow()){
				$data[] = $rows;
			}

			return $data;
		}catch(Exception $e){
			throw $e;
		}
	}

	function ReporteRegisroVentasCentral($data){
		global $sqlca_central;

		$cboalmacen 	= $data['cboalmacen'];
		$cbotv 			= $data['cbotv'];
		$txtnofechaini 	= $data['txtnofechaini'];
		$txtnofechafin 	= $data['txtnofechafin'];
		$rdnotipo 		= $data['rdnotipo'];
		$condorg 		= NULL;
		$condtv 		= NULL;//1 = IS TICKET Y 0 = MANUAL COMORA

		//FILTRO POR ALMACEN
		if($cboalmacen != 'T')
			$condorg = "AND CAB.c_org_id = ".$cboalmacen."";

		//FILTRO POR TIPO DE MOVIENTO
		if($cbotv == '0')//COMPRA
			$condtv = "AND CAB.issale = ".$cbotv."";
		else if($cbotv == '1')//TICKET
			$condtv = "AND CAB.issale = ".$cbotv."";
		else
			$condtv = "";

		if($rdnotipo == 'D'){
			if($cbotv == '1'){
				$colum_fe_emision 		= "TO_CHAR(FIRST(CAB.updated), 'DD/MM/YYYY HH24:MI:SS') AS fe_emision,";
				$colum_tipo_movimiento 	= "'TICKET' AS no_tipo_movimiento,";
				$colum_tipo_documento 	= "'12' AS no_tipo_documento,";
				$colum_documentserial 	= "CAB.documentserial AS no_serie_documento,";
				$colum_documentno 		= "CAB.documentno AS nu_documento,";
				$colum_cli_ruc 			= "FIRST(CLI.value) AS nu_ruc,";
				$colum_cli_nombre 		= "FIRST(CLI.name) AS no_cliente,";
				$column_nu_bi 			= "ROUND(SUM(DET.linetotal / 1.18), 2) AS nu_bi,";
				$column_nu_igv 			= "ROUND(SUM(DET.linetotal - (DET.linetotal / 1.18)), 2) AS nu_igv,";
				$column_nu_total 		= "ROUND(SUM(DET.linetotal), 2) AS nu_total";
			}elseif($cbotv == '0'){
				$colum_fe_emision 		= "TO_CHAR(FIRST(CAB.updated), 'DD/MM/YYYY HH24:MI:SS') AS fe_emision,";
				$colum_tipo_movimiento 	= "'COMPRA' AS no_tipo_movimiento,";
				$colum_tipo_documento 	= "FIRST(TD.nu_doctype_sunat) AS no_tipo_documento,";
				$colum_documentserial 	= "CAB.documentserial AS no_serie_documento,";
				$colum_documentno 		= "CAB.documentno AS nu_documento,";
				$colum_cli_ruc 			= "FIRST(CLI.value) AS nu_ruc,";
				$colum_cli_nombre 		= "FIRST(CLI.name) AS no_cliente,";
				$column_nu_bi 			= "ROUND(SUM(DET.linetotal), 2) AS nu_bi,";
				$column_nu_igv 			= "ROUND(SUM(DET.linetotal * 1.18) - SUM(DET.linetotal), 2) AS nu_igv,";
				$column_nu_total 		= "ROUND(SUM(DET.linetotal * 1.18), 2) AS nu_total";
			}else{
				$colum_fe_emision 		= "TO_CHAR(FIRST(CAB.updated), 'DD/MM/YYYY HH24:MI:SS') AS fe_emision,";
				$colum_tipo_movimiento 	= "(CASE WHEN CAB.issale = 1 THEN 'TICKET' ELSE 'COMPRA' END) AS no_tipo_movimiento,";
				$colum_tipo_documento 	= "(CASE WHEN CAB.issale = 1 THEN '12' ELSE FIRST(TD.nu_doctype_sunat) END) AS no_tipo_documento,";
				$colum_documentserial 	= "CAB.documentserial AS no_serie_documento,";
				$colum_documentno 		= "CAB.documentno AS nu_documento,";
				$colum_cli_ruc 			= "FIRST(CLI.value) AS nu_ruc,";
				$colum_cli_nombre 		= "FIRST(CLI.name) AS no_cliente,";
				$column_nu_bi 			= "(CASE WHEN CAB.issale = 1 THEN ROUND(SUM(DET.linetotal / 1.18), 2) ELSE ROUND(SUM(DET.linetotal),2) END) AS nu_bi,";
				$column_nu_igv 			= "(CASE WHEN CAB.issale = 1 THEN ROUND(SUM(DET.linetotal - (DET.linetotal / 1.18)), 2) ELSE ROUND(SUM(DET.linetotal * 1.18) - SUM(DET.linetotal), 2) END) AS nu_igv,";
				$column_nu_total 		= "(CASE WHEN CAB.issale = 1 THEN ROUND(SUM(DET.linetotal), 2) ELSE ROUND(SUM(DET.linetotal * 1.18), 2) END) AS nu_total";
			}

			$inner_join_c_doctype 	= "INNER JOIN c_doctype AS TD ON (TD.c_doctype_id = CAB.c_doctype_id)";
			$inner_join_c_bpartner 	= "INNER JOIN c_bpartner AS CLI ON (CLI.c_bpartner_id = CAB.c_bpartner_id)";

			$group_fe_emision 		= "";
			$group_issale 			= "CAB.issale,";
			$group_c_doctype_id 	= "CAB.c_doctype_id,";
			$group_documentserial 	= "CAB.documentserial,";
			$group_documentno 		= "CAB.documentno";

			$order_fe_emision 		= "FIRST(CAB.created),";
			$order_issale 			= "CAB.issale,";
			$order_c_doctype_id 	= "CAB.c_doctype_id,";
			$order_documentno 		= "CAB.documentno";

		} else if ($rdnotipo == 'RD'){//Resumen Diario

			$colum_fe_emision 		= "TO_CHAR(CAB.created, 'DD/MM/YYYY') AS fe_emision,";
			$colum_tipo_movimiento 	= "";
			$colum_tipo_documento 	= "";
			$colum_documentserial 	= "";
			$colum_documentno 		= "";
			$colum_cli_ruc 			= "";
			$colum_cli_nombre 		= "";
			$column_nu_bi 			= "ROUND(SUM(DET.linetotal / 1.18), 2) AS nu_bi,";
			$column_nu_igv 			= "ROUND(SUM(DET.linetotal - (DET.linetotal / 1.18)), 2) AS nu_igv,";
			$column_nu_total 		= "ROUND(SUM(DET.linetotal),2) AS nu_total";

			$inner_join_c_doctype 	= "";
			$inner_join_c_bpartner 	= "";

			$group_fe_emision 		= "CAB.created";
			$group_issale 			= "";
			$group_c_doctype_id 	= "";
			$group_documentserial 	= "";
			$group_documentno 		= "";

			$order_fe_emision 		= "CAB.created";
			$order_issale 			= "";
			$order_c_doctype_id 	= "";
			$order_documentno 		= "";

		} else if ($rdnotipo == 'RM'){//Resumen Mensual

			$colum_fe_emision 		= "TO_CHAR(CAB.created, 'MM/YYYY') AS fe_emision,";
			$colum_tipo_movimiento 	= "";
			$colum_tipo_documento 	= "";
			$colum_documentserial 	= "";
			$colum_documentno 		= "";
			$colum_cli_ruc 			= "";
			$colum_cli_nombre 		= "";
			$column_nu_bi 			= "ROUND(SUM(DET.linetotal / 1.18), 2) AS nu_bi,";
			$column_nu_igv 			= "ROUND(SUM(DET.linetotal - (DET.linetotal / 1.18)), 2) AS nu_igv,";
			$column_nu_total 		= "ROUND(SUM(DET.linetotal),2) AS nu_total";

			$inner_join_c_doctype 	= "";
			$inner_join_c_bpartner 	= "";

			$group_fe_emision 		= "TO_CHAR(CAB.created, 'MM/YYYY')";
			$group_issale 			= "";
			$group_c_doctype_id 	= "";
			$group_documentserial 	= "";
			$group_documentno 		= "";

			$order_fe_emision 		= "TO_CHAR(CAB.created, 'MM/YYYY')";
			$order_issale 			= "";
			$order_c_doctype_id 	= "";
			$order_documentno 		= "";

		} else if ($rdnotipo == 'RA'){//Resumen Anual

			$colum_fe_emision 		= "TO_CHAR(CAB.created, 'YYYY') AS fe_emision,";
			$colum_tipo_movimiento 	= "";
			$colum_tipo_documento 	= "";
			$colum_documentserial 	= "";
			$colum_documentno 		= "";
			$colum_cli_ruc 			= "";
			$colum_cli_nombre 		= "";
			$column_nu_bi 			= "ROUND(SUM(DET.linetotal / 1.18), 2) AS nu_bi,";
			$column_nu_igv 			= "ROUND(SUM(DET.linetotal - (DET.linetotal / 1.18)), 2) AS nu_igv,";
			$column_nu_total 		= "ROUND(SUM(DET.linetotal),2) AS nu_total";

			$inner_join_c_doctype 	= "";
			$inner_join_c_bpartner 	= "";

			$group_fe_emision 		= "TO_CHAR(CAB.created, 'YYYY')";
			$group_issale 			= "";
			$group_c_doctype_id 	= "";
			$group_documentserial 	= "";
			$group_documentno 		= "";

			$order_fe_emision 		= "TO_CHAR(CAB.created, 'YYYY')";
			$order_issale 			= "";
			$order_c_doctype_id 	= "";
			$order_documentno 		= "";

		}

		try {

			$registros = array();

			$sql = "
				SELECT
					CAB.c_org_id AS nu_organizacion,
					FIRST(ORG.name) AS no_orgarnizacion,
					".$colum_fe_emision."
					".$colum_tipo_movimiento."
					".$colum_tipo_documento."
					".$colum_documentserial."
					".$colum_documentno."
					".$colum_cli_ruc."
					".$colum_cli_nombre."
					".$column_nu_bi."
					".$column_nu_igv."
					".$column_nu_total."
				FROM
					c_invoiceheader AS CAB
					INNER JOIN c_invoicedetail AS DET ON (DET.c_invoiceheader_id = CAB.c_invoiceheader_id)
					INNER JOIN c_org AS ORG ON (ORG.c_org_id = CAB.c_org_id)
					".$inner_join_c_doctype."
					".$inner_join_c_bpartner."
				WHERE
					CAB.created::DATE BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
					".$condtv."
					".$condorg."
				GROUP BY
					CAB.c_org_id,
					".$group_fe_emision."
					".$group_issale."
					".$group_c_doctype_id."
					".$group_documentserial."
					".$group_documentno."
				ORDER BY
					CAB.c_org_id,
					".$order_fe_emision."
					".$order_issale."
					".$order_c_doctype_id."
					".$order_documentno."
					;
			";

/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/

			if ($sqlca_central->query($sql) <= 0) {
               	throw new Exception("No se encontro ningun registro");
			}
       
			while ($reg = $sqlca_central->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}


	function ReporteRegisroVentasCentralizado($data) {

		global $sqlca_central;
		$result = array();

		$cboalmacen 	= $data['cboalmacen'];
		$txtnofechaini 	= $data['txtnofechaini'];
		$txtnofechafin 	= $data['txtnofechafin'];
		$condorg 		= NULL;

		if($cboalmacen != 'T') {
			//$condorg = "AND CAB.c_org_id = ".$cboalmacen."";
			$condorg = "AND org.c_org_id = ".$cboalmacen."";
		}

		try {

			$registros = array();

			//$sql = "SELECT to_char(h1.created, 'dd/mm/yyyy') AS to_char, SUM(d1.quantity) AS cantidad,	SUM (d1.linetotal) AS importe,	p1. NAME,	p1.VALUE	,  c_org.NAME AS nombre,	c_org.c_org_id FROM c_invoiceheader h1 JOIN c_invoicedetail d1 ON h1.c_invoiceheader_id = d1.c_invoiceheader_id  JOIN c_product p1 ON d1.c_product_id = p1.c_product_id JOIN c_org ON h1.c_org_id = c_org.c_org_id WHERE h1.created BETWEEN '$txtnofechaini' AND '$txtnofechafin' AND h1.issale = 1 AND h1.status = 0 $condorg GROUP BY 	p1.VALUE, 	p1. NAME,  	c_org.NAME, 	h1.issale, to_char(h1.created, 'dd/mm/yyyy'), c_org.c_org_id ORDER BY 	to_char(h1.created, 'dd/mm/yyyy');";


			//$sql = "SELECT _general. NAME AS nombre, _general.c_org_id, GLP.cantidad as glp_cantidad, GLP.importe as glp_importe, LIQUIDO.cantidad as liquido_cantidad, LIQUIDO.importe as liquido_importe, MARKET.cantidad as market_cantidad, MARKET.importe as market_importe FROM ( SELECT org.name, org.c_org_id FROM c_org org WHERE org.isactive = 1 $condorg ) _general LEFT JOIN ( SELECT SUM (invoiceDetail.quantity) AS cantidad, SUM (invoiceDetail.linetotal) AS importe, org.c_org_id from c_invoiceheader invoiceHeader JOIN c_invoicedetail invoiceDetail ON invoiceHeader.c_invoiceheader_id = invoiceDetail.c_invoiceheader_id JOIN c_product product ON invoiceDetail.c_product_id = product.c_product_id JOIN c_org org ON invoiceHeader.c_org_id = org.c_org_id WHERE invoiceHeader.created BETWEEN '$txtnofechaini' AND '$txtnofechafin' AND invoiceHeader.issale IN(0, 1) $condorg AND product.value IN ('11620307') GROUP BY to_char(invoiceHeader.created, 'dd/mm/yyyy'), org.c_org_id ORDER BY to_char(invoiceHeader.created, 'dd/mm/yyyy') ) GLP on _general.c_org_id = GLP.c_org_id LEFT JOIN ( SELECT SUM (invoiceDetail.quantity) AS cantidad, SUM (invoiceDetail.linetotal) AS importe, org.c_org_id from c_invoiceheader invoiceHeader JOIN c_invoicedetail invoiceDetail ON invoiceHeader.c_invoiceheader_id = invoiceDetail.c_invoiceheader_id JOIN c_product product ON invoiceDetail.c_product_id = product.c_product_id JOIN c_org org ON invoiceHeader.c_org_id = org.c_org_id WHERE invoiceHeader.created BETWEEN '$txtnofechaini' AND '$txtnofechafin' AND invoiceHeader.issale IN(0, 1) $condorg AND product.value IN ('11620301','11620302','11620303','11620304','11620305') GROUP BY to_char(invoiceHeader.created, 'dd/mm/yyyy'), org.c_org_id ORDER BY to_char(invoiceHeader.created, 'dd/mm/yyyy') ) LIQUIDO on _general.c_org_id = LIQUIDO.c_org_id LEFT JOIN ( SELECT SUM (invoiceDetail.quantity) AS cantidad, SUM (invoiceDetail.linetotal) AS importe, org.c_org_id from c_invoiceheader invoiceHeader JOIN c_invoicedetail invoiceDetail ON invoiceHeader.c_invoiceheader_id = invoiceDetail.c_invoiceheader_id JOIN c_product product ON invoiceDetail.c_product_id = product.c_product_id JOIN c_org org ON invoiceHeader.c_org_id = org.c_org_id WHERE invoiceHeader.created BETWEEN '$txtnofechaini' AND '$txtnofechafin' AND invoiceHeader.issale IN(0, 1) $condorg AND product.value NOT IN ('11620307','11620308','11620301','11620302','11620303','11620304','11620305') GROUP BY to_char(invoiceHeader.created, 'dd/mm/yyyy'), org.c_org_id ORDER BY to_char(invoiceHeader.created, 'dd/mm/yyyy') ) MARKET on _general.c_org_id = MARKET.c_org_id ; ";

			$sql = "SELECT
				_general. NAME AS nombre,
				_general.c_org_id,
				GLP.cantidad AS glp_cantidad,
				GLP.importe AS glp_importe,
				LIQUIDO.cantidad AS liquido_cantidad,
				LIQUIDO.importe AS liquido_importe,
				MARKET.cantidad AS market_cantidad,
				MARKET.importe AS market_importe,
				_general.systemdate AS systemdate
			FROM
				(
					SELECT
						org. NAME,
						org.c_org_id,
						daycontrol.systemdate
					FROM
						c_org org
					LEFT JOIN c_org orgm ON (
						org.c_org_id :: VARCHAR = orgm.VALUE

					)
					LEFT JOIN c_daycontrol daycontrol ON (
						daycontrol.c_org_id = org.c_org_id
						OR daycontrol.c_org_id = orgm.c_org_id
					)
					WHERE
						org.isactive = 1 $condorg
					AND daycontrol.systemdate BETWEEN '$txtnofechaini'
					AND '$txtnofechafin'
				) _general
			LEFT JOIN (
				SELECT
					org.c_org_id,
					SUM (invoiceDetail.quantity) AS cantidad,
					SUM (invoiceDetail.linetotal) AS importe,
					invoiceHeader.created
				FROM
					c_invoiceheader invoiceHeader
				JOIN c_invoicedetail invoiceDetail ON (
					invoiceHeader.c_invoiceheader_id = invoiceDetail.c_invoiceheader_id
				)
				LEFT JOIN c_product product ON (
					invoiceDetail.c_product_id = product.c_product_id
				)
				JOIN c_org org ON (
					invoiceHeader.c_org_id = org.c_org_id
				)
				WHERE
					invoiceHeader.created BETWEEN '$txtnofechaini'
				AND '$txtnofechafin'
				AND invoiceHeader.issale = 1
				AND product.
				VALUE
					IN ('11620307')
				GROUP BY
					org.c_org_id,
					invoiceHeader.created
			) GLP ON (
				_general.c_org_id = GLP.c_org_id AND GLP.created in (_general.systemdate)
			)
			LEFT JOIN (
				SELECT
					org.c_org_id,
					SUM (invoiceDetail.quantity) AS cantidad,
					SUM (invoiceDetail.linetotal) AS importe,
					invoiceHeader.created
				FROM
					c_invoiceheader invoiceHeader
				JOIN c_invoicedetail invoiceDetail ON (
					invoiceHeader.c_invoiceheader_id = invoiceDetail.c_invoiceheader_id
				)
				JOIN c_product product ON invoiceDetail.c_product_id = product.c_product_id
				JOIN c_org org ON invoiceHeader.c_org_id = org.c_org_id
				WHERE
					invoiceHeader.created BETWEEN '$txtnofechaini'
				AND '$txtnofechafin'
				AND invoiceHeader.issale = 1
				AND product.
				VALUE
					IN (
						'11620301',
						'11620302',
						'11620303',
						'11620304',
						'11620305'
					)
				GROUP BY
					org.c_org_id,
					invoiceHeader.created
			) LIQUIDO ON (
				_general.c_org_id = LIQUIDO.c_org_id AND LIQUIDO.created in (_general.systemdate)
			)
			LEFT JOIN (
				SELECT
					org.c_org_id,
					SUM (invoiceDetail.quantity) AS cantidad,
					SUM (invoiceDetail.linetotal) AS importe,
					invoiceHeader.created
				FROM
					c_invoiceheader invoiceHeader
				JOIN c_invoicedetail invoiceDetail ON invoiceHeader.c_invoiceheader_id = invoiceDetail.c_invoiceheader_id
				JOIN c_product product ON invoiceDetail.c_product_id = product.c_product_id
				JOIN c_org org ON invoiceHeader.c_org_id = org.c_org_id
				WHERE
					invoiceHeader.created BETWEEN '$txtnofechaini'
				AND '$txtnofechafin'
				AND invoiceHeader.issale = 1
				AND product.
				VALUE
					NOT IN (
						'11620307',
						'11620308',
						'11620301',
						'11620302',
						'11620303',
						'11620304',
						'11620305'
					)
				GROUP BY
					org.c_org_id,
					invoiceHeader.created
			) MARKET ON (
				_general.c_org_id = MARKET.c_org_id AND MARKET.created in (_general.systemdate)
			)

			ORDER BY
				nombre
				, _general.systemdate
			;
			";


			$result['query'] = $sql;
			$result['fechaIni'] = $data['txtnofechaini'];
			$result['fechaFin'] = $data['txtnofechafin'];

			if ($sqlca_central->query($sql) <= 0) {
               	//throw new Exception("No se encontro ningun registro");
               	$result['isNull'] = true;
			} else {
				$result['isNull'] = false;
			}
       
			while ($reg = $sqlca_central->fetchRow()) {
				$registro[] = $reg;
			}

			$result['data'] = $registro;
			//return $registro;
			return $result;

		} catch(Exception $e) {
			throw $e;
		}

	}

}

