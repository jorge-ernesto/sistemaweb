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

class InterfaceCopetrolModel extends Model {
	function ListadoAlmacenes($codigo) {
		global $sqlca;
		$cond = '';
		if ($codigo != "")
			$cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
		$query = "	SELECT
					ch_almacen
				FROM
					inv_ta_almacenes
				WHERE
					trim(ch_clase_almacen)='1'
					 ".$cond." 
				ORDER BY
					ch_almacen";

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

		$modulo = "TODOS";
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$ZipFile = $CodAlmacen."del".$FechaDiv[2].$FechaDiv[1].$FechaDiv[0]."al";
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$ZipFile .= $FechaDiv[2].$FechaDiv[1].$FechaDiv[0].".zip";
		if (("pos_trans".$FechaDiv[2].$FechaDiv[1])!=$postrans)
			return "INVALID_DATE";
		if (strlen($FechaIni)<10 || strlen($FechaFin)<10)
			return "INDALID_DATE";

		/*?><script>alert("<?php echo '+++ la campania es: '.$CodAlmacen ; ?> ");</script><?php*/

		//if ($sqlca->query("SELECT tipo FROM pos_cfg WHERE es='$CodAlmacen';")<=0)
			//return "INVALID_DATE";

		$sql = "SELECT tipo FROM pos_cfg WHERE es='$CodAlmacen';";

		$sqlca->query($sql);

		$reg = $sqlca->fetchRow();

		$TipoAlmacen = $reg[0];

		$ExportDir = "/home/data/";

/*	
MODULOS
=======
TODOS       = TODOS
VENTAS      = VENTAS
VENTASC     = VENTAS COMBUSTIBLES
VENTASM     = VENTAS MARKET
COMPRAS     = COMPRAS y CPAGAR
INVENTARIOS = INVENTARIOS
VALES       = VALES
SERVICIOS   = SERVICIOS
PLANILLA    = COMISIONES y ASISTENCIA
*/
/*?><script>alert("<?php echo '+++ la campania es: '.$TipoAlmacen ; ?> ");</script><?php*/

		if ($TipoAlmacen=="M" || $TipoAlmacen=="m") {
			$prefijo_tipo = "m-";
			$nombre_tipo = "Market";
		} else {
			$prefijo_tipo = "t-";
			$nombre_tipo = "Grifos";
		}

		if ($modulo == "TODOS" || $modulo == "VALES") {
			$sql = "	SELECT
						to_char(c.dt_fecha,'dd/mm/yyyy') AS FECHA,
						c.ch_sucursal AS SERIE,
						c.ch_documento AS NUMERO,
						c.ch_cliente AS CLIENTE,
						cli.cli_ruc AS RUC,
						cli.cli_razsocial AS NOMBRE,
						CASE
							WHEN eqp.codigo_iridium IS NOT NULL THEN eqp.codigo_iridium
							ELSE d.ch_articulo
						END AS PRODUCTO,
						d.nu_cantidad AS CANTIDAD,
						round((d.nu_importe / d.nu_cantidad),3) AS PRECIO,
						d.nu_importe AS TOTAL,
						c.dt_fecha AS FECHA_CONSUMO,
						c.ch_placa AS PLACA,
						c.ch_glosa AS NOTA
					FROM
						val_ta_detalle d
						LEFT JOIN val_ta_cabecera c ON c.ch_documento = d.ch_documento
						LEFT JOIN int_articulos art ON d.ch_articulo = art.art_codigo
						LEFT JOIN interface_equivalencia_producto eqp ON art.art_codigo::char(13) = eqp.art_codigo::char(13)
						LEFT JOIN int_clientes cli ON c.ch_cliente = cli.cli_codigo
					WHERE
						c.dt_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND c.ch_sucursal LIKE '%{$CodAlmacen}%';";

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
						12	=>	"NOTA"
			);

			CSVFromQuery($sql,$ExportDir."vales.txt",$headers);
		}

