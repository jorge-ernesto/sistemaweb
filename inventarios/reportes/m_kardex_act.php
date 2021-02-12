<?php
ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class KardexActModel extends Model {

	function search($desde, $hasta, $art_desde, $estacion, $linea, $tipovista, $tipoventa, $cond_cantidad) {
    	global $sqlca;

    	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
    	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

		$FechaDiv = explode("/", $desde);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $hasta);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1]) != $postrans && $tipovista == "D")
			return "INVALID_DATE";

    	$saldos 		= KardexActModel::saldoInicial($desde, $art_desde, $estacion, $linea);
    	$formularios 	= KardexActModel::obtenerTiposFormularios();

        $resultado 		= Array();
    	$anteriores 	= Array();

    	foreach ($saldos['almacenes'] as $cod_almacen => $almacen) {
        	foreach ($almacen['codigos'] as $codigo => $articulo) {
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['cant_anterior'] = $articulo['stk_stock'];
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['unit_anterior'] = $articulo['stk_costounitario'];
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['costo_total'] = $articulo['stk_costototal'];
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['codigo_CUO'] = $articulo['cod_CUO'];

		        $anteriores[$cod_almacen][$codigo]['cant_anterior'] = $articulo['stk_stock'];
	        	$anteriores[$cod_almacen][$codigo]['unit_anterior'] = $articulo['stk_costounitario'];
        	}
   		}

		$sql = "";

		$sCast_Numero_Documento = (($tipovista == "D") ? '::INTEGER' : '');

		if($tipovista == "D") {
			$sql.="SELECT * FROM (";
		}

//CASE WHEN inv.mov_tipdocuref!='10' AND inv.mov_tipdocuref!='35' THEN inv.mov_docurefe ELSE '00'||SUBSTR(inv.mov_numero, 5, 6) END AS nu_documento,
//CASE WHEN inv.mov_tipdocuref!='10' AND inv.mov_tipdocuref!='35' THEN inv.mov_docurefe ELSE SUBSTR(inv.mov_numero, 0, 5) END AS serie_documento,
		$sql.="
SELECT
 to_char(inv.mov_fecha, 'YYYY-MM-DD 00:00:00') AS fecha,
 trim(inv.tran_codigo),
 inv.mov_numero AS nu_documento,
 inv.mov_almaorigen,
 inv.mov_almadestino,
 inv.mov_entidad,
 TRIM(inv.mov_docurefe) AS serie_documento,
 inv.mov_cantidad,
 inv.mov_costounitario,
 inv.mov_costototal,
 inv.mov_costopromedio,
 inv.art_codigo AS codigo,
 inv.mov_naturaleza AS natu,
 inv.mov_almacen AS es,
 tab.tab_car_03,
 inv.mov_tipdocuref,
 'PROVEEDOR: '||PROVEE.pro_razsocial AS no_nombre_entidad,
 ''::TEXT AS numero_documento_liquidado
FROM
 inv_movialma AS inv 
 LEFT JOIN int_articulos AS art ON (inv.art_codigo=art.art_codigo)  
 LEFT JOIN int_tabla_general AS tab ON (tab.tab_tabla='08' AND lpad(inv.mov_tipdocuref,6,'0')=tab.tab_elemento) 
 LEFT JOIN int_proveedores AS PROVEE ON(inv.mov_entidad = PROVEE.pro_codigo)
WHERE
 inv.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59'
			";

			if (!empty($cond_cantidad))
				$sql .= $cond_cantidad;

			if (trim($art_desde) != "")
				$sql .= "AND inv.art_codigo = '" . pg_escape_string($art_desde) . "'";

			if (trim($linea) != "")
		    	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'";

       		if ($estacion != "TODAS")
	   			$sql .= "AND inv.mov_almacen = '" . pg_escape_string($estacion) . "'";

       		if ($tipoventa == "C" && (trim($art_desde) == "" || empty($art_desde)))
		   		$sql .= "AND inv.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			elseif ($tipoventa == "M" && (trim($art_desde) == "" || empty($art_desde)))
		   		$sql .= "AND inv.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			else
				$sql .= "";

			$sql.="
