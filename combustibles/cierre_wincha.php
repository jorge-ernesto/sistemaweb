<?php
include('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

function cierre_turno() {
	global $sqlca;

	$rs = $sqlca->query("SELECT da_fecha, ch_posturno,to_char(now(),'DD/MM/YYYY HH24:MI') FROM pos_aprosys WHERE ch_poscd='A'");
    	if ($rs < 0) {
		echo "Error consultando dia abierto\n";
		return false;
    	}

    	if ($sqlca->numrows() > 0) {
		$aprosys 	= $sqlca->fetchRow();
		$fecha_aprosys 	= $aprosys[0];
		$turno_aprosys 	= $aprosys[1];
		$now 		= $aprosys[2];
		$anio_aprosys 	= substr($fecha_aprosys, 0, 4);
		$mes_aprosys 	= substr($fecha_aprosys, 5, 2);
		$dia_aprosys 	= substr($fecha_aprosys, 8, 2);

		$postrans_name 	= "pos_trans" . $anio_aprosys . $mes_aprosys;
		$fileprefix 	= "/tmp/imprimir/cierret" . date("dmYHis");

		// Genera el reporte de cierre de Z
		$rs = $sqlca->query("SELECT pos_fn_cierre_z_transtmp()");
		if ($rs < 0) {
			echo "Error realizando cierre de Z\n";
			return false;
		}

		$rs = $sqlca->query("SELECT pos FROM pos_cfg");
		if ($rs < 0) {
			echo "Error consultando puntos de venta\n";
			return false;
		}

		while ($row = $sqlca->fetchRow())
			$poss[] = $row[0];

		foreach ($poss as $pos) {
			$file = $fileprefix . $pos;
			//echo "-File1-<<".$fileprefix.">>"."<<".$pos.">>";
			genera_cierre_turno($pos,$fecha_aprosys,$turno_aprosys,$now,$file);
			imprimir_archivo($file,$pos);
		}

		// Verifica si ya existe tabla pos_trans del mes/anio
		$rs = $sqlca->query("SELECT tablename FROM pg_tables WHERE tablename='" . $postrans_name . "'");
		if ($rs < 0) {
			echo "Error consultando por tabla pos_trans\n";
			return false;
		}

		if ($sqlca->numrows() > 0) {
			// Tabla ya existe, solo anexar a ella
			if ($sqlca->query("INSERT INTO " . $postrans_name . " (SELECT * FROM pos_transtmp)") < 0) {
				echo "Error insertando en pos_trans mensual\n";
				return false;
			}
		} else {
			// Crear tabla nueva
			if ($sqlca->query("SELECT * INTO " . $postrans_name . " FROM pos_transtmp") < 0) {
				echo "Error creando tabla nueva para pos_trans mensual\n";
				return false;
			}
		}

		// Elimina registros de pos_transtmp
		$rs = $sqlca->query("DELETE FROM pos_transtmp");
		if ($rs < 0) {
			echo "Error eliminando registros antiguos de pos_transtmp\n";
			return false;
		}

		// Aumenta el numero de turno
		$rs = $sqlca->query("UPDATE pos_aprosys SET ch_posturno=ch_posturno+1 WHERE da_fecha='" . $fecha_aprosys . "'");
		if ($rs < 0) {
			echo "Error actualizando numero de turno\n";
			return false;
		}
    	} else {
		echo "No hay dia abierto. Por favor verifique.\n";
    	}

    	return true;
}

function genera_cierre_turno($pos,$dia,$turno,$now,$file) {
	global $sqlca;

	$sql =	"SELECT
			p1.par_valor,
			p2.par_valor,
			p3.par_valor
		FROM
			int_parametros p1
			LEFT JOIN pos_cfg pc ON trim(pc.pos) = '$pos'
			LEFT JOIN int_parametros p2 ON trim(p2.par_nombre) = 'desces'
			LEFT JOIN int_parametros p3 ON trim(p3.par_nombre) = 'dires'
		WHERE
			trim(p1.par_nombre) = CASE WHEN pc.tipo = 'M' THEN 'razsocial_market' ELSE 'razsocial' END";

	$rs = $sqlca->query($sql);

	if ($rs < 0) {
		echo "Error consultando datos de la estacion\n";
		return false;
	}
	$datoseess = Array();
	$datoseess = $sqlca->fetchRow();

	//Totales por Linea de Producto
	$sql =	"SELECT
			trim(tg.tab_descripcion),
			sum(pt.cantidad),
			sum(pt.importe)
		FROM
			pos_transtmp pt,
			int_tabla_general tg,
			int_articulos a
		WHERE
			pt.codigo=a.art_codigo
			AND (
				a.art_linea=tg.tab_elemento
				OR a.art_linea=substr(tg.tab_elemento,5,2)
			)
			AND pt.trans IS NOT NULL
			AND pt.tm = 'V'
			AND trim(pt.caja)='".$pos."'
			AND tg.tab_tabla='20'
			AND tg.tab_elemento!='000000'
		GROUP BY
			tg.tab_descripcion";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x categoria\n";
		return false;
	}
	$ventasxcategoria = Array();
	while ($row = $sqlca->fetchRow())
		$ventasxcategoria[] = $row;

	//Totales por Tipo de Movimiento y Tipo de Documento
	$sql = 	"SELECT
			tm,
			td,
			count(distinct trans),
			sum(importe)
		FROM
			pos_transtmp
		WHERE
			trans IS NOT NULL
			AND trim(caja) = '".$pos."'
		GROUP BY
			1,
			2
		ORDER BY
			1,
			2";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x tm y td\n";
		return false;
	}

	$ventasxtmtd 	= Array();
	$totalesq 	= Array();
	$totalesq["D"] 	= 0;
	$totalesq["V"] 	= 0;
	$totalesi 	= Array();
	$totalesi["D"] 	= 0;
	$totalesi["V"] 	= 0;

	while ($row = $sqlca->fetchRow()) {
		if ($row[1]=="F")
			$row[1] = "FACTURAS";
		else if ($row[1]=="B")
			$row[1] = "BOLETA";
		else if ($row[1]=="N")
			$row[1] = "NOTA DESPACHO";
		else if ($row[1]=="A")
			$row[1] = "AFERICION";
		$totalesq["*"] += $row[2];
		$totalesq[$row[0]] += $row[2];
		$totalesi["*"] += $row[3];
		$totalesi[$row[0]] += $row[3];
		$ventasxtmtd[] = $row;
	}

	//Totales de Descuentos por Tipo de Documento
	$sql =	"SELECT
			td,
			count(distinct trans),
			sum(importe)
		FROM
			pos_transtmp
		WHERE
			trans IS NOT NULL
			AND trim(caja) = '$pos'
			AND importe < 0
		GROUP BY
			1
		ORDER BY
			1";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando descuentos por tipo de documento\n";
		return false;
	}

	$totaldescuentos = 0;
	$descuentosxtd = Array();
	while ($row = $sqlca->fetchRow()) {
		if ($row[0]=="F")
			$row[0] = "FACTURAS";
		else if ($row[0]=="B")
			$row[0] = "BOLETA";
		else if ($row[0]=="N")
			$row[0] = "NOTA DESPACHO";
		else if ($row[0]=="A")
			$row[0] = "AFERICION";
		$descuentosxtd[$row[0]] = $row;
		$totalesq["-"] += $row[1];
		$totalesi["-"] += $row[2];
	}

	//Totales por Forma de Pago
	$sql =	"SELECT
			fpago,
			sum(importe)
		FROM
			pos_transtmp
		WHERE
			trans IS NOT NULL
			AND trim(caja) = '".$pos."'
			AND td IN ('B','F')
		GROUP BY
			1
		ORDER BY
			1 ";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x forma de pago\n";
		return false;
	}

	$ventasxfpago = Array();
	while ($row = $sqlca->fetchRow()) {
		if ($row[0] == "1")
			$row[0] = "EFECTIVO";
		else if ($row[0] == "2")
			$row[0] = "TARJETAS CREDITO";
		else
			continue;
		$ventasxfpago[] = $row;
	}

	//Totales por Tipo de Tarjeta
	$sql = 	"SELECT
			t.at,
			min(g.tab_descripcion),
			count(distinct t.trans),
			sum(t.importe)
		FROM
			pos_transtmp t
			LEFT JOIN int_tabla_general g ON (g.tab_tabla = '95' AND g.tab_elemento = '00000' || trim(t.at))
		WHERE
			t.trans IS NOT NULL
			AND trim(t.caja) = '".$pos."'
			AND fpago='2'
		GROUP BY
			1
		ORDER BY
			1";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x at\n";
		return false;
	}

	$ventasxat = Array();
	while ($row = $sqlca->fetchRow()) {
		$ventasxat[] = $row;
	}

	//Totales de Caja en Combustibles y Market
	$sql =	"SELECT
			tipo,
			sum(importe)
		FROM
			pos_transtmp
		WHERE
			trans IS NOT NULL
			AND trim(caja) = '$pos'
			AND td IN ('B','F')
			AND fpago = '1'
		GROUP BY
			1
		ORDER BY
			1";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando total caja por tipo de venta\n";
		return false;
	}

	$cajaxtv = Array();
	while ($row = $sqlca->fetchRow())
		$cajaxtv[(($row[0]=="C")?"COMBUSTIBLES":"MARKET")] = $row[1];

	//Primer y Ultimo Ticket
	$sql =	"SELECT
			min(trans),
			max(trans)
		FROM
			pos_transtmp
		WHERE
			trim(caja) = '".$pos."'
		";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando tickets\n";
		return false;
	}

	$tickets = Array();
	while ($row = $sqlca->fetchRow())
		$tickets[] = $row;

	//Generar Ticket
	$buff  = "========================================"	. "\n";
	$buff .= alinea($datoseess[0],2)			. "\n";
	$buff .= alinea($datoseess[1],2)			. "\n";
	$buff .= alinea($datoseess[2],2)			. "\n";
	$buff .= "========================================"	. "\n";
	$buff .= alinea("INFORME DE CONCILIACION DE CAJA",2)	. "\n";
	$buff .= "========================================"	. "\n";
	$buff .= alinea("CIERRE DEL TURNO $turno DIA $dia",2)	. "\n";
	$buff .= alinea("POS $pos DIA $now",2)			. "\n";
	$buff .= "========================================"	. "\n";

	foreach ($ventasxcategoria as $cat)
		$buff .= alineatc($cat[0],$cat[1],$cat[2])		. "\n";

	$buff .= alinea("------------------",1)			. "\n";

	$buff .= alineatc("VENTA TOTAL",$totalesq["V"],$totalesi["V"])		. "\n";
	$buff .= "========================================"	. "\n";

	foreach ($ventasxtmtd as $td) 
		if ($td[0]!="V") 
			continue; 
		else
			$buff .= alineatc($td[1],$td[2],$td[3])			. "\n";

	if (count($descuentosxtd)>0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("DESCUENTOS",2)				. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($descuentosxtd as $dtd)
			$buff .= alineatc($dtd[0],$dtd[1],$dtd[2])		. "\n";

		$buff .= alinea("------------------",1)			. "\n";
		$buff .= alineatc("TOTAL DESCUENTOS",$totalesq["-"],$totalesi["-"])	. "\n";
	}

	if ($totalesq["D"]!=0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("DEVOLUCIONES",2)			. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($ventasxtmtd as $td) 
			if ($td[0]!="D" || $td[1]=="AFERICION") 
				continue; 
			else
				$buff .= alineatc($td[1],$td[2],$td[3])			. "\n";

		$buff .= alinea("------------------",1)			. "\n";
		$buff .= alineatc("TOTAL DEVOLUCIONES",$totalesq["D"],$totalesi["D"])	. "\n";
	}

	$buff .= "========================================"	. "\n";
	$buff .= alinea("RESUMEN FORMAS DE PAGO",2)		. "\n";
	$buff .= "========================================"	. "\n";

	foreach ($ventasxfpago as $vfp)
		$buff .= alineadc($vfp[0],$vfp[1])			. "\n";

	if (count($ventasxat)>0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("RESUMEN DE TARJETAS DE CREDITO",2)	. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($ventasxat as $vat)
			$buff .= alineatc($vat[1],$vat[2],$vat[3])		. "\n";
	}

	if (count($cajaxtv)>0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("TOTAL CAJA",2)				. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($cajaxtv as $tv => $ti)
			$buff .= alineadc($tv,$ti)				. "\n";
	}

	$buff .= "========================================"	. "\n";
	$buff .= alinea("Tickets del {$tickets[0][0]} al {$tickets[0][1]}",0)	. "\n";
	$buff .= "========================================"	. "\n";
	$buff .= "\n\n\n\n\n";

	//Guardar Archivo
	$fh = fopen($file,'w');
	fwrite($fh,$buff);
	fclose($fh);

	return true;
}