		if ($modulo == "TODOS" || $modulo=="VENTAS" || $modulo=="VENTASC") {
			$sql = "	SELECT
						ruc AS CODIGO,
						razsocial AS NOMBRE_RAZON_SOCIAL,
						'116'::character AS TIPO,
						ruc AS NUMERO,
						'1'::character AS CLIENTE,
						''::character AS DIRECCION,
						''::character AS TELEFONO
				        FROM
						ruc
					WHERE
						fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||ch_sucursal||'%'";

			$headers = Array (	0	=>	"CODIGO",
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
						i.codigo_iridium  AS PRODUCTO,
						a.nu_medicion as VARILLAJE
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
						3	=>	"VARILLAJE"
			);

			CSVFromQuery($sql,$ExportDir."varillaje.txt",$headers);

			$sql = "	SELECT
						to_char(c.dt_fechaparte,'dd/mm/yyyy') as FECHA,
						substring(ch_numeroparte,10,1) as TURNO,
						c.ch_surtidor AS MANGUERA,
						trim('GRIFERO') AS GRIFERO,
						i.codigo_iridium AS PRODUCTO,
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
						comb_ta_contometros c,
						interface_equivalencia_producto i 
					WHERE
						dt_fechaparte BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||ch_sucursal||'%'
						AND c.ch_codigocombustible=i.art_codigo;";

			$headers = Array(
						0	=>	"FECHA",
						1	=>	"TURNO",
						2	=>	"MANGUERA",
						3	=>	"GRIFERO",
						4	=>	"PRODUCTO",
						5	=>	"PRECIO",
						6	=>	"CONTOMETROSALIDASOLES",
						7	=>	"CONTOMETROSALIDAGLNS",
						8	=>	"TOTALSALIDASOLES",
						9	=>	"TOTALSALIDAGLNS",
						10	=>	"TOTALCONTADOSOLES",
						11	=>	"TOTALCONTADOGLNS",
						12	=>	"TOTALCREDITOSOLES",
						13	=>	"TOTALCREDITOGLNS",
						14	=>	"TOTALAFERIMIENTOSOLES",
						15	=>	"TOTALAFERIMIENTOGLNS"
			);

			CSVFromQuery($sql,$ExportDir."parte-diario.txt",$headers);
		}   