ORDER BY
 es,
 fecha,
 natu,
 nu_documento,
 codigo
			";

			if($tipovista == "D"){
			$sql.=") AS A UNION (
SELECT
 to_char(cab.dt_fac_fecha,'YYYY-MM-DD 00:00:00') AS fecha,
 cab.ch_fac_tipodocumento AS tipodoc,
 '0'||cab.ch_fac_numerodocumento AS documento,
 cab.ch_almacen AS aorigen,
 '301' AS adestino,
 ''::TEXT,
 cab.ch_fac_seriedocumento::TEXT AS serie_documento,
 det.nu_fac_cantidad as cantidad,
 (select util_fn_costo_promedio(demo.p5,demo.p4,demo.p2,demo.p3)) AS costounitario,
 (select util_fn_costo_promedio(demo.p5,demo.p4,demo.p2,demo.p3)) * det.nu_fac_cantidad  AS costotal,
 (select util_fn_costo_promedio(demo.p5,demo.p4,demo.p2,demo.p3)) AS costopromedio,
 det.art_codigo AS articulo,
 (SELECT trim(tran_naturaleza) AS tran_naturaleza FROM inv_tipotransa WHERE tran_codigo = cab.ch_fac_tipodocumento) AS naturaleza,
 cab.ch_almacen AS almacen,
 FIRST(TDOCU.tab_car_03)::TEXT AS nu_tipo_documento_sunat,
 ''::TEXT,--15
 'CLIENTE: '||FIRST(CLI.cli_razsocial) AS no_nombre_entidad,
 ''::TEXT AS numero_documento_liquidado
FROM
 fac_ta_factura_cabecera AS cab
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = cab.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 INNER JOIN fac_ta_factura_detalle AS det ON(det.ch_fac_tipodocumento=cab.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=cab.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=cab.ch_fac_numerodocumento)
 LEFT JOIN int_articulos AS art ON (art.art_codigo=det.art_codigo)
 LEFT JOIN int_clientes AS CLI ON(CLI.cli_codigo=cab.cli_codigo)
 INNER JOIN (
 SELECT
  to_char(faccab.dt_fac_fecha,'dd/mm/yyyy') AS p1,
  facdet.art_codigo AS p2,
  faccab.ch_almacen AS p3,
  to_char(faccab.dt_fac_fecha,'mm') AS p4,
  to_char(faccab.dt_fac_fecha,'yyyy') AS p5,
  faccab.ch_fac_tipodocumento AS p6,
  faccab.ch_fac_seriedocumento AS p7,
  faccab.ch_fac_numerodocumento AS p8
 FROM
  fac_ta_factura_cabecera AS faccab
  INNER JOIN fac_ta_factura_detalle AS facdet ON(facdet.ch_fac_tipodocumento=faccab.ch_fac_tipodocumento AND facdet.ch_fac_seriedocumento=faccab.ch_fac_seriedocumento AND facdet.ch_fac_numerodocumento=faccab.ch_fac_numerodocumento)
  LEFT JOIN int_articulos AS facart ON (facart.art_codigo=facdet.art_codigo)
 WHERE
  faccab.dt_fac_fecha BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')
  AND faccab.ch_fac_tipodocumento != '45'
  AND faccab.ch_fac_cd_impuesto3 = 'S'
  AND (faccab.ch_descargar_stock IS NULL OR faccab.ch_descargar_stock='N')
";

			if ($estacion != "TODAS")
				$sql .= "AND faccab.ch_almacen ='" . pg_escape_string($estacion) . "' ";

			if (trim($art_desde) != "")
				$sql .= "AND facdet.art_codigo = '" . pg_escape_string($art_desde) . "'";

			if (trim($linea) != "")
		    		$sql .= "AND facart.art_linea = '" . pg_escape_string($linea) . "'";

		    if ($tipoventa == "C" && (trim($art_desde) == "" || empty($art_desde)))
		   		$sql .= "AND facdet.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			elseif ($tipoventa == "M" && (trim($art_desde) == "" || empty($art_desde)))
		   		$sql .= "AND facdet.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			else
				$sql .= "";

			$sql .= "
 GROUP BY
  faccab.dt_fac_fecha,
  faccab.ch_almacen,
  facdet.art_codigo,
  faccab.ch_fac_tipodocumento,
  faccab.ch_fac_seriedocumento,
  faccab.ch_fac_numerodocumento
 ) AS demo ON (cab.ch_fac_tipodocumento = demo.p6 AND cab.ch_fac_seriedocumento=demo.p7 AND cab.ch_fac_numerodocumento = demo.p8)
WHERE
 cab.dt_fac_fecha BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')
 AND cab.ch_fac_tipodocumento != '45'
 AND cab.ch_fac_cd_impuesto3 = 'S'--Despachos electronicos perdidos
 AND (cab.ch_descargar_stock IS NULL OR cab.ch_descargar_stock='N')
";

			if ($estacion != "TODAS")
			$sql .= "AND cab.ch_almacen ='" . pg_escape_string($estacion) . "' ";

			if (trim($art_desde) != "")
				$sql .= "AND det.art_codigo = '" . pg_escape_string($art_desde) . "'";

			if (trim($linea) != "")
		    	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'";

		    if ($tipoventa == "C" && (trim($art_desde) == "" || empty($art_desde)))
		   		$sql .= "AND det.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			elseif ($tipoventa == "M" && (trim($art_desde) == "" || empty($art_desde)))
		   		$sql .= "AND det.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			else
				$sql .= "";

			$sql .= "
GROUP BY
 cab.dt_fac_fecha,
 cab.ch_fac_tipodocumento,
 cab.ch_almacen,
 det.art_codigo,
 cab.ch_fac_seriedocumento,
 cab.ch_fac_numerodocumento,
 det.nu_fac_cantidad,
 demo.p5,
 demo.p4,
 demo.p2,
 demo.p3
";

/*** Agregado 2020-02-13 ****/
//Llamagas Arequipa: Problema con trans iguales con usr distintos, suma de cantidades en el modulo KARDEX
//Linea 291 se agrego pos.caja,pos.trans
//Linea 327 se agrego pos.caja,pos.trans
//Linea 367 se agrego pos.caja, ya exisitia pos_trans
//Linea 292 se agrego AND ITEMESTAN.codigo = pos.codigo

			$sql.="
) UNION (
SELECT
 to_char(pos.dia,'YYYY-MM-DD 00:00:00') AS fecha,
 '12',
 (CASE WHEN pos.usr != '' THEN SUBSTR(TRIM(pos.usr), 6) ELSE pos.trans::TEXT END) AS nu_documento,
 pos.es,
 pos.es,
 FIRST(pos.ruc),
 (CASE WHEN pos.usr != '' THEN  SUBSTR(TRIM(pos.usr), 0, 5) ELSE s.printerserial END)::TEXT,
 FIRST(COALESCE(ITEMESTAN.cantidad, 0) + COALESCE(ITEMPLU.cantidad, 0)),
 FIRST(inv.mov_costounitario),
 (FIRST(COALESCE(ITEMESTAN.cantidad, 0) + COALESCE(ITEMPLU.cantidad, 0)) * FIRST(inv.mov_costounitario)),
 FIRST(inv.mov_costopromedio),
 art.art_codigo AS codigo,
 '3' AS natu,
 pos.es,
 (CASE
  WHEN pos.usr != '' AND FIRST(pos.tm)='V' AND FIRST(pos.td)='B' THEN '03'
  WHEN pos.usr != '' AND FIRST(pos.tm)='V' AND FIRST(pos.td)='F' THEN '01'
  WHEN pos.usr != '' AND FIRST(pos.tm)='A' AND FIRST(pos.td) IN('B','F') THEN '07'
  WHEN pos.usr = '' AND FIRST(pos.tm) IN('V','A') AND FIRST(pos.td) IN('B','F') THEN '12'
  ELSE '00'
 END),
 '',--15
 'CLIENTE: '||(CASE WHEN FIRST(pos.td IN('F','B')) THEN FIRST(R.razsocial) ELSE FIRST(CLI.cli_razsocial) END) AS no_nombre_entidad,
 FIRST(TDOCU.tab_descripcion)||'-'||FIRST(FVLIQUI.ch_fac_seriedocumento)||'-'||FIRST(FVLIQUI.ch_fac_numerodocumento) AS numero_documento_liquidado
FROM
 {$postrans} AS pos
 LEFT JOIN (
 SELECT
  es,
  tm,
  td,
  dia,
  trans,
  codigo,
  caja,
  SUM(cantidad) AS cantidad
 FROM
  {$postrans} AS pos
  LEFT JOIN int_articulos AS art ON (art.art_codigo = pos.codigo)
 WHERE
  pos.dia BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')
  AND pos.td IN('B','F')
  AND art.art_plutipo='1'
";
if (trim($art_desde) != "")
	$sql .= "AND pos.codigo = '" . pg_escape_string($art_desde) . "'";

if (trim($linea) != "")
	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'";

if ($estacion != "TODAS")
$sql .= "AND pos.es='" . pg_escape_string($estacion) . "'";

if ($tipoventa == "C" && (trim($art_desde) == "" || empty($art_desde)))
	$sql .= "AND pos.tipo = 'C'";
elseif ($tipoventa == "M" && (trim($art_desde) == "" || empty($art_desde)))
	$sql .= "AND pos.tipo = 'M'";

$sql .= "
 GROUP BY 1,2,3,4,5,6,pos.caja,pos.trans
 ) AS ITEMESTAN ON(ITEMESTAN.es = pos.es AND ITEMESTAN.td = pos.td AND ITEMESTAN.tm = pos.tm AND ITEMESTAN.dia = pos.dia AND ITEMESTAN.trans = pos.trans AND ITEMESTAN.codigo = pos.codigo AND ITEMESTAN.caja = pos.caja)
 LEFT JOIN (
 SELECT
  es,
  tm,
  td,
  dia,
  trans,
  pos.codigo AS codigo,
  ENLAITEM.ch_item_estandar AS codigo_estandar,
  SUM(cantidad * ENLAITEM.nu_cantidad_descarga) AS cantidad
 FROM
  {$postrans} AS pos
  LEFT JOIN int_ta_enlace_items AS ENLAITEM ON(ENLAITEM.art_codigo = pos.codigo)
  LEFT JOIN int_articulos AS art ON (art.art_codigo = ENLAITEM.ch_item_estandar)
 WHERE
  pos.dia BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')
  AND pos.td IN('B','F')
";
if (trim($art_desde) != ""){
	$sql .= "AND ENLAITEM.ch_item_estandar = '" . pg_escape_string($art_desde) . "'";
}

if (trim($linea) != "")
	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'";

if ($estacion != "TODAS")
$sql .= "AND pos.es='" . pg_escape_string($estacion) . "'";

if ($tipoventa == "C" && (trim($art_desde) == "" || empty($art_desde)))
	$sql .= "AND pos.tipo = 'C'";
elseif ($tipoventa == "M" && (trim($art_desde) == "" || empty($art_desde)))
	$sql .= "AND pos.tipo = 'M'";

$sql .= "
 GROUP BY 1,2,3,4,5,6,7,pos.caja,pos.trans
 ) AS ITEMPLU ON(ITEMPLU.es = pos.es AND ITEMPLU.td = pos.td AND ITEMPLU.tm = pos.tm AND ITEMPLU.dia = pos.dia AND ITEMPLU.trans = pos.trans AND ITEMPLU.codigo = pos.codigo)
 LEFT JOIN inv_movialma AS inv ON (inv.mov_almacen = pos.es AND inv.mov_fecha::date = pos.dia AND (inv.art_codigo = ITEMESTAN.codigo OR inv.art_codigo = ITEMPLU.codigo_estandar))
 LEFT JOIN int_articulos AS art ON (art.art_codigo = ITEMESTAN.codigo OR art.art_codigo = ITEMPLU.codigo_estandar)
 LEFT JOIN s_pos AS s ON (s.s_pos_id = pos.caja::INTEGER)
 LEFT JOIN ruc AS R USING(ruc)
 LEFT JOIN int_clientes AS CLI ON(CLI.cli_codigo=pos.cuenta)
 LEFT JOIN val_ta_cabecera AS VC ON(VC.ch_sucursal = pos.es AND VC.dt_fecha = pos.dia::DATE AND VC.ch_cliente=pos.cuenta AND (VC.ch_documento = pos.caja||'-'||pos.trans::VARCHAR OR VC.ch_documento = pos.trans::VARCHAR))
 LEFT JOIN val_ta_complemento_documento AS FVLIQUI ON(FVLIQUI.ch_sucursal=VC.ch_sucursal AND FVLIQUI.ch_cliente = VC.ch_cliente AND FVLIQUI.dt_fecha=VC.dt_fecha AND FVLIQUI.ch_numeval=VC.ch_documento)
 LEFT JOIN int_tabla_general AS TDOCU ON (TDOCU.tab_tabla='08' AND FVLIQUI.ch_fac_tipodocumento=SUBSTRING(TDOCU.tab_elemento, 5))
WHERE
 inv.tran_codigo IN('25','45')
 AND pos.dia BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')
 AND pos.td IN('B','F')
				";

				if (trim($art_desde) != "")
					$sql .= "AND (pos.codigo = '" . pg_escape_string($art_desde) . "' OR ITEMPLU.codigo_estandar = '" . pg_escape_string($art_desde) . "') ";

				if (trim($linea) != "")
			    	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'  ";

		       	if ($estacion != "TODAS")
			   		$sql .= "AND pos.es='" . pg_escape_string($estacion) . "' ";

		       	if ($tipoventa == "C" && (trim($art_desde) == "" || empty($art_desde)))
			   		$sql .= "AND pos.tipo = 'C'";
				elseif ($tipoventa == "M" && (trim($art_desde) == "" || empty($art_desde)))
			   		$sql .= "AND pos.tipo = 'M'";
				else
					$sql .= "";

				$sql.="
 GROUP BY
  pos.es,
  pos.dia,
  pos.trans,
  s.printerserial,
  pos.usr,
  art.art_codigo,
  pos.caja
) UNION (
SELECT
 to_char(pos.dia,'YYYY-MM-DD 00:00:00') AS fecha,
 '23',
 pos.trans::TEXT AS nu_documento,
 pos.es,
 pos.es,
 '',
 s.printerserial::TEXT,
 pos.cantidad,
 inv.mov_costounitario,
 pos.cantidad * inv.mov_costounitario,
 inv.mov_costopromedio,
 art.art_codigo AS codigo,
 '3' AS natu,
 pos.es,
 '00',
 '',--15
 '' AS no_nombre_entidad,
 '' AS numero_documento_liquidado
FROM
 pos_ta_afericiones AS pos
 LEFT JOIN int_articulos AS art ON (art.art_codigo = pos.codigo)
 LEFT JOIN inv_movialma AS inv ON (inv.mov_almacen = pos.es AND inv.mov_fecha::date = pos.dia AND inv.art_codigo = art.art_codigo)
 LEFT JOIN s_pos AS s ON (s.s_pos_id = pos.caja::INTEGER)
WHERE
 inv.tran_codigo IN('25','45')
 AND pos.dia BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')
";
				if (trim($art_desde) != "")
					$sql .= "AND pos.codigo = '" . pg_escape_string($art_desde) . "'  ";

				if (trim($linea) != "")
			    	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'  ";

		       	if ($estacion != "TODAS")
			   		$sql .= "AND pos.es='" . pg_escape_string($estacion) . "' ";
$sql .="
) UNION (
SELECT DISTINCT
 to_char(VC.dt_fecha,'YYYY-MM-DD 00:00:00') AS fecha,
 '11',
 VC.ch_documento AS nu_documento,
 VC.ch_sucursal,
 VC.ch_sucursal,
 CLI.cli_ruc,
 'FI00'::TEXT,
 VD.nu_cantidad,
 inv.mov_costounitario,
 (VD.nu_cantidad * inv.mov_costounitario),
 inv.mov_costopromedio,
 VD.ch_articulo AS codigo,
 '3' AS natu,
 VC.ch_sucursal,
 '00',
 '',--15
 'CLIENTE: '|| CLI.cli_razsocial AS no_nombre_entidad,
 CASE WHEN FVLIQUI.accion!='XCOBRAR' THEN
  TDOCU.tab_descripcion||'-'||FVLIQUI.ch_fac_seriedocumento||'-'||FVLIQUI.ch_fac_numerodocumento
 ELSE
  'FACTURA-'||FVLIQUI.cod_hermandad
 END AS numero_documento_liquidado
FROM
 val_ta_cabecera AS VC
 LEFT JOIN int_clientes AS CLI ON(CLI.cli_codigo=VC.ch_cliente)
 LEFT JOIN val_ta_complemento_documento AS FVLIQUI ON(FVLIQUI.ch_sucursal=VC.ch_sucursal AND FVLIQUI.ch_cliente = VC.ch_cliente AND FVLIQUI.dt_fecha=VC.dt_fecha AND FVLIQUI.ch_numeval=VC.ch_documento)
 LEFT JOIN val_ta_detalle AS VD ON(VD.ch_sucursal = VC.ch_sucursal AND VD.dt_fecha=VC.dt_fecha AND VD.ch_documento=VC.ch_documento)
 LEFT JOIN val_ta_complemento AS VCC ON(VCC.ch_sucursal = VC.ch_sucursal AND VCC.dt_fecha=VC.dt_fecha AND VCC.ch_documento=VC.ch_documento)
 LEFT JOIN int_articulos AS art ON(VD.ch_articulo = art.art_codigo)
 LEFT JOIN int_tabla_general AS TDOCU ON(TDOCU.tab_tabla='08' AND FVLIQUI.ch_fac_tipodocumento=SUBSTRING(TDOCU.tab_elemento, 5))
 LEFT JOIN inv_movialma AS inv ON(inv.tran_codigo='25' AND inv.mov_almacen = VC.ch_sucursal AND inv.mov_fecha::date = VC.dt_fecha AND inv.art_codigo = VD.ch_articulo)
WHERE
 VC.dt_fecha BETWEEN TO_DATE('" . $desde . "', 'DD/MM/YYYY') AND TO_DATE('" . $hasta . "', 'DD/MM/YYYY')";
				if (trim($art_desde) != "")
					$sql .= "AND VD.ch_articulo = '" . pg_escape_string($art_desde) . "'  ";

				if (trim($linea) != "")
			    	$sql .= "AND art.art_linea = '" . pg_escape_string($linea) . "'  ";

		       	if ($estacion != "TODAS")
			   		$sql .= "AND VC.ch_sucursal='" . pg_escape_string($estacion) . "' ";
				$sql.="
)
ORDER BY
 es,
 fecha,
 natu,
 nu_documento,
 codigo;
