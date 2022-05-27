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


class InterfaceSAPModel extends Model {

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
    
	public function getTax() {
		global $sqlca;
		$sql = "SELECT util_fn_igv();";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$row['tax'] = (double)$row['util_fn_igv'];
		$row['tax'] = (1 + ($row['tax'] / 100));
		return $row['tax'];
	}
    
	/**
	* Lista de tablas de configuración de interfaz ERP (Proveedor)
	*/
	public function getTableConfiguration() {
		global $sqlca;

		$sql = "
SELECT
 TBERP.*
FROM
 sap_mapeo_tabla AS TBERP
 JOIN tipo_interface AS TIPOERP
  USING (id_tipo_interface)
WHERE
 TIPOERP.id_tipo_interface = (SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1)
ORDER BY
 1;
		";
		
		$iStatus = $sqlca->query($sql);
		$arrResult = array();
		$arrResult['bStatus'] = false;
		$arrResult['sMessage'] = 'Problemas al obtener tablas';
		if($iStatus == 0){
			$arrResult['sMessage'] = 'No se encontró ningún registro';
		} else{
			$arrResult['bStatus'] = true;
			$arrResult['sMessage'] = 'Datos encontrados';
			$arrResult['arrData'] = $sqlca->fetchAll();
		}
		return $arrResult;
	}

	function getDetailTableConfiguration($arrPOST){
		global $sqlca;

		$sql = "
SELECT
 *
FROM
 sap_mapeo_tabla_detalle
WHERE
 id_tipo_tabla=" . $arrPOST['iCodeTableConfiguration'] . "
ORDER BY
 1;
		";

		$iStatus = $sqlca->query($sql);
		$arrResult = array();
		$arrResult['bStatus'] = false;
		$arrResult['sMessage'] = 'Problemas al obtener información detalle de tablas';
		if($iStatus == 0){
			$arrResult['sMessage'] = 'No se encontró ningún registro';
		} else{
			$arrResult['bStatus'] = true;
			$arrResult['sMessage'] = 'Datos encontrados';
			$arrResult['arrData'] = $sqlca->fetchAll();
		}
		return $arrResult;
	}