function genera_cierre_dia($pos,$dia,$tabla,$now,$file) {
	global $sqlca;

	$sql =	"SELECT
			p1.par_valor,
			p2.par_valor,
			p3.par_valor
		FROM
			int_parametros p1
			LEFT JOIN pos_cfg pc ON trim(pc.pos) = '$pos'
			LEFT JOIN int_parametros p2 ON trim(p2.par_nombre) = 'desces'
			LEFT JOIN int_parametros p3 ON trim(p3.par_nombre) = 'dires'
		WHERE
			trim(p1.par_nombre) = CASE WHEN pc.tipo = 'M' THEN 'razsocial_market' ELSE 'razsocial' END";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando datos de la estacion\n";
		return false;
	}
	$datoseess = Array();
	$datoseess = $sqlca->fetchRow();
	
	//Totales por Linea de Producto
	$sql =	"SELECT
			trim(tg.tab_descripcion),
			sum(pt.cantidad),
			sum(pt.importe)
		FROM
			$tabla pt,
			int_tabla_general tg,
			int_articulos a
		WHERE
			pt.codigo=a.art_codigo
			AND (
				a.art_linea=tg.tab_elemento
				OR a.art_linea=substr(tg.tab_elemento,5,2)
			)
			AND pt.trans IS NOT NULL
			AND pt.tm = 'V'
			AND pt.dia = '$dia'
			AND trim(pt.caja)='".$pos."'
			AND tg.tab_tabla='20'
			AND tg.tab_elemento!='000000'
		GROUP BY
			tg.tab_descripcion";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x categoria\n";
		return false;
	}
	$ventasxcategoria = Array();
	while ($row = $sqlca->fetchRow())
		$ventasxcategoria[] = $row;

	//Totales por Tipo de Movimiento y Tipo de Documento
	$sql = 	"SELECT
			tm,
			td,
			count(distinct trans),
			sum(importe)
		FROM
			$tabla
		WHERE
			trans IS NOT NULL
			AND dia = '$dia'
			AND trim(caja) = '".$pos."'
		GROUP BY
			1,
			2
		ORDER BY
			1,
			2";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x tm y td\n";
		return false;
	}
	$ventasxtmtd = Array();
	$totalesq = Array();
	$totalesq["D"] = 0;
	$totalesq["V"] = 0;
	$totalesi = Array();
	$totalesi["D"] = 0;
	$totalesi["V"] = 0;

	while ($row = $sqlca->fetchRow()) {
		if ($row[1]=="F")
			$row[1] = "FACTURAS";
		else if ($row[1]=="B")
			$row[1] = "BOLETA";
		else if ($row[1]=="N")
			$row[1] = "NOTA DESPACHO";
		else if ($row[1]=="A")
			$row[1] = "AFERICION";
		$totalesq["*"] += $row[2];
		$totalesq[$row[0]] += $row[2];
		$totalesi["*"] += $row[3];
		$totalesi[$row[0]] += $row[3];
		$ventasxtmtd[] = $row;
	}

	//Totales de Descuentos por Tipo de Documento
	$sql =	"SELECT
			td,
			count(distinct trans),
			sum(importe)
		FROM
			$tabla
		WHERE
			trans IS NOT NULL
			AND trim(caja) = '$pos'
			AND importe < 0
			AND dia = '$dia'
		GROUP BY
			1
		ORDER BY
			1";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando descuentos por tipo de documento\n";
		return false;
	}
	$totaldescuentos = 0;
	$descuentosxtd = Array();

	while ($row = $sqlca->fetchRow()) {
		if ($row[0]=="F")
			$row[0] = "FACTURAS";
		else if ($row[0]=="B")
			$row[0] = "BOLETA";
		else if ($row[0]=="N")
			$row[0] = "NOTA DESPACHO";
		else if ($row[0]=="A")
			$row[0] = "AFERICION";
		$descuentosxtd[$row[0]] = $row;
		$totalesq["-"] += $row[1];
		$totalesi["-"] += $row[2];
	}

	//Totales por Forma de Pago
	$sql =	"SELECT
			fpago,
			sum(importe)
		FROM
			$tabla
		WHERE
			trans IS NOT NULL
			AND trim(caja) = '".$pos."'
			AND dia = '$dia'
			AND td IN ('B','F')
		GROUP BY
			1
		ORDER BY
			1
		";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x forma de pago\n";
		return false;
	}
	$ventasxfpago = Array();

	while ($row = $sqlca->fetchRow()) {
		if ($row[0] == "1")
			$row[0] = "EFECTIVO";
		else if ($row[0] == "2")
			$row[0] = "TARJETAS CREDITO";
		else
			continue;
		$ventasxfpago[] = $row;
	}

	//Totales por Tipo de Tarjeta
	$sql = 	"SELECT
			t.at,
			min(g.tab_descripcion),
			count(distinct t.trans),
			sum(t.importe)
		FROM
			$tabla t
			LEFT JOIN int_tabla_general g ON (g.tab_tabla = '95' AND g.tab_elemento = '00000' || trim(t.at))
		WHERE
			t.trans IS NOT NULL
			AND trim(t.caja) = '".$pos."'
			AND t.dia = '$dia'
			AND fpago='2'
		GROUP BY
			1
		ORDER BY
			1";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando ventas x at\n";
		return false;
	}
	$ventasxat = Array();
	while ($row = $sqlca->fetchRow()) {
		$ventasxat[] = $row;
	}

	//Totales de Caja en Combustibles y Market
	$sql =	"SELECT
			tipo,
			sum(importe)
		FROM
			$tabla
		WHERE
			trans IS NOT NULL
			AND dia = '$dia'
			AND trim(caja) = '$pos'
			AND td IN ('B','F')
			AND fpago = '1'
		GROUP BY
			1
		ORDER BY
			1";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando total caja por tipo de venta\n";
		return false;
	}
	$cajaxtv = Array();
	while ($row = $sqlca->fetchRow())
		$cajaxtv[(($row[0]=="C")?"COMBUSTIBLES":"MARKET")] = $row[1];

	//Primer y Ultimo Ticket
	$sql =	"SELECT
			min(trans),
			max(trans)
		FROM
			$tabla
		WHERE
			trim(caja) = '".$pos."'
			AND dia = '$dia'
		";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando tickets\n";
		return false;
	}
	$tickets = Array();
	while ($row = $sqlca->fetchRow())
		$tickets[] = $row;

	//Generar Ticket
	$buff  = "========================================"	. "\n";
	$buff .= alinea($datoseess[0],2)			. "\n";
	$buff .= alinea($datoseess[1],2)			. "\n";
	$buff .= alinea($datoseess[2],2)			. "\n";
	$buff .= "========================================"	. "\n";
	$buff .= alinea("INFORME DIARIO DE CONCILIACION DE CAJA",2)			. "\n";
	$buff .= "========================================"	. "\n";
	$buff .= alinea("CIERRE DEL DIA $dia",2)	. "\n";
	$buff .= alinea("POS $pos DIA $now",2)			. "\n";
	$buff .= "========================================"	. "\n";

	foreach ($ventasxcategoria as $cat)
		$buff .= alineatc($cat[0],$cat[1],$cat[2])		. "\n";

	$buff .= alinea("------------------",1)			. "\n";

	$buff .= alineatc("VENTA TOTAL",$totalesq["V"],$totalesi["V"])		. "\n";
	$buff .= "========================================"	. "\n";

	foreach ($ventasxtmtd as $td) 
		if ($td[0]!="V") 
			continue; 
		else
			$buff .= alineatc($td[1],$td[2],$td[3])			. "\n";

	if (count($descuentosxtd)>0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("DESCUENTOS",2)				. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($descuentosxtd as $dtd)
			$buff .= alineatc($dtd[0],$dtd[1],$dtd[2])		. "\n";

		$buff .= alinea("------------------",1)			. "\n";
		$buff .= alineatc("TOTAL DESCUENTOS",$totalesq["-"],$totalesi["-"])	. "\n";
	}

	if ($totalesq["D"]!=0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("DEVOLUCIONES",2)			. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($ventasxtmtd as $td) 
			if ($td[0]!="D" || $td[1]=="AFERICION") 
				continue; 
			else
				$buff .= alineatc($td[1],$td[2],$td[3])			. "\n";

		$buff .= alinea("------------------",1)			. "\n";
		$buff .= alineatc("TOTAL DEVOLUCIONES",$totalesq["D"],$totalesi["D"])	. "\n";
	}

	$buff .= "========================================"	. "\n";
	$buff .= alinea("RESUMEN FORMAS DE PAGO",2)		. "\n";
	$buff .= "========================================"	. "\n";

	foreach ($ventasxfpago as $vfp)
		$buff .= alineadc($vfp[0],$vfp[1])			. "\n";

	if (count($ventasxat)>0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("RESUMEN DE TARJETAS DE CREDITO",2)	. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($ventasxat as $vat)
			$buff .= alineatc($vat[1],$vat[2],$vat[3])		. "\n";
	}

	if (count($cajaxtv)>0) {
		$buff .= "========================================"	. "\n";
		$buff .= alinea("TOTAL CAJA",2)				. "\n";
		$buff .= "========================================"	. "\n";

		foreach ($cajaxtv as $tv => $ti)
			$buff .= alineadc($tv,$ti)				. "\n";
	}

	$buff .= "========================================"	. "\n";
	$buff .= alinea("Tickets del {$tickets[0][0]} al {$tickets[0][1]}",0)	. "\n";
	$buff .= "========================================"	. "\n";
	$buff .= "\n\n\n\n\n";

	//Guardar Archivo
	$fh = fopen($file,'w');
	fwrite($fh,$buff);
	fclose($fh);

	return true;
}