";
		}

		//Query
		// echo "<pre>";
		// print_r($sql);
		// echo "</pre>";
		// die();


		if ($sqlca->query($sql) < 0)
			return null;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

	    	$mov_fecha			= substr($a[0], 0, 19);
			$tran_codigo		= $a[1];
	    	$mov_numero			= $a[2];
	    	$mov_almaorigen		= $a[3];
	    	$mov_almadestino	= $a[4];
	    	$mov_entidad		= $a[5];
	    	$mov_docurefe		= $a[6];//Compuesto por SERIE||NUMERO de inv_movialma
	    	$mov_cantidad		= $a[7];

	    	$mov_costounitario	= $a[8];
	    	$mov_costototal		= $a[9];
	    	$mov_costopromedio	= $a[10];
			
	    	$art_codigo			= trim($a[11]);
	    	$mov_naturaleza		= $a[12];
	    	$mov_almacen		= $a[13];
	    	$tipodocu			= $a[14];
	    	$tipoopera			= $a[15];
	    	//Nuevas columnas (Entidad y Liquidado)
	    	$sNombreEntidad = $a[16];
	    	$sDocumentoLiquidado = $a[17];

			if ( $tipovista == "D" && ($tran_codigo != "45" && $tran_codigo != "25") ){
				$tipodocu	= $a[14];
				$seriedocu	= $a[6];
				$numdocu	= $a[2];

				$mov_tipdocuref	= $a['mov_tipdocuref'];
				if ( $seriedocu == '' || empty($seriedocu) ) {
					$tipodocu='00';
					$seriedocu		= $mov_almacen;
					$numdocu		= $mov_numero;
				}

				if ( $tran_codigo != '12' && $tran_codigo != '11' && $tran_codigo != '23' ) {
					$seriedocu = substr($a[6], 0, 4);
					$numdocu = substr($a[6], -8);
					if ( strlen($a[6])<=10 ){//Documentos de ventas manuales físicos
						$seriedocu = substr($a[6], 0, 3);
						$numdocu = substr($a[6], -7);
					}
				    $mov_tipdocuref		= $a['mov_tipdocuref'];
				}

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_fecha'] = $mov_fecha;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipodocu'] = $tipodocu;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['seriedocu'] = $seriedocu;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['numdocu'] = $numdocu;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_numero'] = $mov_numero;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['codigo_tipo_tansa'] = $tran_codigo;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_almadestino'] = $mov_almadestino;

				if ($tran_codigo == "18" && $mov_cantidad < 0)//REGULARIZACION CON CANTIDA < 0 - TICKET TC-0000006506
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[80];//SALIDA POR IDENTIFICACIÓN ERRONEA
				else
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];

				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipoopera'] = $tipoopera;

		   		if ($mov_naturaleza < 3) {
					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_entrada'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_entrada'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;
				} else {
					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_salida'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_salida'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
				}

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_docurefe'] = $mov_docurefe;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_tipdocuref'] = $mov_tipdocuref;

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_anterior'] = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_ant'] = $anteriores[$mov_almacen][$art_codigo]['unit_anterior'];

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual * $mov_costopromedio;

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['sNombreEntidad'] = $sNombreEntidad;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['sDocumentoLiquidado'] = $sDocumentoLiquidado;

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cantidad_total'] += $cant_actual;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_total'] += $mov_costounitario;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['valor_total'] += ($cant_actual * $mov_costopromedio);
		    	$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] = $cant_actual;
		    	$anteriores[$mov_almacen][$art_codigo]['unit_anterior'] = $mov_costounitario;
	
			//} else if($tipovista == "R" && $tran_codigo!= "12" && $tran_codigo!= "VM"){
			} else if($tipovista == "R" && $tran_codigo!= "VM"){

				$seriedocu		= substr($a[6], 0, 4);
				$numdocu		= substr($a[6], -8);
			    $mov_tipdocuref		= $a['mov_tipdocuref'];

				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_fecha'] = $mov_fecha;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipodocu'] = $tipodocu;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['seriedocu'] = $seriedocu;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['numdocu'] = $numdocu;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_numero'] = $mov_numero;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['codigo_tipo_tansa'] = $tran_codigo;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_almadestino'] = $mov_almadestino;

				if ($tran_codigo == "18" && $mov_cantidad < 0)//REGULARIZACION CON CANTIDA NEGATIVA < 0 
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[80];//SALIDA POR IDENTIFICACIÓN ERRONEA
				else
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];

				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipoopera'] = $tipoopera;

		   		if ($mov_naturaleza < 3) {
					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_entrada'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_entrada'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;
				} else {
					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_salida'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_salida'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
				}

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_docurefe'] = $mov_docurefe;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_tipdocuref'] = $mov_tipdocuref;

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_anterior'] = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_ant'] = $anteriores[$mov_almacen][$art_codigo]['unit_anterior'];

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual; //AQUI ESTA EL PROBLEMA
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio; //AQUI ESTA EL PROBLEMA
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual * $mov_costopromedio; //AQUI ESTA EL PROBLEMA

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['sNombreEntidad'] = $sNombreEntidad;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['sDocumentoLiquidado'] = $sDocumentoLiquidado;

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cantidad_total'] += $cant_actual;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_total'] += $mov_costounitario;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['valor_total'] += ($cant_actual * $mov_costopromedio);
		    	$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] = $cant_actual;
		    	$anteriores[$mov_almacen][$art_codigo]['unit_anterior'] = $mov_costounitario;
			}
		}

		foreach ($resultado['almacenes'] as $ka => $va) {
			foreach ($va['articulos'] as $kb => $vb) {
				if (!isset($vb['movimientos']))
					unset($resultado['almacenes'][$ka]['articulos'][$kb]);
	    	}
		}
		// echo "<script>console.log('" . json_encode($resultado) . "')</script>";
		// die();
		return $resultado;
	}

	function movialma($desde, $hasta, $art_desde, $estacion, $linea) {
        	global $sqlca;

        	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
        	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

		$sql="
			SELECT
				inv.art_codigo as codigo
			FROM 
				inv_movialma inv 
			    	LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo)  
			    	LEFT JOIN inv_tipotransa tran ON (trim(tran.tran_codigo)= trim(inv.tran_codigo))  
			    	LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='08' AND lpad(inv.mov_tipdocuref,6,'0')=tab.tab_elemento)
			WHERE
				inv.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59'";

		if (trim($art_desde) != "")
			$sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

		if (trim($linea) != "")
			$sql .= "AND art.art_linea='$linea'  ";

		if ($estacion != "TODAS")
			$sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

		if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['codigo'] = $a[0];
		}

		return $resultado;

	}

    function saldosProductos($desde, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s");

        if ($mes == 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }

        if (strlen($mes) == 1)
            $mes = "0" . $mes;

        $sql = "SELECT
			    	sa.stk_almacen as almacen,
			    	sa.art_codigo as codigo,
			    	sa.stk_stock" . $mes . " as stock,
			    	sa.stk_costo" . $mes . " as costo,
				round (sa.stk_stock" . $mes . " * sa.stk_costo" . $mes . ",4) total
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
			WHERE
				sa.stk_stock" . $mes . " > 0
				AND sa.stk_periodo='$ano' ";

        if (trim($art_desde) != "")
            $sql .= "AND sa.art_codigo='$art_desde' ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='$linea' ";

        if ($estacion != "TODAS")
            $sql .= " AND sa.stk_almacen='$estacion' ";

        $sql .= "ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;";

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado[$i]['almacen'] = $a[0];
            $resultado[$i]['codigo'] = $a[1];
            $resultado[$i]['stock'] = $a[2];
            $resultado[$i]['costo'] = $a[3];
            $resultado[$i]['total'] = $a[4];
        }

        return $resultado;
    }

    function saldoInicial($desde, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s");

        if ($mes == 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }

        if (strlen($mes) == 1)
            $mes = "0" . $mes;

        $sql = "
SELECT
 sa.stk_almacen,
 sa.art_codigo,
 sa.stk_stock" . $mes . ",
 sa.stk_costo" . $mes . ",
 sa.stk_periodo
FROM
 inv_saldoalma sa 
 LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
WHERE
 sa.stk_periodo='" . $ano . "'
		";

        if (trim($art_desde) != "")
            $sql .= "AND sa.art_codigo='" . $art_desde . "' ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='" . $linea . "' ";

        if ($estacion != "TODAS")
            $sql .= " AND sa.stk_almacen='" . $estacion. "' ";

        $sql .= "
ORDER BY
 sa.stk_periodo,
 sa.stk_almacen,
 sa.art_codigo;
		";

        //echo "\n\nSaldo Inicial:".$sql;

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $stk_almacen = trim($a[0]);
            $art_codigo = trim($a[1]);
            $stk_stock = $a[2];
            $stk_costo = $a[3];

            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_stock'] = $stk_stock;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costototal'] = $stk_stock * $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['cod_CUO'] = '' . $stk_almacen . '' . $art_codigo . '' . $mes;
        }

        if ($mes == 12) {
            $mes = 1;
            $ano++;
        } else {
            $mes++;
        }

        if ($dia > 1) {
            $sql = "
SELECT
 inv.mov_cantidad,
 inv.mov_costototal,
 inv.mov_costopromedio,
 inv.mov_costounitario,
 inv.mov_naturaleza,
 inv.mov_almacen,
 inv.art_codigo
FROM
 inv_movialma inv 
 LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo) 
