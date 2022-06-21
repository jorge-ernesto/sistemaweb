<?php
function CSVFromQuery($sql,$file,$headers) {
	global $sqlca;

	if ($sqlca->query($sql)<=0)
	//	return FALSE;

	$numrows = $sqlca->numrows();

	$fh = fopen($file,"w");
	if ($fh===FALSE)
		return FALSE;

	$line = "";

	foreach ($headers as $num => $val)
		$line .= (($num==0)?"":",") . $val;
	
	fwrite($fh,$line."\r\n");

	while ($reg = $sqlca->fetchRow()) {
		$line = "";
		for ($i=0;$i<(count($reg)/2);$i++)
			$line .= (($i==0)?"":",") . str_replace(",",",,",$reg[$i]);
		fwrite($fh,$line."\r\n");
	}

	fclose($fh);

	return TRUE;
}

class InterfaceModel extends Model {
	function ListadoAlmacenes($codigo) {
		global $sqlca;
		$cond = '';
		if ($codigo != "")
			$cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
		$query = "
SELECT
 ch_almacen
FROM
 inv_ta_almacenes
WHERE
 trim(ch_clase_almacen)='1'
 " . $cond . "
ORDER BY
 ch_almacen
";

		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();
		$numrows = $sqlca->numrows();

		$x = 0;
		while ($reg = $sqlca->fetchRow()) {
			if ($numrows>1) {
				if ($x < $numrows-1)
					$conc = ".";
				else
					$conc = "";
			}

			$listado[''.$codigo.''] .= $reg[0].$conc;
			$x++;
		}
		return $listado;
	}