    function getHeader($arrPOST){
		global $sqlca;

		$sql = "
SELECT
	FIRST(ERPSERIE.sap_codigo) AS Series,
	(CASE WHEN FIRST(PT.td)='B' THEN FIRST(ERPORG.name) ELSE 'C'||FIRST(PT.ruc) END) AS CardCode,
	TO_CHAR(DATE(FIRST(PT.fecha)),'YYYYMMDD') AS docdate,
	TO_CHAR(DATE(FIRST(PT.fecha)),'YYYYMMDD') AS docduedate,
	TO_CHAR(DATE(FIRST(PT.fecha)),'YYYYMMDD') AS taxdate,
	usr AS NumAtCard,
	SUBSTRING(usr, 0, 5) AS FolioPrefixString,
	SUBSTRING(usr, 6, 8) AS FolioNumber,
	FIRST(ERPMP.sap_codigo) AS PaymentGroupCode,
	CASE
	WHEN FIRST(PT.tm)='V' AND FIRST(PT.td)='B' THEN '03'
	WHEN FIRST(PT.tm)='V' AND FIRST(PT.td)='F' THEN '01'
	ELSE
	'07'
	END AS Indicator,
	ROUND(SUM(importe), 2) AS DocTotal, --TC-0000006049
	FIRST(ERPORG.description) AS SalesPersonCode,
	CASE WHEN FIRST(IEP.codigo_iridium) IS NULL THEN FIRST(IEP.codigo_iridium) ELSE FIRST(ITEM.art_descbreve) END AS Comments,
	CASE WHEN FIRST(IEP.codigo_iridium) IS NULL THEN FIRST(IEP.codigo_iridium) ELSE FIRST(ITEM.art_descbreve) END AS JournalMemo,
	TO_CHAR(DATE(FIRST(PT.dia)),'YYYYMMDD') AS U_EXC_FECRECEP,
	FIRST(turno) AS U_CTG_NUMLIQ,
	ROUND(SUM(PT.importe-PT.igv), 2) AS importe, --TC-0000006049
	ROUND(SUM(PT.igv), 2) AS impuestos, --TC-0000006049
	'' AS u_exx_serie,
	'' AS u_exx_nroini,
	'' AS u_exx_nrofin
FROM
	" . $arrPOST['sTablePostransYM'] . " AS PT
	JOIN sap_mapeo_tabla_detalle AS ERPORG
	ON(ERPORG.opencomb_codigo = PT.es AND ERPORG.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Centro Costo' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN ebi_serial AS EBIS
	ON(
	EBIS.ch_sucursal = PT.es
	AND EBIS.s_pos_id = PT.caja::INTEGER
	AND(
		EBIS.doctype = (CASE WHEN PT.tm='V' AND PT.td='B' THEN '03' END) OR
		EBIS.doctype = (CASE WHEN PT.tm='V' AND PT.td='F' THEN '01' END) OR
		EBIS.doctype = (CASE WHEN PT.tm!='V' THEN '07' END)
	)
	AND EBIS.documentserial = SUBSTRING(PT.usr, 0, 5)
	)
	JOIN sap_mapeo_tabla_detalle AS ERPSERIE --AGREGAR LEFT SI HICIERA FALTA
		ON(ERPSERIE.opencomb_codigo = EBIS.ebi_serial_id::TEXT AND ERPSERIE.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Series de Documentos' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPMP
		ON(ERPMP.opencomb_codigo = PT.fpago AND ERPMP.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Condiciones de Pago' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	LEFT JOIN interface_equivalencia_producto AS IEP
		ON(IEP.art_codigo = PT.codigo)
	LEFT JOIN int_articulos AS ITEM
		ON(ITEM.art_codigo = PT.codigo)
WHERE
	PT.es='".$arrPOST['sCodigoAlmacen']."'
	AND PT.dia::DATE='".$arrPOST['dInicial']."'
	AND PT.tm IN('V','A')
	AND PT.td IN('B','F')
GROUP BY
	es,
	caja,
	trans,
	usr
ORDER BY
 	FIRST(PT.fecha)
		";

		// debug
		// echo "getHeader - pos_trans";
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$iStatus = $sqlca->query($sql);
		$arrResult = array();
		$arrResult['bStatus'] = false;
		$arrResult['sMessage'] = 'Problemas al obtener header';
		if($iStatus == 0){
			$arrResult['sMessage'] = 'No se encontró ningún registro';
			return $arrResult;
		} else{
			$arrResult['bStatus'] = true;
			$arrResult['sMessage'] = 'Datos encontrados';
			$arrResult['arrDataPostrans'] = $sqlca->fetchAll();
		}

		$sql = "
SELECT
	FIRST(ERPSERIE.sap_codigo) AS Series,
	(CASE WHEN FIRST(FC.ch_fac_tipodocumento)='35' THEN FIRST(ERPORG.name) ELSE 'C'||FIRST(CLI.cli_ruc) END) AS CardCode,
	TO_CHAR(DATE(FIRST(FC.dt_fac_fecha)),'YYYYMMDD') AS docdate,
	TO_CHAR(DATE(FIRST(FC.dt_fac_fecha)),'YYYYMMDD') AS docduedate,
	TO_CHAR(DATE(FIRST(FC.dt_fac_fecha)),'YYYYMMDD') AS taxdate,
	FC.ch_fac_seriedocumento||'-'||LPAD(FC.ch_fac_numerodocumento, 8, '0') AS NumAtCard,
	FC.ch_fac_seriedocumento AS FolioPrefixString,
	LPAD(FC.ch_fac_numerodocumento, 8, '0') AS FolioNumber,
	FIRST(ERPMP.sap_codigo) AS PaymentGroupCode,
	FIRST(TDOCU.tab_car_03) AS Indicator,
	ROUND(SUM(FD.nu_fac_valortotal), 2) AS DocTotal,
	FIRST(ERPORG.description) AS SalesPersonCode,
	FIRST(ITEM.art_descbreve) AS Comments,
	FIRST(ITEM.art_descbreve) AS JournalMemo,
	TO_CHAR(DATE(FIRST(FC.dt_fac_fecha)),'YYYYMMDD') AS U_EXC_FECRECEP,
	0 AS U_CTG_NUMLIQ,
	ROUND(SUM(FD.nu_fac_importeneto), 2) AS importe,
	ROUND(SUM(FD.nu_fac_impuesto1), 2) AS impuestos,
	'' AS u_exx_serie,
	'' AS u_exx_nroini,
	'' AS u_exx_nrofin
FROM
	fac_ta_factura_cabecera AS FC
	JOIN fac_ta_factura_detalle AS FD
		USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
	JOIN int_clientes AS CLI
		USING(cli_codigo)
	JOIN int_tabla_general AS TDOCU
		ON (SUBSTRING(TDOCU.tab_elemento, 5) = FC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
	JOIN sap_mapeo_tabla_detalle AS ERPORG
		ON(ERPORG.opencomb_codigo = FC.ch_almacen AND ERPORG.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Centro Costo' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPSERIE
		--ON(ERPSERIE.opencomb_codigo = FC.ch_fac_tipodocumento AND ERPSERIE.name = FC.ch_fac_seriedocumento AND ERPSERIE.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Series de Documentos' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
		ON(ERPSERIE.opencomb_codigo = FC.ch_fac_tipodocumento AND ERPSERIE.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Series de Documentos' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPMP
		ON(ERPMP.opencomb_codigo = substring(FC.nu_tipo_pago, 2, 1) AND ERPMP.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Condiciones de Pago' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	LEFT JOIN int_articulos AS ITEM
		USING(art_codigo)
WHERE
	FC.ch_almacen='".$arrPOST['sCodigoAlmacen']."'
	AND FC.dt_fac_fecha='".$arrPOST['dInicial']."'
GROUP BY
	FC.ch_almacen,
	FC.dt_fac_fecha,
	FC.ch_fac_tipodocumento,
	FC.ch_fac_seriedocumento,
	FC.ch_fac_numerodocumento
ORDER BY
 	FC.dt_fac_fecha;
		";

		// debug
		// echo "getHeader - fac_ta_factura_cabecera";
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$iStatusSQL = $sqlca->query($sql);
		if ( (int)$iStatusSQL < 0 ){
			return array(
				'bStatus' => false,
				'sMessage' => 'Problemas al obtener header - manual',
				'sMessageSQL' => $sqlca->get_error(),
			);
		} else if ( (int)$iStatusSQL == 0 ){
			$arrResult['arrDataManual'] = array();
		} else if ( (int)$iStatusSQL > 0 ){
			$arrResult['arrDataManual'] = $sqlca->fetchAll();
		}

		$arrResult['arrData'] = array_merge($arrResult['arrDataPostrans'],$arrResult['arrDataManual']);
		return $arrResult;
	}

    function getDetail($arrPOST){
		global $sqlca;

		$sql = "
SELECT
	CASE
	WHEN FIRST(PT.tm)='V' AND FIRST(PT.td)='B' THEN '03'
	WHEN FIRST(PT.tm)='V' AND FIRST(PT.td)='F' THEN '01'
	ELSE
	'07'
	END AS Indicator,
	FIRST(ERPITEM.sap_codigo) AS ItemCode,
	FIRST(PT.codigo) AS ItemCodeOcs,
	FIRST(ERPALMA.sap_codigo) AS WarehouseCode,
	ROUND(SUM(PT.cantidad), 3) AS Quantity, --TC-0000006049
	ROUND(SUM(PT.precio), 3) AS Price, --TC-0000006049
	FIRST(ERPORG.sap_codigo) AS CostingCode,
	FIRST(OPERACIONES.sap_codigo) AS CostingCode2,
	(CASE 
		WHEN FIRST(PT.codigo) IN (SELECT ch_codigocombustible FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307') THEN '03' 
		WHEN FIRST(PT.codigo) IN (SELECT ch_codigocombustible FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307') THEN '02' 
		ELSE '04' 
	END) AS CostingCode3,
	FIRST(DESTINO.sap_codigo) AS CostingCode4,
	FIRST(ERPUM.description) AS UnitsOfMeasurment,
	FIRST(ERPITEM.description) AS U_EXX_PERDGHDCM,
	FIRST(ERPUM.name) AS MeasureUnit
FROM
	" . $arrPOST['sTablePostransYM'] . " AS PT
	JOIN sap_mapeo_tabla_detalle AS ERPORG
	   	ON(ERPORG.opencomb_codigo = PT.es AND ERPORG.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Centro Costo' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPALMA
		ON(ERPALMA.opencomb_codigo = PT.es AND ERPALMA.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Almacen' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPITEM
		ON(ERPITEM.opencomb_codigo = PT.codigo AND ERPITEM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Items' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN int_articulos AS ITEM
		ON(PT.codigo = ITEM.art_codigo)
	JOIN sap_mapeo_tabla_detalle AS ERPUM
		ON(ERPUM.opencomb_codigo = ITEM.art_unidad AND ERPUM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Unidad Medida' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	LEFT JOIN sap_mapeo_tabla_detalle AS OPERACIONES 
		ON(TRIM(OPERACIONES.opencomb_codigo) = 'Ventas' AND OPERACIONES.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Operaciones' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	LEFT JOIN sap_mapeo_tabla_detalle AS DESTINO 
		ON(TRIM(DESTINO.opencomb_codigo) = 'Ventas' AND DESTINO.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Destino' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
WHERE
	PT.es='".$arrPOST['sCodigoAlmacen']."'
	AND PT.dia::DATE='".$arrPOST['dInicial']."'
	AND PT.tm IN('V','A')
	AND PT.td IN('B','F')
GROUP BY
	es,
	caja,
	trans,
	usr
ORDER BY
 	FIRST(PT.fecha)
		";

		// debug
		// echo "getDetail - pos_trans";
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$iStatus = $sqlca->query($sql);
		$arrResult = array();
		$arrResult['bStatus'] = false;
		$arrResult['sMessage'] = 'Problemas al obtener detail';
		if($iStatus == 0){
			$arrResult['sMessage'] = 'No se encontró ningún registro';
		} else{
			$arrResult['bStatus'] = true;
			$arrResult['sMessage'] = 'Datos encontrados';
			$arrResult['arrDataPostrans'] = $sqlca->fetchAll();
		}

		$sql = "
SELECT
	FIRST(TDOCU.tab_car_03) AS Indicator,
	FIRST(ERPITEM.sap_codigo) AS ItemCode,
	FIRST(ITEM.art_codigo) AS ItemCodeOcs,
	FIRST(ERPALMA.sap_codigo) AS WarehouseCode,
	ROUND(SUM(FD.nu_fac_cantidad), 3) AS Quantity,
	ROUND(FIRST(FD.nu_fac_precio), 3) AS Price,
	FIRST(ERPORG.sap_codigo) AS CostingCode,	
	FIRST(OPERACIONES.sap_codigo) AS CostingCode2,
	(CASE 
		WHEN FIRST(ITEM.art_codigo) IN (SELECT ch_codigocombustible FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307') THEN '03' 
		WHEN FIRST(ITEM.art_codigo) IN (SELECT ch_codigocombustible FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307') THEN '02' 
		ELSE '04' 
	END) AS CostingCode3,
	FIRST(DESTINO.sap_codigo) AS CostingCode4,
	FIRST(ERPUM.description) AS UnitsOfMeasurment,
	FIRST(ERPITEM.description) AS U_EXX_PERDGHDCM,
	FIRST(ERPUM.name) AS MeasureUnit	
FROM
	fac_ta_factura_cabecera AS FC
	JOIN fac_ta_factura_detalle AS FD
		USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
	JOIN int_tabla_general AS TDOCU
		ON (SUBSTRING(TDOCU.tab_elemento, 5) = FC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
	JOIN sap_mapeo_tabla_detalle AS ERPORG
		ON(ERPORG.opencomb_codigo = FC.ch_almacen AND ERPORG.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Centro Costo' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPALMA
		ON(ERPALMA.opencomb_codigo = FC.ch_almacen AND ERPALMA.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Almacen' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN sap_mapeo_tabla_detalle AS ERPITEM --AGREGAR LEFT SI HICIERA FALTA
		ON(ERPITEM.opencomb_codigo = FD.art_codigo AND ERPITEM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Items' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	JOIN int_articulos AS ITEM
		USING(art_codigo)
	JOIN sap_mapeo_tabla_detalle AS ERPUM
		ON(ERPUM.opencomb_codigo = ITEM.art_unidad AND ERPUM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Unidad Medida' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))	
	LEFT JOIN sap_mapeo_tabla_detalle AS OPERACIONES 
		ON(TRIM(OPERACIONES.opencomb_codigo) = 'Ventas' AND OPERACIONES.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Operaciones' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
	LEFT JOIN sap_mapeo_tabla_detalle AS DESTINO 
		ON(TRIM(DESTINO.opencomb_codigo) = 'Ventas' AND DESTINO.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Destino' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
WHERE
	FC.ch_almacen='".$arrPOST['sCodigoAlmacen']."'
	AND FC.dt_fac_fecha='".$arrPOST['dInicial']."'
GROUP BY
	FC.ch_almacen,
	FC.dt_fac_fecha,
	FC.ch_fac_tipodocumento,
	FC.ch_fac_seriedocumento,
	FC.ch_fac_numerodocumento
ORDER BY
 	FC.dt_fac_fecha;
		";

		// debug
		// echo "getDetail - fac_ta_factura_cabecera";
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$iStatusSQL = $sqlca->query($sql);
		if ( (int)$iStatusSQL < 0 ){
			return array(
				'bStatus' => false,
				'sMessage' => 'Problemas al obtener header - manual',
				'sMessageSQL' => $sqlca->get_error(),
			);
		} else if ( (int)$iStatusSQL == 0 ){
			$arrResult['arrDataManual'] = array();
		} else if ( (int)$iStatusSQL >= 0 ){
			$arrResult['arrDataManual'] = $sqlca->fetchAll();
		}

		$arrResult['arrData'] = array_merge($arrResult['arrDataPostrans'],$arrResult['arrDataManual']);
		return $arrResult;
	}

    function getResumen($arrPOST){
		global $sqlca;

		$sql = "
SELECT
 FIRST(ERPITEM.sap_codigo) AS codigo,
 FIRST(ERPITEM.name) AS producto,
 ROUND(SUM(PT.cantidad), 2) AS cantidad,
 ROUND(SUM(PT.importe-PT.igv), 2) AS importe,
 ROUND(SUM(PT.igv), 2) AS impuestos,
 ROUND(SUM(PT.importe), 2) AS total
FROM
 " . $arrPOST['sTablePostransYM'] . " AS PT
 JOIN sap_mapeo_tabla_detalle AS ERPITEM
  ON(ERPITEM.opencomb_codigo = PT.codigo AND ERPITEM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Items' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='SAP BUSINESS ONE (EXCEL Y TEXTO)' LIMIT 1) LIMIT 1))
WHERE
 PT.es='".$arrPOST['sCodigoAlmacen']."'
 AND PT.dia::DATE='".$arrPOST['dInicial']."'
 AND PT.tm IN('V','A')
 AND PT.td IN('B','F')
GROUP BY
 codigo
		";

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$iStatusProducto = $sqlca->query($sql);
		$arrResult = array();
		$arrResult['bStatus'] = false;
		$arrResult['sMessage'] = 'Problemas al obtener resumen(producto)';
		if( (int)$iStatusProducto < 0){
			return $arrResult;
		} else if( (int)$iStatusProducto == 0){
			$arrResult['sMessage'] = 'No se encontró ningún registro';
			return $arrResult;
		}
		$arrResult['arrData']['arrProductos'] = $sqlca->fetchAll();

		$sql = "
SELECT
 TO_CHAR(VD.dt_fecha,'DD/MM/YYYY') AS fecha,
 VD.ch_documento AS despacho,
 ITEM.art_descbreve AS producto,
 VD.nu_cantidad AS cantidad,
 VC.ch_placa AS placa,
 (CASE
  WHEN cli_ndespacho_efectivo = '0' AND cli_anticipo = 'N' THEN 'EFECTIVO'
  WHEN cli_ndespacho_efectivo = '1' AND cli_anticipo = 'N' THEN 'CREDITO'
  WHEN cli_ndespacho_efectivo = '0' AND cli_anticipo = 'S' THEN 'ANTICIPO'
 END) pago,
 VC.ch_cliente AS cliente,
 VC.ch_turno AS turno,
 SPLIT_PART(VC.fecha_replicacion::TEXT, ' ', 2) AS hora,
 (CASE WHEN ch_liquidacion != '' THEN 'LIQUIDADO' ELSE 'PENDIENTE' END) AS estado
FROM
 val_ta_cabecera AS VC
 JOIN val_ta_detalle AS VD
  USING(ch_sucursal, dt_fecha, ch_documento)
 JOIN int_clientes AS CLI
  ON (CLI.cli_codigo = VC.ch_cliente)
 JOIN int_articulos AS ITEM
  ON(VD.ch_articulo = ITEM.art_codigo)
WHERE
 VC.ch_sucursal='".$arrPOST['sCodigoAlmacen']."'
 AND VC.dt_fecha='".$arrPOST['dInicial']."'
		";

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$iStatusVales = $sqlca->query($sql);
		$arrResult['bStatus'] = false;
		$arrResult['sMessage'] = 'Problemas al obtener resumen(vales)';
		if($iStatusVales == 0){
			$arrResult['sMessage'] = 'No se encontró ningún registro';
			return $arrResult;
		}

		if ( (int)$iStatusProducto>0 && (int)$iStatusVales>0 ) {
			$arrResult['bStatus'] = true;
			$arrResult['sMessage'] = 'Datos encontrados';
			$arrResult['arrData']['arrVales'] = $sqlca->fetchAll();
		}
		return $arrResult;
	}

	public function saveConfiguration($arrPOST){
		global $sqlca;

		$sql = "
INSERT INTO sap_mapeo_tabla_detalle
VALUES (
	" . $arrPOST['iCodeTableConfiguration'] . ",
	nextval('seq_id_tipo_tabla_detalle'),
	'" . $arrPOST['iIDOCS'] . "',
	'" . $arrPOST['iIDSAP'] . "',
	'" . $arrPOST['sName'] . "',
	'" . $arrPOST['sDescription'] . "'
);
		";

		$iStatusSQL = $sqlca->query($sql);
		if ((int)$iStatusSQL >= 0) {
			return array(
				'sStatus' => 'success',
				'sMessage' => 'Registro guardado',
			);
		}
		return array(
			'sStatus' => 'danger',
			'sMessage' => 'Problemas al guardar registro',
			'sMessageSQL' => $sqlca->get_error(),
		);
	}

	public function deleteConfiguration($arrGET){
		global $sqlca;

		$sql = "DELETE FROM sap_mapeo_tabla_detalle WHERE id_tipo_tabla_detalle=" . $arrGET['iIDTipoTablaDetalle'];
		$iStatusSQL = $sqlca->query($sql);
		if ((int)$iStatusSQL >= 0) {
			return array(
				'sStatus' => 'success',
				'sMessage' => 'Registro eliminado',
			);
		}
		return array(
			'sStatus' => 'danger',
			'sMessage' => 'Problemas al eliminar registro',
			'sMessageSQL' => $sqlca->get_error(),
		);
	}
}