WHERE
 inv.mov_fecha BETWEEN '" . ($ano . "-" . $mes . "-01") . " 00:00:00' AND '" . ($ano . "-" . $mes . "-" . ($dia - 1)) . " 23:59:59' ";

            if (trim($art_desde) != "")
                $sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

            if (trim($linea) != "")
                $sql .= "AND art.art_linea='$linea'  ";

            if ($estacion != "TODAS")
                $sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

            $sql .= "
ORDER BY
 inv.mov_almacen,
 inv.art_codigo,
 inv.mov_fecha;
			";

            //echo "\n\nSaldo Inicial 2: ".$sql;

            if ($sqlca->query($sql) < 0)
                return $resultado;

            for ($i = 0; $i < $sqlca->numrows(); $i++) {
                $a = $sqlca->fetchRow();

                $mov_cantidad = $a[0];
                $mov_costototal = $a[1];
                $mov_costopromedio = $a[2];
                $mov_costounitario = $a[3];
                $mov_naturaleza = $a[4];
                $mov_almacen = trim($a[5]);
                $art_codigo = trim($a[6]);

                if ($mov_naturaleza < 3) {
                    $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] += $mov_cantidad;
                    $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
                } else {
                    $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] -= $mov_cantidad;
                }
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $mov_costopromedio;
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costototal'] = $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] * $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'];

                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['cod_CUO'] = '' . $mov_almacen . '' . $art_codigo . '' . $mes;
            }
        }
        return $resultado;
    }

    function obtenerEstaciones() {
        global $sqlca;

        $sql = "SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_almacen;";
        if ($sqlca->query($sql, "_estaciones") < 0)
            return null;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
            $array = $sqlca->fetchRow("_estaciones");
            $resultado[$array[0]] = $array[0] . " - " . $array[1];
        }

        $resultado['TODAS'] = "Todas las estaciones";
        return $resultado;
    }

    public static function obtenerDescripcionAlmacen($codigo) {
        global $sqlca;

        $sql = "SELECT trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_almacen='$codigo';";
        if ($sqlca->query($sql, "_almacenes") < 0)
            return null;

        $a = $sqlca->fetchRow("_almacenes");
        return $a[0];
    }

    function obtenerTiposFormularios() {
        global $sqlca;

        $sql = "SELECT  trim(tran_codigo) as tran_codigo,trim(format_sunat) as tran_descripcion FROM inv_tipotransa ORDER BY tran_codigo;";
        if ($sqlca->query($sql, "_formularios") < 0)
            return null;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
            $array = $sqlca->fetchRow("_formularios");
            $resultado[$array[0]] = $array[1];
            // $resultado[$array[0]] = $array[0] . " - " . $array[1];
        }

        $resultado['TODOS'] = "Todos los tipos";
        return $resultado;
    }

    function obtenerProveedor($codigo) {
        global $sqlca;

        $sql = "SELECT pro_razsocial FROM int_proveedores WHERE pro_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_proveedor") < 0)
            return null;

        $a = $sqlca->fetchRow("_proveedor");
        return $a[0];
    }

    function obtenerCliente($codigo) {
        global $sqlca;

        $sql = "SELECT cli_razsocial FROM int_clientes WHERE cli_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_cliente") < 0)
            return null;

        $a = $sqlca->fetchRow("_cliente");
        return $a[0];
    }

    public static function obtenerDescripcion($codigo) {
        global $sqlca;

        $sql = "SELECT art_descripcion FROM int_articulos WHERE art_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_articulo") < 0)
            return null;

        $a = $sqlca->fetchRow("_articulo");
        return $a[0];
    }

    function datosEmpresa() {
        global $sqlca;

        $sql = "SELECT p1.par_valor, p2.par_valor, p3.par_valor FROM int_parametros p1, int_parametros p2, int_parametros p3 WHERE p1.par_nombre='razsocial' and p2.par_nombre='ruc' and p3.par_nombre='dires';";
        if ($sqlca->query($sql) < 0)
            return null;

        $res = Array();
        $a = $sqlca->fetchRow();
        $res['razsocial'] = $a[0];
        $res['ruc'] = $a[1];
        $res['direccion'] = $a[2];

        return $res;
    }

	function anexo_sunat() {
		global $sqlca;

		$sql = "SELECT par_valor FROM int_parametros  WHERE par_nombre='anexo_sunat';";
		if ($sqlca->query($sql) < 0)
		    return "9999";

		$res = Array();
		$a = $sqlca->fetchRow();
		$anexo = "9999";
		if (!empty($a['par_valor'])) {
		    $anexo = $a['par_valor'];
		}


		return $anexo;
	}

    function unidadMedida($codigo) {
        global $sqlca;

        $sql = "SELECT
                                tab_car_03 ||'-'|| tab_car_04
			FROM 
				int_tabla_general tab 
				LEFT JOIN int_articulos art ON (tab.tab_tabla='34' AND trim(tab.tab_elemento)=trim(art.art_unidad)) 
			WHERE 
				trim(art.art_codigo)='" . trim($codigo) . "';";

        if ($sqlca->query($sql) < 0)
            return null;
        $a = $sqlca->fetchRow();

        return explode("-", $a[0]);
    }

    public static function unidadMedidaExcel($codigo) {
        global $sqlca;

        $sql = "SELECT
                                tab_car_03
			FROM 
				int_tabla_general tab 
				LEFT JOIN int_articulos art ON (tab.tab_tabla='34' AND trim(tab.tab_elemento)=trim(art.art_unidad)) 
			WHERE 
				trim(art.art_codigo)='" . trim($codigo) . "';";

        if ($sqlca->query($sql) < 0)
            return null;
        $a = $sqlca->fetchRow();
        return $a[0];
    }


}