	function interface_fn_opensoft_copetrol($FechaIni,$FechaFin,$CodAlmacen) {
		global $sqlca;

		$modulo 	= "TODOS";
		$FechaDiv 	= explode("/", $FechaIni);
		$FechaIni 	= $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans 	= "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$ZipFile 	= $CodAlmacen."del".$FechaDiv[2].$FechaDiv[1].$FechaDiv[0]."al";
		$FechaDiv 	= explode("/", $FechaFin);
		$FechaFin 	= $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$ZipFile 	.= $FechaDiv[2].$FechaDiv[1].$FechaDiv[0].".zip";

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1])!=$postrans)
			return "INVALID_DATE";

		if (strlen($FechaIni)<10 || strlen($FechaFin)<10)
			return "INDALID_DATE";

		$sql = "SELECT tipo FROM pos_cfg WHERE es='$CodAlmacen';";

		$sqlca->query($sql);

		$reg 			= $sqlca->fetchRow();

		$TipoAlmacen 	= $reg[0];

		$ExportDir 		= "/home/data/";

		if ($TipoAlmacen == "M" || $TipoAlmacen == "m") {
			$prefijo_tipo 	= "m-";
			$nombre_tipo 	= "Market";
		} else {
			$prefijo_tipo 	= "t-";
			$nombre_tipo 	= "Grifos";
		}

		if ($modulo == "TODOS" || $modulo == "VALES") {
			$sql = "
SELECT
 to_char(c.dt_fecha,'dd/mm/yyyy') AS FECHA,
 c.ch_sucursal AS SERIE,
 c.ch_documento AS NUMERO,
 c.ch_cliente AS CLIENTE,
 cli.cli_ruc AS RUC,
 cli.cli_razsocial AS NOMBRE,
 d.ch_articulo AS PRODUCTO,
 d.nu_cantidad AS CANTIDAD,
 CASE WHEN d.nu_importe > 0.00 AND d.nu_cantidad > 0.00 THEN round((d.nu_importe / d.nu_cantidad),3) ELSE 0.00 END AS PRECIO,
 d.nu_importe AS TOTAL,
 c.dt_fecha AS FECHA_CONSUMO,
 c.ch_placa AS PLACA,
 c.ch_glosa AS NOTA,
 c.ch_caja AS CAJA,
 c.ch_lado AS LADO,
 c.ch_turno AS TURNO,
 cli.cli_anticipo AS ANTICIPO,
 cli.cli_ndespacho_efectivo AS DESPACHO_EFECTIVO,
 p.ch_numeval AS NUMERO_MANUAL,
 VREF.id_origen AS REF_MANUAL,
 c.nu_odometro AS odometro,
 pf.nomusu AS chofer
FROM
 val_ta_detalle AS d
 LEFT JOIN val_ta_cabecera AS c ON (c.ch_documento = d.ch_documento AND c.dt_fecha = d.dt_fecha AND c.ch_sucursal = d.ch_sucursal)
 LEFT JOIN val_ta_complemento AS p ON (p.ch_documento = d.ch_documento AND p.dt_fecha = d.dt_fecha AND p.ch_sucursal = d.ch_sucursal)
 LEFT JOIN int_articulos AS art ON (d.ch_articulo = art.art_codigo)
 LEFT JOIN interface_equivalencia_producto AS eqp ON (art.art_codigo::char(13) = eqp.art_codigo::char(13))
 LEFT JOIN int_clientes AS cli ON (c.ch_cliente = cli.cli_codigo)
 LEFT JOIN (
 SELECT
  PTREF.id_trans,
  VTCOM.id_manual AS id_origen
 FROM
  (SELECT caja||'-'||trans AS id_trans, rendi_gln FROM " . $postrans . " WHERE tm='A' AND td='N' AND grupo!='D' AND dia::DATE BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "') AS PTREF
  JOIN (SELECT trans AS id_trans, caja AS id_caja FROM " . $postrans . " WHERE tm='V' AND td='N' AND grupo!='D' AND dia::DATE BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "') AS PTORIGEN
   ON (PTORIGEN.id_trans = PTREF.rendi_gln)
  JOIN (SELECT ch_documento AS id_trans, ch_numeval AS id_manual FROM val_ta_complemento WHERE dt_fecha BETWEEN '" . $FechaIni . "' AND '" . $FechaIni . "') AS VTCOM
   ON (VTCOM.id_trans = PTORIGEN.id_caja||'-'||PTORIGEN.id_trans)
  ) AS VREF ON (VREF.id_trans = c.ch_documento)
  LEFT JOIN pos_fptshe1 AS pf ON(pf.numtar = c.ch_tarjeta)
 WHERE
  c.dt_fecha BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
  AND c.ch_sucursal LIKE '%{$CodAlmacen}%';
			";

			$headers = Array (	0	=>	"FECHA",
						1	=>	"SERIE",
						2	=>	"NUMERO",
						3	=>	"CLIENTE",
						4	=>	"RUC",
						5	=>	"NOMBRE",
						6	=>	"PRODUCTO",
						7	=>	"CANTIDAD",
						8	=>	"PRECIO",
						9	=>	"TOTAL",
						10	=>	"FECHA_CONSUMO",
						11	=>	"PLACA",
						12	=>	"NOTA",
						13	=>	"CAJA",
						14	=>	"LADO",
						15	=>	"TURNO",
						16	=>	"ANTICIPO",
						17	=>	"DESPACHO_EFECTIVO",
						18	=>	"NUMERO_MANUAL",
						19	=>	"REF_MANUAL",
						20 => "ODOMETRO",
						21 => "CHOFER"
			);

			CSVFromQuery($sql,$ExportDir."vales.txt",$headers);
		}

		if ($modulo == "TODOS" || $modulo=="VENTAS" || $modulo=="VENTASC") {
			$sql = "
SELECT * FROM
 (SELECT
  FIRST(CLI.cli_ruc) AS NUMERO,
  FIRST(replace(CLI.cli_razsocial, ',', '\,')) AS NOMBRE_RAZON_SOCIAL,
  '116'::character AS TIPO,
  FIRST(CLI.cli_ruc) AS NUMERO,
  FIRST(CASE WHEN CLI.cli_tipo = 'AC' THEN '1' ELSE '0' END)::CHARACTER AS CLIENTE,
  FIRST(replace(CLI.cli_direccion, ',', '\,')) AS DIRECCION,
  FIRST(CLI.cli_contacto) AS TELEFONO
 FROM
  fac_ta_factura_cabecera AS FC
  JOIN int_clientes AS CLI ON(FC.cli_codigo = CLI.cli_codigo)
 WHERE
  FC.ch_fac_tipodocumento IN ('10', '11', '20', '35')
  AND FC.dt_fac_fecha BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
  AND '${CodAlmacen}' LIKE '%'||FC.ch_almacen||'%'
 GROUP BY
  CLI.cli_codigo) AS P
 UNION
 (SELECT
  FIRST(CLI.cli_ruc) AS NUMERO,
  FIRST(replace(CLI.cli_razsocial, ',', '\,')) AS NOMBRE_RAZON_SOCIAL,
  '116'::character AS TIPO,
  FIRST(CLI.cli_ruc) AS NUMERO,
  FIRST(CASE WHEN CLI.cli_tipo = 'AC' THEN '1' ELSE '0' END)::CHARACTER AS CLIENTE,
  FIRST(replace(CLI.cli_direccion, ',', '\,')) AS DIRECCION,
  FIRST(CLI.cli_contacto) AS TELEFONO
 FROM
  val_ta_cabecera AS VC
  JOIN int_clientes AS CLI ON(VC.ch_cliente = CLI.cli_codigo)
 WHERE
  DATE(VC.dt_fecha) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
  AND '${CodAlmacen}' LIKE '%'||VC.ch_sucursal||'%'
 GROUP BY
  CLI.cli_codigo)
 UNION
 (SELECT DISTINCT
  t.ruc as ruc, 
  replace(r.razsocial, ',', '\,') AS NOMBRE_RAZON_SOCIAL,
  '116'::character AS TIPO,
  t.ruc AS NUMERO,
  '1'::character AS CLIENTE,
  ''::character AS DIRECCION,
  ''::character AS TELEFONO
 FROM 
  " . $postrans . " AS t
  LEFT JOIN ruc AS r USING (ruc)
 WHERE
  DATE(t.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
  AND t.ruc != ''
  AND '${CodAlmacen}' LIKE '%'||t.es||'%'
);
			";
			
			$headers = Array (
				0	=>	"CODIGO",
				1	=>	"NOMBRE_RAZON_SOCIAL",
				2	=>	"TIPO",
				3	=>	"NUMERO",
				4	=>	"CLIENTE",
				5	=>	"DIRECCION",
				6	=>	"TELEFONO"
			);

			CSVFromQuery($sql,$ExportDir."maestro.txt",$headers);

			$sql = "	SELECT
						a.ch_sucursal AS ALMACEN,
						to_char(a.dt_fechamedicion,'dd/mm/yyyy') as FECHA,
						i.art_codigo  AS PRODUCTO,
						a.nu_medicion as VARILLAJE,
						a.ch_tanque as NROTANQUE
					FROM
						comb_ta_mediciondiaria a,
						comb_ta_tanques b,
						interface_equivalencia_producto i
					WHERE
						a.ch_tanque=b.ch_tanque
						AND a.ch_sucursal=b.ch_sucursal
						AND a.dt_fechamedicion BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||a.ch_sucursal||'%'  
						AND b.ch_codigocombustible=i.art_codigo;";

			$headers = Array (	0	=>	"ALMACEN",
						1	=>	"FECHA",
						2	=>	"PRODUCTO",
						3	=>	"VARILLAJE",
						4	=>	"NROTANQUE"
			);

			CSVFromQuery($sql,$ExportDir."varillaje.txt",$headers);

			$sql = "	SELECT
						to_char(c.dt_fechaparte,'dd/mm/yyyy') as FECHA,
						substring(ch_numeroparte,10,1) as TURNO,
						surt.ch_numerolado AS LADO,
						c.ch_surtidor AS MANGUERA,
						trim('GRIFERO') AS GRIFERO,
						i.art_codigo AS PRODUCTO,
						CASE
							WHEN c.nu_ventagalon<>0 THEN round(c.nu_ventavalor/c.nu_ventagalon,3)
							ELSE 0
						END AS PRECIO,
						c.nu_contometrofinalvalor AS CONTOMETROSALIDASOLES,
						c.nu_contometrofinalgalon AS CONTOMETROSALIDAGLNS,
						c.nu_ventavalor+c.nu_descuentos AS TOTALSALIDASOLES,
						c.nu_ventagalon AS TOTALSALIDAGLNS,
						c.nu_ventavalor+c.nu_descuentos AS TOTALCONTADOSOLES,
						c.nu_ventagalon AS TOTALCONTADOGLNS,
						0 AS TOTALCREDITOSOLES,
						0 AS TOTALCREDITOGLNS,
						CASE
							WHEN c.nu_ventagalon<>0 THEN round((nu_afericionveces_x_5*5)*(c.nu_ventavalor/c.nu_ventagalon),3)
							ELSE 0
						END AS TOTALAFERIMIENTOSOLES,
						round(nu_afericionveces_x_5*5,2) AS TOTALAFERIMIENTOGLNS
					FROM
						comb_ta_contometros c
						LEFT JOIN comb_ta_surtidores surt ON (c.ch_sucursal= surt.ch_sucursal and c.ch_surtidor=surt.ch_surtidor),
						interface_equivalencia_producto i
					WHERE
						dt_fechaparte BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||c.ch_sucursal||'%'
						AND c.ch_codigocombustible=i.art_codigo;";

			$headers = Array(
						0	=>	"FECHA",
						1	=>	"TURNO",
						3	=>	"LADO",
						4	=>	"MANGUERA",
						5	=>	"GRIFERO",
						6	=>	"PRODUCTO",
						7	=>	"PRECIO",
						8	=>	"CONTOMETROSALIDASOLES",
						9	=>	"CONTOMETROSALIDAGLNS",
						10	=>	"TOTALSALIDASOLES",
						11	=>	"TOTALSALIDAGLNS",
						12	=>	"TOTALCONTADOSOLES",
						13	=>	"TOTALCONTADOGLNS",
						14	=>	"TOTALCREDITOSOLES",
						15	=>	"TOTALCREDITOGLNS",
						16	=>	"TOTALAFERIMIENTOSOLES",
						17	=>	"TOTALAFERIMIENTOGLNS"
			);

			CSVFromQuery($sql,$ExportDir."parte-diario.txt",$headers);
		}   

		if ($modulo=="TODOS" || $modulo=="VENTAS" || $modulo=="VENTASM") {
			$sql = "
			SELECT
				ca.ch_fac_tipodocumento||trim(ca.ch_fac_seriedocumento)||trim(ca.ch_fac_numerodocumento) AS VENTA,
				(CASE WHEN ca.ch_fac_anulado = 'S' THEN '0' ELSE '1' END) AS SITUACION,
				ca.ch_fac_tipodocumento AS DOCUMENTO,
				trim(ca.ch_fac_seriedocumento) AS SERIE,
				LPAD(trim(ca.ch_fac_numerodocumento)::TEXT, 8, '0') AS NUMERO,
				to_char(ca.dt_fac_fecha,'dd/mm/yyyy') AS FECHA,
				ca.ch_fac_moneda AS MONEDA,
				trim('') AS OBSERVACIONES,
				ca.cli_codigo AS CLIENTE,
				ca.nu_fac_valorbruto AS SUBTOTAL,
				ca.nu_fac_descuento1 AS DESCUENTO,
				ca.nu_fac_valorbruto AS AFECTO,
				ca.nu_fac_impuesto1 AS IGV,
				ca.nu_fac_valortotal AS TOTAL,
				(CASE
					WHEN ch_fac_credito = 'N' AND ch_fac_anticipo = 'S' THEN 'ANTICIPO'
					WHEN ch_fac_credito = 'S' THEN 'CREDITO'
					WHEN ch_fac_credito = 'N' AND ch_fac_anticipo = 'N' THEN 'CONTADO'
				END) AS TIPO,
				(string_to_array(co.ch_fac_observacion2, '*'))[2] || '-' ||  LPAD((string_to_array(co.ch_fac_observacion2, '*'))[1]::TEXT, 8, '0') AS REFERENCIA,
				ca.nu_tipo_pago::INTEGER,
				(CASE WHEN ca.ch_fac_tiporecargo2='S' THEN ca.nu_fac_valortotal ELSE 0 END) AS EXONERADA,
				(CASE WHEN ca.ch_fac_tiporecargo2 IN('T', 'U', 'W') THEN ca.nu_fac_recargo1 ELSE 0 END) AS GRATUITA,
				(CASE WHEN ca.ch_fac_tiporecargo2='V' THEN ca.nu_fac_valortotal ELSE 0 END) AS INAFECTO,
				to_char(ca.fe_vencimiento, 'DD/MM/YYYY') AS fvencimiento,
				(CASE WHEN strpos(co.nu_fac_complemento_direccion, '*') > 0 THEN (string_to_array(co.nu_fac_complemento_direccion, '*'))[1] ELSE NULL END) AS NUMERO_CUENTA_DETRACCION, --requerimiento grupo-mandujano detracciones
				(CASE WHEN strpos(co.nu_fac_complemento_direccion, '*') > 0 THEN (string_to_array(co.nu_fac_complemento_direccion, '*'))[2] ELSE NULL END) AS IMPORTE_DETRACCION, --requerimiento grupo-mandujano detracciones
    			(CASE WHEN strpos(co.nu_fac_complemento_direccion, '*') > 0 THEN (string_to_array(co.nu_fac_complemento_direccion, '*'))[3] ELSE NULL END) AS PORCENTAJE_DETRACCION, --requerimiento grupo-mandujano detracciones
				(CASE WHEN strpos(co.nu_fac_complemento_direccion, '*') > 0 THEN (string_to_array(co.nu_fac_complemento_direccion, '*'))[4] ELSE NULL END) AS COD_BIENES_SERVICIOS, --requerimiento grupo-mandujano detracciones
				ca.ch_liquidacion AS LIQUIDACION
			FROM
				fac_ta_factura_cabecera AS ca
				LEFT JOIN fac_ta_factura_complemento AS co ON(ca.ch_fac_tipodocumento = co.ch_fac_tipodocumento AND ca.ch_fac_seriedocumento = co.ch_fac_seriedocumento AND ca.ch_fac_numerodocumento = co.ch_fac_numerodocumento)
			WHERE
				ca.dt_fac_fecha BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
				AND '${CodAlmacen}' LIKE '%'||ca.ch_almacen||'%'
				AND ca.ch_fac_tipodocumento <> '45'
			";

			$headers = Array(
						0	=>	"VENTA",
						1	=>	"SITUACION",
						2	=>	"DOCUMENTO",
						3	=>	"SERIE",
						4	=>	"NUMERO",
						5	=>	"FECHA",
						6	=>	"MONEDA",
						7	=>	"OBSERVACIONES",
						8	=>	"CLIENTE",
						9	=>	"SUBTOTAL",
						10	=>	"DESCUENTO",
						11	=>	"AFECTO",
						12	=>	"IGV",
						13	=>	"TOTAL",
						14	=>	"TIPO",
						15	=>	"REFERENCIA",
						16	=>	"FPAGO",//Se agregaron 3 campos nuevos - 10/08/2018
						17	=>	"EXONERADA",
						18	=>	"GRATUITA",
						19	=>	"INAFECTO",
						20  =>  "FECHA_VENCIMIENTO",
						21  =>  "NUMERO_CUENTA_DETRACCION",
						22  =>  "IMPORTE_DETRACCION",
						23  =>  "PORCENTAJE_DETRACCION",
						24  =>  "COD_BIENES_SERVICIOS",
						25  =>  "LIQUIDACION"
			);

			CSVFromQuery($sql,$ExportDir."venta.txt",$headers);

			// Facturas de oficina y liquidadas - 05/07/2019
// 			$sql = "
// SELECT
//  ca.ch_fac_tipodocumento||trim(ca.ch_fac_seriedocumento)||trim(ca.ch_fac_numerodocumento) AS VENTA,
//  (CASE WHEN ca.ch_fac_anulado = 'S' THEN '0' ELSE '1' END) AS SITUACION,
//  ca.ch_fac_tipodocumento AS DOCUMENTO,
//  trim(ca.ch_fac_seriedocumento) AS SERIE,
//  trim(ca.ch_fac_numerodocumento) AS NUMERO,
//  to_char(ca.dt_fac_fecha,'dd/mm/yyyy') AS FECHA,
//  ca.ch_fac_moneda AS MONEDA,
//  trim('') AS OBSERVACIONES,
//  ca.cli_codigo AS CLIENTE,
//  ca.nu_fac_valorbruto - ca.nu_fac_impuesto1 AS SUBTOTAL,
//  ca.nu_fac_descuento1 AS DESCUENTO,
//  ca.nu_fac_valorbruto AS AFECTO,
//  ca.nu_fac_impuesto1 AS IGV,
//  ca.nu_fac_valortotal AS TOTAL,
//  td.tab_descripcion AS TIPO,
//  (string_to_array(co.ch_fac_observacion2, '*'))[2] || '-' || (string_to_array(co.ch_fac_observacion2, '*'))[1] AS REFERENCIA,
//  ca.nu_tipo_pago::INTEGER,
//  (CASE WHEN ca.ch_fac_tiporecargo2='S' THEN ca.nu_fac_valortotal ELSE 0 END) AS EXONERADA,
//  (CASE WHEN ca.ch_fac_tiporecargo2 IN('T', 'U', 'W') THEN ca.nu_fac_recargo1 ELSE 0 END) AS GRATUITA,
//  (CASE WHEN ca.ch_fac_tiporecargo2='V' THEN ca.nu_fac_valortotal ELSE 0 END) AS INAFECTO,
//  to_char(ca.fe_vencimiento, 'DD/MM/YYYY') AS fvencimiento
// FROM
//  fac_ta_factura_cabecera AS ca
//  LEFT JOIN fac_ta_factura_complemento AS co ON(ca.ch_fac_tipodocumento = co.ch_fac_tipodocumento AND ca.ch_fac_seriedocumento = co.ch_fac_seriedocumento AND ca.ch_fac_numerodocumento = co.ch_fac_numerodocumento) 
//  INNER JOIN int_tabla_general as td on ( td.tab_elemento!='000000' AND ca.nu_tipo_pago = substring(td.tab_elemento, 5, 2) AND td.tab_tabla='05') 
// WHERE
//  ca.dt_fac_fecha BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
//  AND '${CodAlmacen}' LIKE '%'||ca.ch_almacen||'%'
//  AND ca.ch_fac_tipodocumento <> '45'
// 			";

// 			$headers = Array(
// 				0	=>	"VENTA",
// 				1	=>	"SITUACION",
// 				2	=>	"DOCUMENTO",
// 				3	=>	"SERIE",
// 				4	=>	"NUMERO",
// 				5	=>	"FECHA",
// 				6	=>	"MONEDA",
// 				7	=>	"OBSERVACIONES",
// 				8	=>	"CLIENTE",
// 				9	=>	"SUBTOTAL",
// 				10	=>	"DESCUENTO",
// 				11	=>	"AFECTO",
// 				12	=>	"IGV",
// 				13	=>	"TOTAL",
// 				14	=>	"TIPO",
// 				15	=>	"REFERENCIA",
// 				16	=>	"FPAGO",//Se agregaron 3 campos nuevos - 10/08/2018
// 				17	=>	"EXONERADA",
// 				18	=>	"GRATUITA",
// 				19	=>	"INAFECTO",
// 				20  =>  "FECHA_VENCIMIENTO"
// 			);

// 			CSVFromQuery($sql,$ExportDir."facturas_venta-cabecera.txt",$headers);

		// ./ Facturas de oficina y liquidadas (Se discrimina los tipos 45 - Ventas tickets)

			$sql = "	SELECT
						d.ch_fac_tipodocumento||trim(d.ch_fac_seriedocumento)||d.ch_fac_numerodocumento AS VENTA,
						l.tab_descripcion AS LINEA,
						d.art_codigo AS PRODUCTO,
						d.nu_fac_cantidad AS CANTIDAD,
						d.nu_fac_precio AS PRECIO,
						d.nu_fac_descuento1 AS DCTO,
						d.nu_fac_impuesto1  AS IGV,
						d.nu_fac_valortotal AS IMPORTE,
						c.ch_liquidacion AS LIQUIDACION
					FROM
						fac_ta_factura_detalle d,
						fac_ta_factura_cabecera c,
						int_articulos a,
						int_tabla_general l
					WHERE
						d.ch_fac_tipodocumento = c.ch_fac_tipodocumento
						AND d.ch_fac_seriedocumento = c.ch_fac_seriedocumento
						AND d.ch_fac_numerodocumento = c.ch_fac_numerodocumento
						AND d.cli_codigo = c.cli_codigo
						AND d.art_codigo=a.art_codigo
						AND l.tab_elemento=a.art_linea
						AND c.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||c.ch_almacen||'%'
						AND d.ch_fac_tipodocumento<>'45'
						AND l.tab_tabla = '20';";

			$headers = Array(
						0	=>	"VENTA",
						1	=>	"LINEA",
						2	=>	"PRODUCTO",
						3	=>	"CANTIDAD",
						4	=>	"PRECIO",
						5	=>	"DCTO",
						6	=>	"IGV",
						7	=>	"IMPORTE",
						8   =>  "LIQUIDACION"
			);

			CSVFromQuery($sql,$ExportDir."ventalinea.txt",$headers);

		// Facturas de oficina y liquidadas DETALLE - 05/07/2019
// 			$sql = "
// SELECT
//  d.ch_fac_tipodocumento||trim(d.ch_fac_seriedocumento)||d.ch_fac_numerodocumento AS VENTA,
//  l.tab_descripcion AS LINEA,
//  d.art_codigo AS PRODUCTO,
//  d.nu_fac_cantidad AS CANTIDAD,
//  CASE WHEN (c.ch_fac_tiporecargo2='' OR c.ch_fac_tiporecargo2=NULL OR c.ch_fac_tiporecargo2 IS NULL) THEN
//   round(d.nu_fac_precio / (SELECT 1 + (tab_num_01 * 0.01) FROM int_tabla_general where tab_tabla||tab_elemento = (SELECT par_valor FROM int_parametros WHERE par_nombre='igv actual')), 4)
//  ELSE
//   0.00
//  END AS VALOR_UNITARIO,
//  d.nu_fac_descuento1 AS DCTO,
//  CASE WHEN (c.ch_fac_tiporecargo2='' OR c.ch_fac_tiporecargo2=NULL OR c.ch_fac_tiporecargo2 IS NULL) THEN
//   round(c.nu_fac_valorbruto * (SELECT tab_num_01 * 0.01 FROM int_tabla_general where tab_tabla||tab_elemento = (SELECT par_valor FROM int_parametros WHERE par_nombre='igv actual')),2)
//  ELSE
//   0.00
//  END AS IGV,
//  CASE WHEN (c.ch_fac_tiporecargo2='' OR c.ch_fac_tiporecargo2=NULL OR c.ch_fac_tiporecargo2 IS NULL) THEN
//   c.nu_fac_valorbruto + round(c.nu_fac_valorbruto * (SELECT tab_num_01 * 0.01 FROM int_tabla_general where tab_tabla||tab_elemento = (SELECT par_valor FROM int_parametros WHERE par_nombre='igv actual')),2)
//  ELSE
//   c.nu_fac_valorbruto
//  END AS IMPORTE,
//  (CASE WHEN c.ch_fac_tiporecargo2='S' THEN c.nu_fac_valortotal ELSE 0 END) AS EXONERADA,
//  (CASE WHEN c.ch_fac_tiporecargo2 IN('T', 'U', 'W') THEN c.nu_fac_recargo1 ELSE 0 END) AS GRATUITA,
//  (CASE WHEN c.ch_fac_tiporecargo2='V' THEN c.nu_fac_valortotal ELSE 0 END) AS INAFECTO,
//  d.nu_fac_precio AS PRECIO,
//  c.nu_fac_valorbruto AS AFECTO 
// FROM
//  fac_ta_factura_detalle AS d,
//  fac_ta_factura_cabecera AS c,
//  int_articulos AS a,
//  int_tabla_general AS l
// WHERE
//  d.ch_fac_tipodocumento = c.ch_fac_tipodocumento
//  AND d.ch_fac_seriedocumento = c.ch_fac_seriedocumento
//  AND d.ch_fac_numerodocumento = c.ch_fac_numerodocumento
//  AND d.cli_codigo = c.cli_codigo
//  AND d.art_codigo=a.art_codigo
//  AND l.tab_elemento=a.art_linea
//  AND c.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
//  AND '${CodAlmacen}' LIKE '%'||c.ch_almacen||'%'
//  AND d.ch_fac_tipodocumento<>'45'
//  AND l.tab_tabla = '20';
// 			";

// 			$headers = Array(
// 				0	=>	"VENTA",
// 				1	=>	"LINEA",
// 				2	=>	"PRODUCTO",
// 				3	=>	"CANTIDAD",
// 				4	=>	"VALOR_UNITARIO",
// 				5	=>	"DCTO",
// 				6	=>	"IGV",
// 				7	=>	"IMPORTE",
// 				8	=>	"EXONERADA",//Se agregaron 4 campos nuevos - 07/08/2019
// 				9	=>	"GRATUITA",
// 				10	=>	"INAFECTO",
// 				11	=>	"PRECIO",
// 				12	=>	"AFECTO"
// 			);

// 			CSVFromQuery($sql,$ExportDir."facturas_venta-detalle.txt",$headers);

		// ./ Facturas de oficina y liquidadas DETALLE (Se discrimina los tipos 45 - Ventas tickets)

			$sql = "
				SELECT
					first(t.caja) AS CAJAREGISTRADORA,
					first(t.pump) AS LADO,
					first(z.nu_posz_z_numero) AS ZZ,
					first('1'::character) AS OP,
					first(c.nroserie) AS SERIE,
					t.trans AS TICKET,
					first(t.tm) AS TM,
					first(t.td) AS TIPO,
					first(t.ruc) AS CLIENTE,
					first('.'::character) AS NOMBRE,
					first(t.ruc) AS RUC,
					first(to_char(t.dia,'dd/mm/yyyy')) AS FECHASISTEMA,
					first(to_char(t.fecha,'dd/mm/yyyy HH24:MI:SS')) AS FECHAEMITIDA,
					sum(t.cantidad) AS CANTIDAD,
					sum(t.precio) AS PRECIO,
					sum(t.igv) AS IGV,
					sum(t.importe-t.igv) AS AFECTO,
					sum(t.importe) AS TOTAL,
					first(t.td) AS TIPODOC,
					first(t.fpago) AS FORMAPAGO,
					first(t.at ||' - '||int_tabla_general.tab_descripcion) AS TIPOTARJETA,
					first(t.dia) AS FECHASYS,
					first(a.art_codigo) AS PRODUCTO,
					first(t.turno) AS TURNO,
					first(t.text1) AS TARJETA,
					first(t.placa) AS PLACA,
					first(t.indexa) AS NUMERO_TARJETA_FIDE
				FROM
					$postrans AS t
					LEFT JOIN pos_z_cierres AS z
					 ON(t.es = z.ch_sucursal AND t.caja=z.ch_posz_pos AND t.dia::date = z.dt_posz_fecha_sistema AND t.turno = z.nu_posturno::varchar)
					LEFT JOIN pos_cfg AS c
					 ON(t.caja = c.pos)
					LEFT JOIN int_articulos AS a
					 ON(trim(t.codigo)=trim(a.art_codigo))
					LEFT JOIN int_tabla_general
					 ON(trim(t.at)=substring(int_tabla_general.tab_elemento,6,6) AND tab_tabla='95')
				WHERE
					t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
					AND '{$CodAlmacen}' LIKE '%'||t.es||'%'
					AND t.td IN ('F','B')
					AND t.usr = ''
				GROUP BY
					t.trans,
					t.codigo,
					t.caja
				ORDER BY
					CAJAREGISTRADORA,
					ZZ,
					t.trans;
			";

			$headers = Array(
						0	=>	"CAJAREGISTRADORA",
						1	=>	"LADO",
						2	=>	"ZZ",
						3	=>	"OP",
						4	=>	"SERIE",
						5	=>	"TICKET",
						6	=>	"TM",
						7	=>	"TIPO",
						8	=>	"CLIENTE",
						9	=>	"NOMBRE",
						10	=>	"RUC",
						11	=>	"FECHASISTEMA",
						12	=>	"FECHAEMITIDA",
						13	=>	"CANTIDAD",
						14	=>	"PRECIO",
						15	=>	"IGV",
						16	=>	"AFECTO",
						17	=>	"TOTAL",
						18	=>	"TIPODOC",
						19	=>	"FORMAPAGO",
						20	=>	"TIPOTARJETA",
						21	=>	"FECHA_SISTEMA",
						22	=>	"PRODUCTO",
						23	=>	"TURNO",
						24	=>	"TARJETA",
						25	=>	"PLACA",
						26	=>	"NUMERO_TARJETA_FIDELIZACION"
			);

			CSVFromQuery($sql,$ExportDir."zzventatickets.txt",$headers);

			$sql = "
			SELECT
				first(t.caja) AS CAJAREGISTRADORA,
				first(t.pump) AS LADO,
				first(z.nu_posz_z_numero) AS ZZ,
				first('1'::character) AS OP,
				SUBSTR(TRIM(t.usr), 0, 5) SERIE,
				LPAD(SUBSTR(TRIM(t.usr), 6)::TEXT, 8, '0') NUMERO,
				first(t.tm) AS TM,
				(CASE
					WHEN first(t.tm) = 'V' AND first(t.td) = 'F' THEN '01'
					WHEN first(t.tm) = 'V' AND first(t.td) = 'B' THEN '03'
					WHEN first(t.tm) = 'D' THEN '07'
					WHEN first(t.tm) = 'A' THEN '07'
				END) AS TIPO,
				first(t.ruc) AS CLIENTE,
				first('.'::character) AS NOMBRE,
				first(t.ruc) AS RUC,
				first(to_char(t.dia,'dd/mm/yyyy')) AS FECHASISTEMA,
				first(to_char(t.fecha,'dd/mm/yyyy HH24:MI:SS')) AS FECHAEMITIDA,
				sum(t.cantidad) AS CANTIDAD,
				sum(t.precio) AS PRECIO,
				sum(t.igv) AS IGV,
				sum(t.importe-t.igv) AS AFECTO,
				sum(t.importe) AS TOTAL,
				first(t.td) AS TIPODOC,
				first(t.fpago) AS FORMAPAGO,
				first(t.at ||' - '||int_tabla_general.tab_descripcion) AS TIPOTARJETA,
				first(t.dia) AS FECHASYS,
				first(a.art_codigo) AS PRODUCTO,
				first(t.turno) AS TURNO,
				first(t.text1) AS TARJETA,
				first(t.placa) AS PLACA,
				SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) as REFERENCIA,
				first(t.indexa) AS NUMERO_TARJETA_FIDE
			FROM
				$postrans AS t
				LEFT JOIN pos_z_cierres AS z
				 ON(t.es = z.ch_sucursal AND t.caja=z.ch_posz_pos AND t.dia::date = z.dt_posz_fecha_sistema AND t.turno = z.nu_posturno::varchar)
				LEFT JOIN pos_cfg AS c
				 ON(t.caja = c.pos)
				LEFT JOIN int_articulos AS a
				 ON(trim(t.codigo)=trim(a.art_codigo))
				LEFT JOIN int_tabla_general
				 ON(trim(t.at)=substring(int_tabla_general.tab_elemento,6,6) AND tab_tabla='95')
				LEFT JOIN
				(SELECT
				venta_tickes.feoriginal as fe1,
				venta_tickes.ticketextorno as fe2,
				venta_tickes.feextorno as fe3
				FROM
				(SELECT 
				extorno.origen as cadenaorigen,
				p.trans as ticketoriginal,
				extorno.trans1 as ticketextorno,
				p.usr as feoriginal,
				extorno.usr as feextorno,
				p.fecha as fefecha
				FROM
				$postrans p
				INNER JOIN (
					SELECT 
						(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
						(dia || caja || trans) as origen,
						trans as trans1,
						usr
					FROM
						$postrans
					WHERE
						tm = 'A'
						AND td IN ('B','F')
						AND usr != ''
					) as extorno ON (dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
					AND td IN ('B','F')
					AND tm = 'V'
					AND p.trans < extorno.trans1
					AND p.usr != ''
				) AS venta_tickes
				GROUP BY
				venta_tickes.cadenaorigen,
			    venta_tickes.ticketoriginal,
				venta_tickes.ticketextorno,
				venta_tickes.feoriginal,
				venta_tickes.feextorno,
				venta_tickes.fefecha
				) AS ext on ( ext.fe2 = t.trans and ext.fe3 = t.usr)
			WHERE
				t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
				AND '{$CodAlmacen}' LIKE '%'||t.es||'%'
				AND t.td IN ('F','B')
				AND t.usr != ''
			GROUP BY
				t.usr,
				t.codigo,
				t.caja,
				ext.fe1
			ORDER BY
				CAJAREGISTRADORA,
				ZZ,
				t.usr;
			";

			$headers = Array(
				0	=>	"CAJAREGISTRADORA",
				1	=>	"LADO",
				2	=>	"ZZ",
				3	=>	"OP",
				4	=>	"SERIE",
				5	=>	"NUMERO",
				6	=>	"TM",
				7	=>	"TIPO",
				8	=>	"CLIENTE",
				9	=>	"NOMBRE",
				10	=>	"RUC",
				11	=>	"FECHASISTEMA",
				12	=>	"FECHAEMITIDA",
				13	=>	"CANTIDAD",
				14	=>	"PRECIO",
				15	=>	"IGV",
				16	=>	"AFECTO",
				17	=>	"TOTAL",
				18	=>	"TIPODOC",
				19	=>	"FORMAPAGO",
				20	=>	"TIPOTARJETA",
				21	=>	"FECHA_SISTEMA",
				22	=>	"PRODUCTO",
				23	=>	"TURNO",
				24	=>	"TARJETA",
				25	=>	"PLACA",
				26	=>	"REFERENCIA",
				27 	=>  "NUMERO_TARJETA_FIDELIZACION"
			);

			CSVFromQuery($sql,$ExportDir."zzventaplaya.txt",$headers);

			$sql = "	SELECT
						ch_posz_pos AS CAJAREGISTRADORA,
						nu_posz_z_numero AS ZZ,
						'$nombre_tipo' AS ORIGEN,
						'$nombre_tipo' AS VENDEDOR,
						to_char(dt_posz_fecha_apertura,'dd/mm/yyyy') AS APERTURAFECHA,
						to_char(dt_posz_fecha_apertura,'HH24:MI:SS') AS APERTURAHORA,
						to_char(dt_posz_fecha_cierre,'dd/mm/yyyy') AS CIERREFECHA,
						to_char(dt_posz_fecha_cierre,'HH24:MI:SS') AS CIERREHORA,
						nu_posz_tran_inicial AS OBSERVACION,
						nu_posz_b_transas AS TOTALTICKETSBOLETAS,
						nu_posz_b_total-nu_posz_b_impuesto AS AFECTOBOLETAS,
						nu_posz_b_impuesto AS IGVBOLETAS,
						nu_posz_b_total    AS TOTALBOLETAS,
						nu_posz_f_transas AS TOTALTICKETSFACTURAS,
						nu_posz_f_total-nu_posz_f_impuesto AS AFECTOFACTURAS,
						nu_posz_f_impuesto AS IGVFACTURAS,
						nu_posz_f_total    AS TOTALFACTURAS,
						nu_posz_t_transas AS TOTALTICKETS,
						nu_posz_t_total-nu_posz_t_impuesto AS AFECTO,
						nu_posz_t_impuesto AS IGV,
						nu_posz_t_total    AS TOTAL,
						nu_posz_tipo_cambio AS TC
					FROM
						pos_z_cierres
					WHERE
						dt_posz_fecha_sistema BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
						AND '${CodAlmacen}' LIKE '%'||ch_sucursal||'%';";

			//var_dump($sql);
			//exit;

			$headers = Array(
						0	=>	"CAJAREGISTRADORA",
						1	=>	"ZZ",
						2	=>	"ORIGEN",
						3	=>	"VENDEDOR",
						4	=>	"APERTURAFECHA",
						5	=>	"APERTURAHORA",
						6	=>	"CIERREFECHA",
						7	=>	"CIERREHORA",
						8	=>	"OBSERVACION",
						9	=>	"TOTALTICKETSBOLETAS",
						10	=>	"AFECTOBOLETAS",
						11	=>	"IGVBOLETAS",
						12	=>	"TOTALBOLETAS",
						13	=>	"TOTALTICKETSFACTURAS",
						14	=>	"AFECTOFACTURAS",
						15	=>	"IGVFACTURAS",
						16	=>	"TOTALFACTURAS",
						17	=>	"TOTALTICKETS",
						18	=>	"AFECTO",
						19	=>	"IGV",
						20	=>	"TOTAL",
						21	=>	"TC"
			);

			CSVFromQuery($sql,$ExportDir."zz.txt",$headers);

			$sql = "	SELECT
						max(t.caja) AS CAJAREGISTRADORA,
						z.nu_posz_z_numero AS ZZ,
						max(to_char(t.dia,'dd/mm/yyyy')) AS FECHA,
						max(CASE
							WHEN i.codigo_iridium IS NOT NULL THEN i.art_codigo
							ELSE t.codigo
						END) AS PRODUCTO,
						max(CASE
							WHEN i.codigo_iridium IS NOT NULL THEN i.codigo_iridium
							ELSE a.art_descbreve
						END) AS DESCRIPCION,
						sum(t.cantidad) AS TCANTIDAD,
						max(t.precio) AS PPRECIO,
						sum(t.importe-t.igv) AS IMPORTESINIGV,
						sum(t.igv) AS IMPORTEIGV,
						sum(t.importe) AS TIMPORTE,
						FIRST(a.art_linea) AS LINEA,
						FIRST(l.tab_descripcion) AS DESCLINEA
					FROM
						$postrans t
						LEFT JOIN pos_z_cierres z ON (t.caja=z.ch_posz_pos AND t.dia::date = z.dt_posz_fecha_sistema AND t.turno = z.nu_posturno::varchar)
						LEFT JOIN pos_cfg c ON t.caja=c.pos
						LEFT JOIN interface_equivalencia_producto i ON trim(t.codigo)=trim(i.art_codigo)
						LEFT JOIN int_articulos a ON trim(t.codigo)=trim(a.art_codigo)
						LEFT JOIN int_tabla_general l ON (l.tab_tabla='20' AND (a.art_linea = l.tab_elemento OR a.art_linea = substr(l.tab_elemento,5,2)))
					WHERE
						t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
						AND '${CodAlmacen}' LIKE '%'||t.es||'%'
					GROUP BY
						(CASE
							WHEN i.codigo_iridium IS NOT NULL THEN i.codigo_iridium
							ELSE t.codigo
						END),
						ZZ,
						t.caja,
						z.nu_posz_z_numero
					ORDER BY
						ZZ;";

			$headers = Array(
						0	=>	"CAJAREGISTRADORA",
						1	=>	"ZZ",
						2	=>	"FECHA",
						3	=>	"PRODUCTO",
						4	=>	"DESCRIPCION",
						5	=>	"TCANTIDAD",
						6	=>	"PPRECIO",
						7	=>	"IMPORTESINIGV",
						8	=>	"IMPORTEIGV",
						9	=>	"TIMPORTE",
						10	=>	"LINEA",
						11	=>	"DESCLINEA"
			);

			CSVFromQuery($sql,$ExportDir."producto.txt",$headers);

			$sql = "	SELECT 	
						pdd.ch_valida as valida,
						pdd.dt_dia as dia,
						pdd.ch_posturno as turno,
						TRIM(pdd.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
						to_char(pdd.dt_fecha,'DD/MM/YYYY HH24:MI:SS') as fecha,
						pdd.ch_numero_correl as seq,
						pdd.ch_numero_documento as num,
						pdd.ch_moneda as moneda,
						pdd.nu_tipo_cambio as cambio,
						CASE
							WHEN pdd.ch_moneda='01' THEN pdd.nu_importe
							ELSE 0
						END as importesoles,
						CASE
							WHEN pdd.ch_moneda='02' THEN pdd.nu_importe
							ELSE 0
						END as importedolares,
						pdd.ch_usuario as usuario,
						pdd.ch_ip as ip,
						(CASE
							WHEN pdd.ch_moneda <> '01' AND pdd.ch_moneda='02' THEN 'Billetes'
							WHEN (pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10) >= 1 AND (pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010) < 1 THEN 'Billetes'
							WHEN (pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10) < 1 AND (pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010) >= 1 THEN 'Monedas'
							WHEN (pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10) >= 1 AND (pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010) >= 1 THEN 'B y M'
						ELSE 'Ninguna' END) as denominacion
					FROM
						pos_depositos_diarios pdd
						LEFT JOIN pla_ta_trabajadores ptt ON (pdd.ch_codigo_trabajador = ptt.ch_codigo_trabajador)
					WHERE
						pdd.ch_almacen = '${CodAlmacen}'
						AND pdd.dt_dia BETWEEN '$FechaIni' AND '$FechaFin'
					ORDER BY  
						fecha, turno, seq;";

			$headers = Array(
						0	=>	"VALIDA",
						1	=>	"DIA",
						2	=>	"TURNO",
						3	=>	"TRABAJADOR",
						4	=>	"FECHA",
						5	=>	"SEQ.",
						6	=>	"NUM.",
						7	=>	"MONEDA",
						8	=>	"CAMBIO",																		
						9	=>	"IMPORTE S/.",
						10	=>	"IMPORTE $",
						11	=>	"USUARIO",
						12	=>	"IP",
						13	=>	"DENOMINACION"
			);

			CSVFromQuery($sql,$ExportDir."depositos.txt",$headers);

			$sql = "	SELECT  
						hl.ch_sucursal AS ESTACION,
						hl.dt_dia AS DIA,
						hl.ch_posturno AS TURNO,
						hl.ch_lado AS LADO,
						hl.ch_codigo_trabajador AS CODTRABAJADOR,
						pl.ch_apellido_paterno ||' '|| pl.ch_apellido_materno ||' '|| pl.ch_nombre1 AS NOMTRABAJADOR,
						(CASE WHEN ch_tipo = 'C' THEN 'COMBUSTIBLE' ELSE 'MARKET' END) AS TIPO
					FROM
						pos_historia_ladosxtrabajador hl
						INNER JOIN pla_ta_trabajadores pl on hl.ch_codigo_trabajador=pl.ch_codigo_trabajador 
					WHERE
						dt_dia BETWEEN '$FechaIni' AND '$FechaFin'
						AND hl.ch_sucursal = '${CodAlmacen}'
					ORDER BY
						hl.ch_posturno,
						hl.ch_lado ;";
//echo $sql;
			$headers = Array(
						0	=>	"ESTACION",
						1	=>	"DIA",
						2	=>	"TURNO",
						3	=>	"LADO",
						4	=>	"CODTRABAJADOR",
						5	=>	"NOMTRABAJADOR",
						6	=>	"TIPO"
			);

			CSVFromQuery($sql,$ExportDir."trabajadores.txt",$headers);

			$sql = "	SELECT
						dia AS DIA,
						turno AS TURNO,
						fecha AS FECHA
					FROM 
						pos_contometros
					WHERE
						dia BETWEEN '$FechaIni' AND '$FechaFin'
					GROUP BY
						dia,
						turno,
						fecha
					ORDER BY 
						fecha;";

			$headers = Array(
						0	=>	"DIA",
						1	=>	"TURNO",
						2	=>	"FECHA"
			);

			CSVFromQuery($sql,$ExportDir."cierres.txt",$headers);

			$sql = "
SELECT
 to_char(com.dt_fac_fecha,'DD/MM/YYYY') AS FECHA,
 com.ch_fac_tipodocumento AS TIPO,
 com.ch_fac_seriedocumento AS SERIE,
 LPAD(trim(com.ch_fac_numerodocumento)::TEXT, 8, '0') AS NUMERO,
 cab.ch_numeval AS NOTADESPACHO,
 cli.cli_ruc AS CLIENTE,
 cli.cli_ruc AS RUC,
 cli.cli_razsocial AS NOMBRE,
 CASE WHEN eqp.codigo_iridium IS NOT NULL THEN eqp.art_codigo ELSE det.art_codigo END AS PRODUCTO,
 det.nu_fac_cantidad AS CANTIDAD,
 round(det.nu_fac_precio,3) AS PRECIO,
 det.nu_fac_valortotal AS TOTAL,
 cab.dt_fecha AS FECHA_CONSUMO,
 rtrim(cab.ch_placa,' ') AS PLACA,
 c.ch_glosa AS NOTA,
 c.ch_caja AS CAJA,
 c.ch_lado AS LADO,
 c.ch_turno AS TURNO,
 to_char(com.fe_vencimiento, 'DD/MM/YYYY') AS fvencimiento,
 (CASE WHEN com.ch_fac_tiporecargo2='S' THEN com.nu_fac_valortotal ELSE 0 END) AS EXONERADA,
 (CASE WHEN com.ch_fac_tiporecargo2 IN('T', 'U', 'W') THEN com.nu_fac_recargo1 ELSE 0 END) AS GRATUITA,
 (CASE WHEN com.ch_fac_tiporecargo2='V' THEN com.nu_fac_valortotal ELSE 0 END) AS INAFECTO,
 c.nu_importe AS TOTAL_ITEM
FROM
 fac_ta_factura_cabecera AS com
 JOIN int_clientes AS cli
  USING (cli_codigo)
 JOIN val_ta_complemento_documento AS cab
  ON (cab.ch_liquidacion = com.ch_liquidacion AND cab.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND cab.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento = com.ch_fac_numerodocumento)
 JOIN val_ta_cabecera AS c
  ON (c.ch_sucursal = cab.ch_sucursal AND c.dt_fecha = cab.dt_fecha AND c.ch_documento = cab.ch_numeval)
 JOIN fac_ta_factura_detalle AS det
  ON (cab.ch_fac_tipodocumento = det.ch_fac_tipodocumento AND cab.ch_fac_seriedocumento = det.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento = det.ch_fac_numerodocumento AND cab.art_codigo = det.art_codigo)
 JOIN interface_equivalencia_producto AS eqp
  ON (det.art_codigo::char(13) = eqp.art_codigo::char(13))
WHERE
 com.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
 AND com.ch_almacen LIKE '%{$CodAlmacen}%'
 AND com.ch_liquidacion != ''
 AND (com.ch_fac_anulado IS NULL OR com.ch_fac_anulado ='' OR com.ch_fac_anulado='N');
			";

			$headers = Array(
						0	=>	"FECHA",
						1	=>	"TIPO",
						2	=>	"SERIE",
						3	=>	"NUMERO",
						4	=>	"NOTADESPACHO",
						5	=>	"CLIENTE",
						6	=>	"RUC",
						7	=>	"NOMBRE",
						8	=>	"PRODUCTO",
						9	=>	"CANTIDAD",
						10	=>	"PRECIO",
						11	=>	"TOTAL",
						12	=>	"FECHA_CONSUMO",
						13	=>	"PLACA",
						14	=>	"NOTA",
						15	=>	"CAJA",
						16	=>	"LADO",
						17	=>	"TURNO",
						18	=>	"FECHA_VENCIMIENTO",//Se agregaron 3 campos nuevos - 10/08/2018
						19	=>	"EXONERADA",
						20	=>	"GRATUITA",
						21	=>	"INAFECTO",
						22	=>	"TOTAL_ITEM",
			);

			CSVFromQuery($sql,$ExportDir."vales-liquidados.txt",$headers);

			$sql = "
					SELECT DISTINCT
						C.dia DIA,
						C.turno TURNO,
						C.num_lado LADO,
						C.manguera MANGUERA,
						C.cnt_vol FINALCONTGALONES,
						C.cnt_val FINALCONTSOLES,
						P.importe DESCUENTOS
					FROM(
					SELECT
						pos.dia dia,
						pos.turno turno,
						pos.num_lado num_lado,
						pos.manguera || ' - ' || m.product AS manguera,
						pos.cnt_vol cnt_vol,
						pos.cnt_val cnt_val,
						m.product
					FROM
						pos_contometros pos
						LEFT JOIN f_grade m ON(m.f_pump_id::integer = pos.num_lado::integer AND m.name::integer = pos.manguera)
					WHERE
						dia BETWEEN '$FechaIni' AND '$FechaFin'
					) AS C

					LEFT JOIN

					(

						SELECT
							dia,
							turno,
							SUM(importe) AS importe,
							pump,
							codigo
						FROM
							$postrans
						WHERE
							td = 'N'
							AND grupo = 'D'
							AND es LIKE '%{$CodAlmacen}%'
							AND(
								(dia::date||''||turno::integer >= (
								SELECT
								min(dia)||''||min(turno)
							FROM
								pos_contometros
							WHERE
								dia BETWEEN '$FechaIni' AND '$FechaFin'))

							AND

							(dia::date||''||turno::integer <=
							(SELECT
								max(dia)||''||max(turno)
							FROM
								pos_contometros
							WHERE
								dia BETWEEN '$FechaIni' AND '$FechaFin')))
						GROUP BY
							DIA,
							TURNO,
							PUMP,
							CODIGO
						ORDER BY
							DIA,
							TURNO,
							PUMP

					) AS P
						ON (
							C.dia = P.dia
							AND C.turno::integer = P.turno::integer
							AND C.num_lado::integer = P.pump::integer
							AND C.product::integer = P.codigo::integer
						)

					ORDER BY
					DIA,
					TURNO,
					LADO,
					MANGUERA;

				";

			$headers = Array(
						0	=>	"DIA",
						1	=>	"TURNO",
						2	=>	"LADO",
						3	=>	"MANGUERA",
						4	=>	"FINALCONTGALONES",
						5       =>	"FINALCONTSOLES",
						6       =>	"DESCUENTOS"
			);

			CSVFromQuery($sql,$ExportDir."contometros-turno.txt",$headers);

			$sql = "
					SELECT
						caja CAJA,
						trans TICKET,
						dia DIA,
						turno TURNO,
						fecha FECHA,
						pump LADO,
						codigo PRODUCTO,
						cantidad CANTIDAD,
						precio PRECIO,
						importe IMPORTE,
						veloc VELOC,
						lineas LINEAS,
						responsabl RESPONSABLE
					FROM
						pos_ta_afericiones
					WHERE
						es LIKE '%{$CodAlmacen}%'
						AND dia BETWEEN '$FechaIni' AND '$FechaFin'
					ORDER BY
						dia desc,
						turno,
						caja,
						trans;
				";

			$headers = Array(
						0	=>	"CAJA",
						1	=>	"TICKET",
						2	=>	"DIA",
						3	=>	"TURNO",
						4	=>	"FECHA",
						5	=>	"LADO",
						6	=>	"PRODUCTO",
						7	=>	"CANTIDAD",
						8	=>	"PRECIO",
						9	=>	"IMPORTE",
						10	=>	"VELOC",
						11	=>	"LINEAS",
						12	=>	"RESPONSABLE"
			);

			CSVFromQuery($sql,$ExportDir."afericiones.txt",$headers);

			$sql = "
					SELECT 
						m.mov_numero,
						to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi:ss') AS mov_fecha,
						m.mov_tipdocuref,
						SUBSTRING(m.mov_docurefe, 1, 4),
						SUBSTRING(m.mov_docurefe, 5, 8),
						m.mov_almaorigen,
						m.mov_almadestino,
						m.mov_almacen,	 
						RTRIM(m.art_codigo, ' '),
						m.mov_cantidad,
						m.mov_costounitario,
						a.art_descripcion,
						RTRIM(mov_entidad, ' ') proveedor,
						--ROUND((m.mov_costounitario * m.mov_cantidad) / 1.18,4) subtotal,
						--ROUND(((m.mov_costounitario * m.mov_cantidad) - (m.mov_costounitario * m.mov_cantidad) / 1.18),4) igv,
						--ROUND(m.mov_costounitario * m.mov_cantidad,4) total,
						ROUND(m.mov_costounitario * m.mov_cantidad, 4) as subtotal,
						ROUND((m.mov_costounitario * m.mov_cantidad) * (util_fn_igv()/100), 4) as igv,
						ROUND((m.mov_costounitario * m.mov_cantidad) + (m.mov_costounitario * m.mov_cantidad) * (util_fn_igv()/100), 4) as total,
						CASE WHEN
							com_tipo_compra = '01' THEN 'Soles'
						ELSE
							'Dolares'
						END AS moneda,
						a.art_linea AS LINEA,
						l.tab_descripcion AS DESCLINEA
					FROM 
						inv_movialma m
						LEFT JOIN int_articulos a ON(m.art_codigo = a.art_codigo)
						LEFT JOIN int_tabla_general l ON (l.tab_tabla='20' AND (a.art_linea = l.tab_elemento OR a.art_linea = substr(l.tab_elemento,5,2)))
					WHERE 
						m.mov_fecha::DATE BETWEEN '$FechaIni' AND '$FechaFin'
						AND m.mov_almacen LIKE '%{$CodAlmacen}%'
						AND m.tran_codigo IN('01','21')
					ORDER BY 
						m.mov_fecha DESC
				";

			$headers = Array(
						0	=>	"FORMULARIO",
						1	=>	"FECHA",
						2	=>	"TIPO",
						3	=>	"SERIE",
						4	=>	"#DOCUMENTO",
						5	=>	"ORI",
						6	=>	"DEST",
						7	=>	"ALM",
						8	=>	"COD. ART.",
						9	=>	"CANTIDAD",
						10	=>	"COSTO UNIT.",
						11	=>	"DESCRIPCION ART",
						12	=>	"PROVEEDOR",
						13	=>	"SUB-TOTAL",
						14	=>	"IGV",
						15	=>	"TOTAL",
						16	=>	"MONEDA",
						17	=>	"LINEA",
						18	=>	"DESCLINEA"
			);

			CSVFromQuery($sql,$ExportDir."compras.txt",$headers);
		
			/*$flag = InterfaceModel::validaDia($FechaFin,$CodAlmacen);

			if ($flag == 1) { 
				$sql = "";

				$headers = Array(
							0	=>	"ESTACION",
							1	=>	"DIA",
							2	=>	"TURNO",
							3	=>	"TRABAJADOR",
							4	=>	"IMPORTE",
							5	=>	"OBSERVACION",
							6	=>	"PLANILLA",
							7	=>	"ALM",
							8	=>	"flagescrito",
							9	=>	"FLAG"
				);

				CSVFromQuery($sql,$ExportDir."sobrantes-faltantes.txt",$headers);
			}else{*/

			$sql = "
					SELECT 
						dt.es,
						to_char(dt.dia,'DD/MM/YYYY') as dia,
						dt.turno as turno,
						TRIM(t.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
						dt.importe as importe,
						dt.observacion as observacion,
						CASE
							WHEN dt.planilla=0 THEN 'No'
							ELSE 'Si'
						END as planilla,
						CASE
							WHEN dt.flag=0 THEN 'Automatico'
							ELSE 'Manual'
						END as flagescrito,
						dt.flag as flag
					FROM
						comb_diferencia_trabajador dt
						LEFT JOIN pla_ta_trabajadores t ON (dt.ch_codigo_trabajador = t.ch_codigo_trabajador)
					WHERE 
						dt.dia BETWEEN '$FechaIni' AND '$FechaFin'
						AND dt.es LIKE '%{$CodAlmacen}%'
					ORDER BY 
						dt.dia desc
				";

				$headers = Array(
							0	=>	"ESTACION",
							1	=>	"DIA",
							2	=>	"TURNO",
							3	=>	"TRABAJADOR",
							4	=>	"IMPORTE",
							5	=>	"OBSERVACION",
							6	=>	"PLANILLA",
							7	=>	"flagescrito",
							8	=>	"FLAG"
				);

				CSVFromQuery($sql,$ExportDir."sobrantes-faltantes.txt",$headers);


				$sql =	"
						SELECT
							almacen AS almacen,
							to_char(dia,'DD/MM/YYYY') AS dia,
							turno AS turno,
							to_char(fecha,'DD/MM/YYYY HH24:MI:SS') AS fecha
						FROM
							pos_consolidacion
						WHERE
							dia BETWEEN '$FechaIni' AND '$FechaFin'
							AND almacen LIKE '%{$CodAlmacen}%'
						ORDER BY
							dia ASC,
							turno ASC;
				";

				$headers = Array(
							0	=>	"ALMACEN",
							1	=>	"DIA",
							2	=>	"TURNO",
							3	=>	"FECHA"
				);

				CSVFromQuery($sql,$ExportDir."consolidaciones.txt",$headers);

				/* FACTURAS DE COMPRAS */

				$sql = "
					SELECT DISTINCT
						to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fregistro,
						to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') femision,
						a.ch_sigla_almacen cc,
						gen.tab_desc_breve||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento documento, 
						p.pro_codigo||' '||p.pro_razsocial proveedor,
						CASE WHEN d.pro_det_moneda IN('2','02') THEN '$' ELSE 'S/.' END moneda,
						c.pro_cab_impto1 impuesto,
						c.pro_cab_imptotal total,
						c.pro_cab_impsaldo saldo,
						rubro.ch_descripcion rubro,
						to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') fvencimiento,
						c.pro_cab_tipdocumento||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento|| ' - ' ||c.pro_codigo eliminar,
						c.pro_cab_fecharegistro,
						c.regc_sunat_percepcion perce,
						c.pro_cab_tcambio tc,
						c.pro_cab_impinafecto inafecto,
						c.pro_cab_impafecto imponible
					FROM
						cpag_ta_cabecera c
						INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
						LEFT JOIN int_proveedores p ON (c.pro_codigo = p.pro_codigo)
						LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
						LEFT JOIN int_tabla_general as gen ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
						LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					WHERE
						c.pro_cab_fechaemision::DATE BETWEEN '$FechaIni' AND '$FechaFin'
						AND c.pro_cab_almacen LIKE '%{$CodAlmacen}%'
					ORDER BY 
						to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') DESC;
				";

				$headers = Array(
							0	=>	"FECHA REGISTRO",
							1	=>	"FECHA EMISION",
							2	=>	"CC",
							3	=>	"DOCUMENTO",
							4	=>	"PROVEEDOR",
							5	=>	"MONEDA",
							6	=>	"BASE IMPONIBLE",
							7	=>	"IMPUESTO",
							8	=>	"INAFECTO IGV",
							9	=>	"TOTAL",
							10	=>	"SALDO",
							11	=>	"PERCEPCION",
							12	=>	"RUBRO",
							13	=>	"FECHA VENC."
				);

				CSVFromQuery($sql,$ExportDir."registro_compras.txt",$headers);

		}

		return $ZipFile;

	}

}