		if ($modulo=="TODOS" || $modulo=="VENTAS" || $modulo=="VENTASM") {
			$sql = "	SELECT
						ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento) AS VENTA,
						CASE
							WHEN ch_fac_anulado='S' THEN '0'
							ELSE '1'
						END AS SITUACION,
						ch_fac_tipodocumento AS DOCUMENTO,
						trim(ch_fac_seriedocumento) AS SERIE,
						trim(ch_fac_numerodocumento) AS NUMERO,
						to_char(dt_fac_fecha,'dd/mm/yyyy') AS FECHA,
						ch_fac_moneda AS MONEDA,
						trim('') AS OBSERVACIONES,
						cli_codigo AS CLIENTE,
						nu_fac_valorbruto AS SUBTOTAL,
						nu_fac_descuento1 AS DESCUENTO,
						nu_fac_valorbruto AS AFECTO,
						nu_fac_impuesto1 AS IGV,
						nu_fac_valortotal AS TOTAL
					FROM
						fac_ta_factura_cabecera
					WHERE
						dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||ch_almacen||'%'
						AND ch_fac_tipodocumento<>'45';";

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
						13	=>	"TOTAL"
			);

			CSVFromQuery($sql,$ExportDir."venta.txt",$headers);

			$sql = "	SELECT
						d.ch_fac_tipodocumento||trim(d.ch_fac_seriedocumento)||d.ch_fac_numerodocumento AS VENTA,
						TRIM('1') AS LINEA,
						i.codigo_iridium AS PRODUCTO,
						d.nu_fac_cantidad AS CANTIDAD,
						d.nu_fac_precio AS PRECIO,
						d.nu_fac_descuento1 AS DCTO,
						d.nu_fac_impuesto1  AS IGV,
						d.nu_fac_valortotal AS IMPORTE
					FROM
						fac_ta_factura_detalle d,
						fac_ta_factura_cabecera c,
						interface_equivalencia_producto i
					WHERE
						d.ch_fac_tipodocumento = c.ch_fac_tipodocumento
						AND d.ch_fac_seriedocumento = c.ch_fac_seriedocumento
						AND d.ch_fac_numerodocumento = c.ch_fac_numerodocumento
						AND d.cli_codigo = c.cli_codigo
						AND c.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND '${CodAlmacen}' LIKE '%'||c.ch_almacen||'%'
						AND d.ch_fac_tipodocumento<>'45'
						AND trim(d.art_codigo)=trim(i.art_codigo);";

			$headers = Array(
						0	=>	"VENTA",
						1	=>	"LINEA",
						2	=>	"PRODUCTO",
						3	=>	"CANTIDAD",
						4	=>	"PRECIO",
						5	=>	"DCTO",
						6	=>	"IGV",
						7	=>	"IMPORTE"
			);

			CSVFromQuery($sql,$ExportDir."ventalinea.txt",$headers);

			$sql = "	SELECT
						t.caja AS CAJAREGISTRADORA,
						first(z.nu_posz_z_numero) AS ZZ,
						first('1'::character) AS OP,
						first(c.nroserie) AS SERIE,
						t.trans AS TICKET,
						first(t.td) AS TIPO,
						first(t.ruc) AS CLIENTE,
						first('.'::character) AS NOMBRE,
						first(t.ruc) AS RUC,
						first(to_char(t.fecha,'dd/mm/yyyy')) AS FECHA,
						first(to_char(t.fecha,'HH24:MI:SS')) AS HORA,
						sum(t.importe-t.igv) AS AFECTO,
						sum(t.igv) AS IGV,
						sum(t.importe) AS TOTAL,
						first(t.td) AS TIPODOC,
						first(t.fpago) AS FORMAPAGO,
						first(t.at) AS TIPOTARJHETA,
						first(t.dia) AS FECHASYS,
						sum(t.cantidad) AS CANTIDAD,
						first(a.art_codigo) AS PRODUCTO,
						first(a.art_descbreve) AS DESCRIPCION
					FROM
						$postrans t
						LEFT JOIN pos_z_cierres z ON (
										t.caja = z.ch_posz_pos AND
										t.dia = z.dt_posz_fecha_sistema::date AND
										t.turno::integer = z.nu_posturno AND
										t.es = z.ch_sucursal)
						LEFT JOIN pos_cfg c ON (t.caja = c.pos)
						LEFT JOIN int_articulos a ON (trim(t.codigo) = trim(a.art_codigo))
					WHERE
						t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
						AND '{$CodAlmacen}' LIKE '%'||t.es||'%'
						AND t.td IN ('F','B')
					GROUP BY
						TICKET,
						CAJAREGISTRADORA,
						t.codigo
					ORDER BY
						CAJAREGISTRADORA,
						ZZ,
						TRANS;";

			$headers = Array(
						0	=>	"CAJAREGISTRADORA",
						1	=>	"ZZ",
						2	=>	"OP",
						3	=>	"SERIE",
						4	=>	"TICKET",
						5	=>	"TIPO",
						6	=>	"CLIENTE",
						7	=>	"NOMBRE",
						8	=>	"RUC",
						9	=>	"FECHA",
						10	=>	"HORA",
						11	=>	"AFECTO",
						12	=>	"IGV",
						13	=>	"TOTAL",
						14	=>	"TIPODOC",
						15	=>	"FORMAPAGO",
						16	=>	"TIPOTARJETA",
						17	=>	"FECHA_SISTEMA",
						18	=>	"CANTIDAD",
						19	=>	"PRODUCTO",
						20	=>	"DESCRIPCION"
			);

			CSVFromQuery($sql,$ExportDir."zzventatickets.txt",$headers);//colocar el codigo y descripcion del producto

			$sql = "	SELECT
						t.caja AS CAJAREGISTRADORA,
						first(z.nu_posz_z_numero) AS ZZ,
						first('1'::character) AS OP,
						first(c.nroserie) AS SERIE,
						t.trans AS TICKET,
						first(t.td) AS TIPO,
						first(t.ruc) AS CLIENTE,
						first('.'::character) AS NOMBRE,
						first(t.ruc) AS RUC,
						first(to_char(t.fecha,'dd/mm/yyyy')) AS FECHA,
						first(to_char(t.fecha,'HH24:MI:SS')) AS HORA,
						sum(t.importe-t.igv) AS AFECTO,
						sum(t.igv) AS IGV,
						sum(t.importe) AS TOTAL,
						first(t.td) AS TIPODOC,
						first(t.fpago) AS FORMAPAGO,
						first(t.at) AS TIPOTARJHETA,
						first(t.dia) AS FECHASYS,
						sum(t.cantidad) AS CANTIDAD
					FROM
						$postrans t
						LEFT JOIN pos_z_cierres z ON (
										t.caja = z.ch_posz_pos AND
										t.dia = z.dt_posz_fecha_sistema::date AND
										t.turno::integer = z.nu_posturno AND
										t.es = z.ch_sucursal)
						LEFT JOIN pos_cfg c ON (t.caja = c.pos)
					WHERE
						t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
						AND '{$CodAlmacen}' LIKE '%'||t.es||'%'
						AND t.td IN ('F','B')
					GROUP BY
						TICKET,
						CAJAREGISTRADORA
					ORDER BY
						CAJAREGISTRADORA,
						ZZ,
						TRANS;";

			$headers = Array(
						0	=>	"CAJAREGISTRADORA",
						1	=>	"ZZ",
						2	=>	"OP",
						3	=>	"SERIE",
						4	=>	"TICKET",
						5	=>	"TIPO",
						6	=>	"CLIENTE",
						7	=>	"NOMBRE",
						8	=>	"RUC",
						9	=>	"FECHA",
						10	=>	"HORA",
						11	=>	"AFECTO",
						12	=>	"IGV",
						13	=>	"TOTAL",
						14	=>	"TIPODOC",
						15	=>	"FORMAPAGO",
						16	=>	"TIPOTARJETA",
						17	=>	"FECHA_SISTEMA",
						18	=>	"CANTIDAD"
			);

			CSVFromQuery($sql,$ExportDir."zzventatickets_1.txt",$headers);//colocar el codigo y descripcion del producto

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
							WHEN i.codigo_iridium IS NOT NULL THEN i.codigo_iridium
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
						sum(t.importe) AS TIMPORTE
					FROM
						$postrans t
						LEFT JOIN pos_z_cierres z ON t.caja = z.ch_posz_pos AND t.dia=z.dt_posz_fecha_sistema AND t.turno = cast(z.nu_posturno  as varchar)
						LEFT JOIN pos_cfg c ON t.caja=c.pos
						LEFT JOIN interface_equivalencia_producto i ON trim(t.codigo)=trim(i.art_codigo)
						LEFT JOIN int_articulos a ON trim(t.codigo)=trim(a.art_codigo)
					WHERE
						t.fecha BETWEEN '$FechaIni' AND '$FechaFin'
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

			/*var_dump($sql);
			exit;
			

						3	=>	"PRODUCTO",
						4	=>	"DESCRIPCION",
						5	=>	"TCANTIDAD",
						6	=>	"PPRECIO",*/

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
						9	=>	"TIMPORTE"
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
						pdd.ch_ip as ip
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
						12	=>	"IP"
			);

			CSVFromQuery($sql,$ExportDir."depositos.txt",$headers);

		}

		return $ZipFile;
	}
}
?>

