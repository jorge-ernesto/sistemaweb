<?php

class RegistroVentasFEModel extends Model {

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}
    
    	function SearchTickets($almacen, $fdesde, $fhasta, $type) {
		global $sqlca;

		$y = substr($fdesde, 6, 4);
		$m = substr($fdesde, 3, 2);

		$postrans = "pos_trans".$y.$m;

		$cond 	= "";
		$cond2 	= "";

		if($type == "C"){
			//$cond 	= "AND t.tipo 	= 'C'";
			$cond 	= "AND tipo 	= 'C'";
			$cond2 	= "AND pt.tipo	= 'C'";
			$cond3 	= "AND tipo 	= 'C'";
		}elseif($type == "M"){
			//$cond 	= "AND t.tipo 	= 'M'";
			$cond 	= "AND tipo 	= 'M'";
			$cond2 	= "AND pt.tipo 	= 'M'";
			$cond3 	= "AND tipo 	= 'M'";
		}
	
		/*$sql = "
			SELECT
				'12' as tipo,
				SUBSTR(TRIM(t.usr), 0, 5) as serie,
				MIN(SUBSTR(TRIM(t.usr), 6)) ||' - '|| MAX(SUBSTR(TRIM(t.usr), 6)) as rango,
				TO_CHAR(t.dia, 'dd/mm/YYYY') as femision,
				COUNT(t.trans) as cantidad,
				(SELECT
					COUNT(*)
				FROM
					$postrans pt
				WHERE
					pt.importe	= 0
					AND pt.cantidad	= 0
					AND pt.precio	= 0
					AND pt.igv	= 0
					AND pt.soles_km	= 0
					AND pt.caja	= t.caja
					AND pt.dia	= t.dia
					AND pt.trans BETWEEN MIN(t.trans) AND MAX(t.trans)
					AND pt.usr != ''
				) as anulado,
				SUM(allmonto.nubi) as vbruto,
				'0.00' as dscto,
				SUM(allmonto.nubi) as vventa,
				SUM(allmonto.nuigv) as igv,
				SUM(allmonto.nutotal) as total,
				'TICKETS DE VENTAS' as desc
			FROM
				$postrans t
				LEFT JOIN (
						SELECT
							dia,
							caja,
							trans,
							codigo,
							ROUND(SUM(importe - igv), 2) as nubi,
							ROUND(SUM(igv), 2) as nuigv,
							ROUND(SUM(importe), 2) as nutotal,
							usr
						FROM
							$postrans
						WHERE
							DATE(dia) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
							AND es = '$almacen'
							AND td IN ('B', 'F')
							AND usr != ''
							$cond3
						GROUP BY	
							dia,
							caja,
							trans,
							codigo,
							usr
				) AS allmonto ON (allmonto.dia = t.dia AND allmonto.caja = t.caja AND allmonto.trans = t.trans AND allmonto.codigo = t.codigo AND allmonto.usr=t.usr)
			WHERE
				DATE(t.dia) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
				AND t.es = '$almacen'
				AND t.td IN ('B', 'F')
				AND t.usr != ''
				$cond
			GROUP BY
				t.dia,
				t.caja,
				SUBSTR(TRIM(t.usr), 0, 5)
			ORDER BY
				t.dia,
				t.caja,
				MIN(t.trans);
		";*/

		/*$sql = "SELECT
 '12' as tipo,
 SUBSTR(TRIM(t.usr), 0, 5) as serie,
 MIN(SUBSTR(TRIM(t.usr), 6)) ||' - '|| MAX(SUBSTR(TRIM(t.usr), 6)) as rango,
 TO_CHAR(t.dia, 'dd/mm/YYYY') as femision,
 COUNT(t.trans) as cantidad,
 (SELECT
 COUNT(*)
FROM
 $postrans pt
WHERE
 pt.importe = 0
 AND pt.cantidad = 0
 AND pt.precio = 0
 AND pt.igv = 0
 AND pt.soles_km = 0
 AND pt.caja = t.caja
 AND pt.dia = t.dia
 AND pt.trans BETWEEN MIN(t.trans) AND MAX(t.trans)
 AND pt.usr != ''
) as anulado,
SUM(t.nubi) as vbruto,
'0.00' as dscto,
SUM(t.nubi) as vventa,
SUM(t.nuigv) as igv,
SUM(t.nutotal) as total,
'TICKETS DE VENTAS' as desc
, -1 status,
TO_CHAR(t.dia, 'YYYYmmdd') as timecop
,t.td as td
,t.tm as tm

FROM (
SELECT
 dia,
 caja,
 trans,
 codigo,
 ROUND(SUM(importe - igv), 2) as nubi,
 ROUND(SUM(igv), 2) as nuigv,
 ROUND(SUM(importe), 2) as nutotal
 ,usr
 ,td
 ,tm
FROM
    $postrans
WHERE
 DATE(dia) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
 AND es = '$almacen'
 AND td IN ('B', 'F')
 AND usr != ''
 $cond
 --AND usr = 'F001-00002905'

GROUP BY
 dia,
 caja,
 trans,
 codigo
 ,td
 ,tm
 ,usr
) t
GROUP BY
 t.dia,
 t.caja,
 SUBSTR(TRIM(t.usr), 0, 5)
 ,t.td
 ,t.tm
 ORDER BY t.dia,
 t.caja,
 MIN(t.trans);
";
*/

$sql = "
SELECT
 TO_CHAR(t.dia, 'dd/mm/YYYY') as femision,
 CASE WHEN tm = 'V' THEN td ELSE 'N' END AS td,
 SUBSTR(TRIM(t.usr), 0, 5) as serie,
 MIN(SUBSTR(TRIM(t.usr), 6)) ||' - '|| MAX(SUBSTR(TRIM(t.usr), 6)) as rango,
 COUNT(*) as cantidad,
 COALESCE((SELECT COUNT(*) FROM  $postrans pt WHERE pt.importe = 0 AND pt.cantidad = 0 AND pt.precio = 0 AND pt.igv = 0 AND pt.soles_km = 0 AND pt.caja = t.caja AND pt.dia = t.dia AND pt.trans BETWEEN MIN(t.trans) AND MAX(t.trans) AND pt.usr != '' GROUP BY pt.caja),0) as anulado,
 ABS(ROUND(SUM(t.importe - igv), 2)) as nubi,
 ABS(ROUND(SUM(t.igv), 2)) as nuigv,
 ABS(ROUND(SUM(t.importe), 2)) as nutotal
 ,TO_CHAR(t.dia, 'YYYYmmdd') as timecop
FROM
 $postrans t
WHERE
 t.dia BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
 AND t.es = '$almacen' AND t.usr != ''
GROUP BY t.dia,2,3,t.caja
ORDER BY t.dia,2,3;
";
		//echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();	    

			/*$tipo 		= $a[0];
			$serie 		= $a[1];
			$rango 		= $a[2];
			$femision 	= $a[3];
			$cantidad	= $a[4];
			$anulado	= $a[5];
			$vbruto	 	= $a[6];
			$dscto	 	= $a[7];
			$vventa	 	= $a[8];
			$igv		= $a[9];
			$total	 	= $a[10];
			$desc	 	= $a[11];

			$enviados = $a[12];
			$timecop = $a[13];

			$td = $a[14];
			$tm = $a[15];*/

			$tipo 		= $a[1];
			$serie 		= $a[2];
			$rango 		= $a[3];
			$femision 	= $a[0];
			$cantidad	= $a[4];
			$anulado	= $a[5];
			$vbruto	 	= $a[1] == 'N' ? -(float)$a[6] : (float)$a[6];
			$dscto	 	= 0;
			$vventa	 	= $a[1] == 'N' ? -(float)$a[6] : (float)$a[6];
			$igv		= $a[1] == 'N' ? -(float)$a[7] : (float)$a[7];
			$total	 	= $a[1] == 'N' ? -(float)$a[8] : (float)$a[8];
			$desc	 	= 'TICKETS DE VENTAS';

			$enviados = -1;
			$timecop = $a[9];

			/*$td = $a[14];
			$tm = $a[15];*/

			/* REGISTROS TICKETS */
			//['td'][$td]['tm'][$tm]
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['tipo'] 	= $tipo;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['serie'] 	= $serie;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['rango'] 	= $rango;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['femision'] = $femision;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['cantidad'] = $cantidad;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['anulado'] 	= $anulado;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vbruto'] 	= $vbruto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['dscto'] 	= $dscto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vventa'] 	= $vventa;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['igv'] 	= $igv;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['total'] 	= $total;

			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['enviados'] = $enviados;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['timecop'] = $timecop;


		    	/* Totales por SERIE */
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['cantidad']	+= $cantidad;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['anulado'] 	+= $anulado;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['vbruto'] 	+= $vbruto;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['dscto'] 		+= $dscto;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['vventa'] 	+= $vventa;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['igv'] 		+= $igv;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['total'] 		+= $total;

		    	/* Totales por TIPO */
		    	@$result['tipos'][$desc]['totales']['cantidad'] += $cantidad;
		    	@$result['tipos'][$desc]['totales']['anulado'] 	+= $anulado;
		    	@$result['tipos'][$desc]['totales']['vbruto'] 	+= $vbruto;
		    	@$result['tipos'][$desc]['totales']['dscto'] 	+= $dscto;
		    	@$result['tipos'][$desc]['totales']['vventa'] 	+= $vventa;
		    	@$result['tipos'][$desc]['totales']['igv'] 	+= $igv;
		    	@$result['tipos'][$desc]['totales']['total'] 	+= $total;

		}
	
		return $result;
    }

    	function SearchDocumentos($almacen, $fdesde, $fhasta, $type) {
		global $sqlca;
	
		$cond	= "";
		$cond2	= "";

		if($type == "C"){
			$cond 	="AND det.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			$cond2 	="AND tdet.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
		}elseif($type == "M"){
			$cond 	= "AND det.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			$cond2 	="AND tdet.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
		}

		$sql = "
			SELECT
				gen.tab_car_03 tipo,
				fall.ch_fac_seriedocumento as serie,
				MIN(fall.ch_fac_numerodocumento) || '-' || MAX(fall.ch_fac_numerodocumento) as rango,
				TO_CHAR(fall.dt_fac_fecha, 'dd/mm/YYYY') as femision,
				COUNT(fall.ch_fac_numerodocumento) as cantidad,
				(SELECT
					COUNT(*)
				FROM
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle tdet ON(t.cli_codigo = tdet.cli_codigo AND tdet.ch_fac_tipodocumento=t.ch_fac_tipodocumento AND tdet.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND tdet.ch_fac_numerodocumento=t.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gent ON(t.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				WHERE
					t.ch_fac_anulado 				= 'S'
					AND gent.tab_car_03 			= gen.tab_car_03
					AND t.ch_fac_seriedocumento 	= fall.ch_fac_seriedocumento
					AND t.dt_fac_fecha 				= fall.dt_fac_fecha
					AND t.ch_fac_numerodocumento BETWEEN MIN(fall.ch_fac_numerodocumento) AND MAX(fall.ch_fac_numerodocumento)
					$cond2
				) as anulado,
				SUM(allmonto.vbruto),
				SUM(allmonto.dscto),
				SUM(allmonto.vventa),
				SUM(allmonto.igv),
				SUM(allmonto.total),
				gen.tab_descripcion as desc
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 0 THEN 1 ELSE 0 END) AS _pendiente
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 1 THEN 1 ELSE 0 END) AS _completado
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 2 THEN 1 ELSE 0 END) AS _anulado
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 3 THEN 1 ELSE 0 END) AS _completado_enviado
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 4 THEN 1 ELSE 0 END) AS _completado_error
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 5 THEN 1 ELSE 0 END) AS _anulado_enviado
				, SUM(CASE WHEN fall.nu_fac_recargo3 = 6 THEN 1 ELSE 0 END) AS _anulado_error,
				TO_CHAR(fall.dt_fac_fecha, 'YYYYmmdd') as timecop
			FROM
				fac_ta_factura_cabecera fall
				LEFT JOIN fac_ta_factura_detalle det ON(fall.cli_codigo = det.cli_codigo AND det.ch_fac_tipodocumento=fall.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=fall.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=fall.ch_fac_numerodocumento)
				LEFT JOIN int_tabla_general as gen ON(fall.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				LEFT JOIN (
				SELECT
					gen.tab_car_03 tipo,
					fmonto.ch_fac_seriedocumento as serie,
					fmonto.dt_fac_fecha as femision, 
					fmonto.ch_fac_numerodocumento AS nudocumento,
					det.cli_codigo as nucodcliente,
					det.art_codigo as nucodproducto,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_importeneto) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_importeneto), 2) END) as vbruto,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_descuento1) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_descuento1), 2) END) as dscto,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_importeneto) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_importeneto), 2) END) as vventa,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_impuesto1) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_impuesto1), 2) END) as igv, 
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_valortotal) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_valortotal), 2) END) as total
				FROM
					fac_ta_factura_cabecera fmonto
					LEFT JOIN fac_ta_factura_detalle det ON(fmonto.cli_codigo = det.cli_codigo AND det.ch_fac_tipodocumento=fmonto.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=fmonto.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=fmonto.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gen ON(fmonto.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
					LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = fmonto.dt_fac_fecha)
				WHERE
					DATE(fmonto.dt_fac_fecha) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') and TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
					AND fmonto.ch_almacen = '$almacen'
					AND fmonto.ch_fac_tipodocumento != '45'
					AND (fmonto.ch_fac_seriedocumento like 'F%' OR fmonto.ch_fac_seriedocumento like 'B%')
					$cond
				GROUP BY
					tipo,
					serie,
					femision,
					nudocumento,
					nucodcliente,
					nucodproducto,
					fmonto.ch_fac_moneda
				ORDER BY
					tipo,
					serie,
					femision,
					nudocumento
				) AS allmonto ON (allmonto.nucodcliente = fall.cli_codigo AND allmonto.tipo = gen.tab_car_03 AND allmonto.serie = fall.ch_fac_seriedocumento AND allmonto.nudocumento = fall.ch_fac_numerodocumento AND allmonto.nucodproducto = det.art_codigo)
			WHERE
				DATE(fall.dt_fac_fecha) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') and TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
				AND fall.ch_almacen = '$almacen'
				AND fall.ch_fac_tipodocumento != '45'
				AND (fall.ch_fac_seriedocumento like 'F%' OR fall.ch_fac_seriedocumento like 'B%')
				$cond
			GROUP BY
				gen.tab_car_03,
				fall.ch_fac_seriedocumento,
				fall.dt_fac_fecha,
				gen.tab_descripcion
				--, fall.nu_fac_recargo3
				, fall.ch_fac_tipodocumento--agregado para agrupar por TD
			ORDER BY
				tipo,
				serie,
				fall.dt_fac_fecha,
				MIN(fall.ch_fac_numerodocumento);
		";

		//echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

	    	$a = $sqlca->fetchRow();		    

	    	$tipo 		= $a[0];
	    	$serie 		= $a[1];
	    	$rango 		= $a[2];
	    	$femision 	= $a[3];
	    	$cantidad	= $a[4];
	    	$anulado	= $a[5];

			$vbruto	 	= $a[6];
			$dscto	 	= $a[7];
			$vventa	 	= ($a[6] - $a[7]);
			$igv		= $a[9];
			$total	 	= $a[10];
			$desc	 	= $a[11];

			$_pendiente	 	= $a[12];
			$_completado	 	= $a[13];
			$_anulado	 	= $a[14];
			$_completado_enviado	 	= $a[15];
			$_completado_error	 	= $a[16];
			$_anulado_enviado	 	= $a[17];
			$_anulado_error	 	= $a[18];

			$timecop	 	= $a[19];

			$numeros = explode("-",$rango);

			$numero		= $numeros[0] - 1;//Le resto menos uno porque no contabiliza el primer documento
			$numeronew	= $numeros[1];

			$cantreal	= $numeronew - $numero;

			/* REGISTROS DOCUMENTOS MANUALES */
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['tipo'] 	= $tipo;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['serie'] 	= $serie;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['rango'] 	= $rango;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['femision'] = $femision;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['cantidad'] = $cantidad;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['cantreal'] = $cantreal;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['anulado'] 	= $anulado;

			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vbruto'] 	= $vbruto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['dscto'] 	= $dscto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vventa'] 	= $vventa;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['igv'] 		= $igv;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['total'] 	= $total;

			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_pendiente'] = $_pendiente;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_completado'] = $_completado;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_anulado'] = $_anulado;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_completado_enviado'] = $_completado_enviado;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_completado_error'] = $_completado_error;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_anulado_enviado'] = $_anulado_enviado;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['_anulado_error'] = $_anulado_error;


			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['timecop'] = $timecop;

	    	/* Totales por SERIE */
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['cantidad'] 	+= $cantidad;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['anulado'] 	+= $anulado;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['vbruto'] 	+= $vbruto;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['dscto'] 		+= $dscto;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['vventa'] 	+= $vventa;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['igv'] 		+= $igv;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['total'] 		+= $total;

	    	/* Totales por TIPO */
	    	@$result['tipos'][$desc]['totales']['cantidad'] += $cantidad;
	    	@$result['tipos'][$desc]['totales']['anulado'] 	+= $anulado;
	    	@$result['tipos'][$desc]['totales']['vbruto'] 	+= $vbruto;
	    	@$result['tipos'][$desc]['totales']['dscto'] 	+= $dscto;
	    	@$result['tipos'][$desc]['totales']['vventa'] 	+= $vventa;
	    	@$result['tipos'][$desc]['totales']['igv'] 		+= $igv;
	    	@$result['tipos'][$desc]['totales']['total'] 	+= $total;


	    	/* TOTAL GENERAL */
	    	if($tipo == '07'){
		    	@$result['totales']['vbruto_nc'] 	+= $vbruto;
		    	@$result['totales']['dscto_nc'] 	+= $dscto;
		    	@$result['totales']['vventa_nc'] 	+= $vventa;
		    	@$result['totales']['igv_nc'] 		+= $igv;
		    	@$result['totales']['total_nc'] 	+= $total;
		    	@$result['totales']['total'] 	-= $total ;
		    }else{
		    	@$result['totales']['cantidad'] += $cantidad;
		    	@$result['totales']['anulado'] 	+= $anulado;
		    	@$result['totales']['vbruto'] 	+= $vbruto;
		    	@$result['totales']['dscto'] 	+= $dscto;
		    	@$result['totales']['vventa'] 	+= $vventa;
		    	@$result['totales']['igv'] 		+= $igv;

		    	@$result['totales']['total'] 	+= $total ;
		    }

		}
	
		return $result;

   	}

	function getDocumentStatusByDateRange($params) {
		$return = array();
		global $sqlca;
		//$sql = "SELECT to_char(created, 'YYYYMMDD') as timecom, status FROM ebi_queue where created::DATE BETWEEN to_date('".$params['fdesde']."', 'DD/MM/YYYY') AND to_date('".$params['fhasta']."', 'DD/MM/YYYY');";
		$sql = "
		SELECT to_char(created, 'YYYYMMDD') AS timecop
,SUM(CASE WHEN status = 0 THEN
1
ELSE 0 END
) AS registred,
SUM(CASE WHEN status = 1 THEN
1
ELSE 0 END
) AS send,
SUM(CASE WHEN status = 2 THEN
1
ELSE 0 END
) AS invalid,
SUM(1) AS total

FROM ebi_queue WHERE created::DATE BETWEEN to_date('".$params['fdesde']." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') AND to_date('".$params['fhasta']." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')
AND optype = 2
GROUP BY to_char(created, 'YYYYMMDD')
, status
;
		";
		$sqlca->query($sql);
		$registered = 0;
		$send = 0;
		$sendError = 0;
		$total = 0;
		while ($row = $sqlca->fetchRow()) {
			$return[$row[0]] = $row;
		}

		return $return;
	}
}