//Imprime el archivo especificado en el POS indicado solo si el POS esta configurado para imprimir
function imprimir_archivo($file,$pos) {
	global $sqlca;

	
	//	$sql =	"SELECT
	//		trim(pc_samba),
	//	trim(prn_samba),
	//		trim(ip) 
	//	FROM
	//		pos_cfg 
	//	WHERE
	//		impcierre = true
	//		AND trim(pos) = '$pos'";

	$sql = "SELECT
			'tm300' as printername,
			CASE
			WHEN terminaldata LIKE '%|%' THEN split_part(terminaldata, '|', 1) ELSE terminaldata END as printerip
			FROM
			s_pos c
			JOIN int_parametros p ON (p.par_nombre='pos_consolida' AND c.s_pos_id::VARCHAR = p.par_valor)";


	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando POS\n";
		return false;
	}
	if ($sqlca->numrows()<1)
		return true;

	$row = $sqlca->fetchRow();
	$smbc = "lpr -H {$row['printerip']} -P {$row['printername']} {$file}";

	//	$fp = fopen("COMANDO.txt","a");
	//	fwrite($fp, "-".$smbc."-".PHP_EOL);
	//	fclose($fp);  

	exec($smbc);

	return true;
}

//Retorna una cadena con $q espacios
function espacios($q) {
	$ret = "";
	for ($q;$q>0;$q--)
		$ret .= " ";
	return $ret;
}

/* Alinea texto simple
Tipo:
0	Derecha
1	Izquierda
2	Centro
*/

function alinea($str,$tipo) {
	if ($tipo==0)
		return ($str . espacios((40-strlen($str))));
	else if ($tipo==1)
		return (espacios((40-strlen($str))) . $str);
	return (espacios((20-(strlen($str)/2))) . $str . espacios((20-(strlen($str)/2))));
}

// Alinea Texto y Numero a Doble Columna
function alineadc($str1,$str2) {
	$str2 = number_format($str2,2,".",",");
	return ($str1 . (espacios((40-(strlen($str1)+strlen($str2))))) . $str2);
}

// Alinea Texto, Cantitdad y Monto a Triple Columna(20,10,10)
function alineatc($str1,$q,$d) {
	if (strlen($str1)>20)
		$str1 = substr($str1,0,20);
	$q = number_format($q,2,".",",");
	$d = number_format($d,2,".",",");
	return ($str1 . espacios((20-strlen($str1))) . espacios((10-strlen($q))) . $q . espacios((10-strlen($d))) . $d);
}
