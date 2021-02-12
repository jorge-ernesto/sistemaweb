<?php
include('/sistemaweb/include/dbsqlca.php');

set_time_limit(0);

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

// add log
//$fp = fopen("log_cierre.txt","a"); fwrite($fp, "____________________________________________________________________________".PHP_EOL); fclose($fp); 
// end log

// Parche AAG: Solamente permite cierre manual si no hay dispensadores configurados
$sqlca->query("SELECT count(*) FROM f_pump;");
$q = 1;
if ($sqlca->numrows() > 0) {
	$rr = $sqlca->fetchRow();
	$q = $rr[0];
}
if (isset($_GET['ocs_shift_pwd']) && $_GET['ocs_shift_pwd'] == "710818")
	$q = 0;
if ($q != 0)
	die("Opcion deshabilitada; cierres solamente en playa");

if ($_REQUEST['flg'] == 'CT') {
    	$sqlca->query("BEGIN");
    	echo "Cierre de TURNO\n";
    	$success = cierre_turno();
    	if (!$success) {
		$sqlca->query("ROLLBACK");
		echo "Error cerrando TURNO\n";
		exit;
    	}
    	$sqlca->query("COMMIT");
    	echo "Fin de cierre de TURNO\n";
} else if ($_REQUEST['flg'] == 'CD') {
    	$sqlca->query("BEGIN");
    	echo "Cierre de DIA\n";
    	echo "Ejecutando cierre de TURNO PREVIO\n";
    	$success = cierre_turno();
    	if (!$success) {
		echo "Error en cierre de TURNO previo\n";
		$sqlca->query("ROLLBACK");
		exit;
    	}
    	echo "Fin de cierre de TURNO\n";
    	$success = cierre_dia();
    	if (!$success) {
		echo "Error en cierre de DIA\n";
		$sqlca->query("ROLLBACK");
		exit;
    	}
    	$sqlca->query("COMMIT");
    	echo "Fin de cierre de DIA\n";
} else {
    	echo "<body>";
    	echo "&nbsp;TRANSACCIONES DE PUNTO DE VENTA<hr noshade>";
    	echo "<p><a href='cierres.php?flg=CT'>Cierre de Turno</a>&nbsp;&nbsp;&nbsp;<a href='cierres.php?flg=CD'>Cierre del Dia</a></p>";
    	echo "</table>";
    	echo "</body>";
	exit;
}

function cierre_turno() {
	global $sqlca;

	$q1 = "SELECT da_fecha, ch_posturno,to_char(now(),'DD/MM/YYYY HH24:MI') FROM pos_aprosys WHERE ch_poscd='A'";
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q1." -".PHP_EOL); fclose($fp); 
	// end log
	$rs = $sqlca->query($q1);
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
		$q2 = "SELECT pos_fn_cierre_z_transtmp()";
		// add log
		//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q2." -".PHP_EOL); fclose($fp); 
		// end log
		$rs = $sqlca->query($q2);
		if ($rs < 0) {
			echo "Error realizando cierre de Z\n";
			return false;
		}

		$q3 = "SELECT pos FROM pos_cfg";
		// add log
		//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q3." -".PHP_EOL); fclose($fp); 
		// end log
		$rs = $sqlca->query($q3);
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

		//set_time_limit(0);

		// Verifica si ya existe tabla pos_trans del mes/anio
		$q4 = "SELECT tablename FROM pg_tables WHERE tablename='" . $postrans_name . "'";
		// add log
		//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q4." -".PHP_EOL); fclose($fp); 
		// end log
		$rs = $sqlca->query($q4);
		if ($rs < 0) {
			echo "Error consultando por tabla pos_trans\n";
			return false;
		}

		if ($sqlca->numrows() > 0) {
			// Tabla ya existe, solo anexar a ella
			$q5 = "INSERT INTO " . $postrans_name . " (SELECT * FROM pos_transtmp)";
			// add log
			//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q5." -".PHP_EOL); fclose($fp); 
			// end log
			if ($sqlca->query($q5) < 0) {
				echo "Error insertando en pos_trans mensual\n";
				return false;
			}
		} else {
			// Crear tabla nueva
			$q6 = "SELECT * INTO " . $postrans_name . " FROM pos_transtmp";
			// add log
			//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q6." -".PHP_EOL); fclose($fp); 
			// end log
			if ($sqlca->query($q6) < 0) {
				echo "Error creando tabla nueva para pos_trans mensual\n";
				return false;
			}
		}

		// Elimina registros de pos_transtmp
		$q7 = "DELETE FROM pos_transtmp";
		// add log
		//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q7." -".PHP_EOL); fclose($fp); 
		// end log
		$rs = $sqlca->query($q7);
		if ($rs < 0) {
			echo "Error eliminando registros antiguos de pos_transtmp\n";
			return false;
		}

		// Aumenta el numero de turno
		$q8 = "UPDATE pos_aprosys SET ch_posturno=ch_posturno+1 WHERE da_fecha='" . $fecha_aprosys . "'";
		// add log
		//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$q8." -".PHP_EOL); fclose($fp); 
		// end log
		$rs = $sqlca->query($q8);
		if ($rs < 0) {
			echo "Error actualizando numero de turno\n";
			return false;
		}
    	} else {
		echo "No hay dia abierto. Por favor verifique.\n";
    	}

    	return true;
}

function cierre_dia() {
	global $sqlca;
    
	// Busca dia abierto
	$rs = $sqlca->query("SELECT da_fecha,to_char(now(),'DD/MM/YYYY HH24:MI') FROM pos_aprosys WHERE ch_poscd='A'");
	if ($rs < 0) {
		echo "Error consultando dia abierto\n";
		return false;
	}
    
	if ($sqlca->numrows() > 0) {
		$aprosys = $sqlca->fetchRow();
		$fecha_aprosys = $aprosys[0];
		$now = $aprosys[1];
		$fecha_actual_anio = substr($fecha_aprosys, 0, 4);
		$fecha_actual_mes = substr($fecha_aprosys, 5, 2);
		$fecha_actual_dia = substr($fecha_aprosys, 8, 2);

		if ($sqlca->query("SELECT to_date('" . $fecha_aprosys . "', 'YYYY-MM-DD') + interval '1 day', to_char((to_date('" . $fecha_aprosys . "', 'YYYY-MM-DD') + interval '1 day'), 'D')") < 0) {
		    echo "Error obteniendo fecha nueva\n";
		    return false;
		}
		$row = $sqlca->fetchRow();
		$fecha_nueva = $row[0];
		$fecha_nueva_anio = substr($fecha_nueva, 0, 4);
		$fecha_nueva_mes = substr($fecha_nueva, 5, 2);
		$fecha_nueva_dia = substr($fecha_nueva, 8, 2);
		$fecha_nueva_semana = $row[1];

		$postrans_name = "pos_trans" . $fecha_actual_anio . $fecha_actual_mes;
		$fileprefix = "/tmp/imprimir/cierred" . date("dmYHis");
    	} else {
		echo "No hay dia abierto. Por favor verifique.\n";
		return false;
    	}

    	// FALTA: Impresion de wincha de cierre de dia
	$rs = $sqlca->query("SELECT pos FROM pos_cfg");

	if ($rs < 0) {
		echo "Error consultando puntos de venta\n";
		return false;
	}

	while ($row = $sqlca->fetchRow())
		$poss[] = $row[0];

	foreach ($poss as $pos) {
		$file = $fileprefix . $pos;
		//echo "-File2-<<".$fileprefix.">>"."<<".$pos.">>";
		genera_cierre_dia($pos,$fecha_aprosys,$postrans_name,$now,$file);
		imprimir_archivo($file,$pos);
	}

	// Importa parte de venta
    	$rs = $sqlca->query("SELECT combex_fn_contometros_auto(to_date('" . $fecha_aprosys . "', 'YYYY-MM-DD'))");
    	if ($rs < 0) {
		echo "Error importando parte de venta\n";
		return false;
    	}
    
    	// Actualiza pos_aprosys cerrando todos los dias
    	$rs = $sqlca->query("UPDATE pos_aprosys SET ch_poscd='S'");
    	if ($rs < 0) {
		echo "Error cerrando dias en aprosys\n";
		return false;
    	}

    	// Cambio de MES
    	if ($fecha_nueva_dia == "01" && $fecha_nueva_mes != "01") {
		$rs = $sqlca->query("UPDATE inv_saldoalma SET stk_stock" . $fecha_nueva_mes . "=stk_stock" . $fecha_actual_mes . ", stk_costo" . $fecha_nueva_mes . "=stk_costo" . $fecha_actual_mes . " WHERE stk_periodo='" . $fecha_actual_anio . "'");
		if ($rs < 0) {
		    echo "Error pasando saldos y costos a nuevo mes\n";
		    return false;
		}
    	}

    	// Cambio de ANIO
    	if ($fecha_nueva_dia == "01" && $fecha_nueva_mes == "01") {
		$sql = "INSERT INTO
		    		inv_saldoalma
		    		(
					stk_almacen,
					stk_periodo,
					art_codigo,
					stk_stockinicial,
					stk_costoinicial,
					stk_stock01,
					stk_costo01
		    		)
			SELECT
		    		stk_almacen,
		    		'" . $fecha_nueva_anio . "',
		    		art_codigo,
		    		stk_stock12,
		    		stk_costo12,
		    		stk_stock12,
		    		stk_costo12
			FROM
		    		inv_saldoalma
			WHERE
		    		stk_periodo='" . $fecha_actual_anio . "' ";

		$rs = $sqlca->query($sql);
		if ($rs < 0) {
	    		echo "Error insertando saldos anuales\n";
	    		return false;
		}
    	}
    
    	// Eliminar despachos de mas de 1 mes de antiguedad
    	if ($sqlca->query("DELETE FROM pos_nbastra_historico where hora < now() - interval '1 month'") < 0) {
		echo "Error eliminando despachos con mas de 1 mes de antiguedad\n";
		return false;
    	}
    
    	if ($sqlca->query("INSERT INTO pos_nbastra_historico SELECT * FROM pos_nbastra WHERE expoac='E'") < 0) {
		echo "Error moviendo despachos a historico\n";
		return false;
    	}
    
    	if ($sqlca->query("DELETE FROM pos_nbastra WHERE expoac='E'") < 0) {
		echo "Error eliminando despachos\n";
		return false;
   	}
    
    	// Copiar acumulados a anteriores e inicializarlos a cero.
    	$sql = "UPDATE
			pos_fptshe1
	    	SET
			nu_ant_galones_acumulados=nu_galones_acumulados,
			nu_ant_importe_acumulado=nu_importe_acumulado,
			nu_galones_acumulados=0,
			nu_importe_acumulado=0
	    	WHERE
			(nu_limite_galones!=0 OR nu_limite_importe!=0)
			AND
			(
		    		(ch_dia_de_corte='" . $fecha_nueva_dia . "' AND ch_tipo_periodo_acumular='M')
		    		OR (ch_dia_de_corte='" . $fecha_nueva_semana . "' AND ch_tipo_periodo_acumular='S')
			)";    
    	if ($sqlca->query($sql) < 0) {
		echo "Error copiando acumulados de saldos de tarjetas.\n";
		return false;
    	}
    
    	// Creacion del dia en pos_aprosys
    	if ($sqlca->query("INSERT INTO pos_aprosys (da_fecha, ch_posturno, ch_poscd) VALUES ('" . $fecha_nueva . "', '1', 'A')") < 0) {
		echo "Error insertando dia en aprosys\n";
		return false;
    	}
    
   	// FALTA: calculo de comisiones a planillas
    
    	// Inicializa numerador de tiradas en 1
    	if ($sqlca->query("UPDATE int_num_documentos SET num_numactual='1' WHERE num_tipdocumento='81'") < 0) {
		echo "Error inicializando numerador de tiradas a 1\n";
		return false;
    	}
    
    	// Copia el tipo de cambio del dia anterior
    	$sql = "INSERT INTO
			int_tipo_cambio
			(
		    		tca_moneda,
		    		tca_fecha,
		    		tca_compra_libre,
		    		tca_venta_libre,
		    		tca_compra_banco,
		    		tca_venta_banco,
		    		tca_compra_oficial,
		    		tca_venta_oficial
			)
	    	SELECT
				tca_moneda,
				'" . $fecha_nueva . "',
				tca_compra_libre,
				tca_venta_libre,
				tca_compra_banco,
				tca_venta_banco,
				tca_compra_oficial,
				tca_venta_oficial
	    		FROM
				int_tipo_cambio
	    		WHERE
				tca_fecha='" . $fecha_aprosys . "'";

    	if ($sqlca->query($sql) < 0) {
		echo "Error copiando tipo de cambio\n";
		return false;
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
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
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando tickets\n";
		return false;
	}

	$tickets = Array();
	
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Inicio de Wincha : ".$hora." -".PHP_EOL); fclose($fp); 
	// end log
	
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
	
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Fin de Wincha : ".$hora." -".PHP_EOL); fclose($fp); 
	// end log

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

	$sql =	"SELECT
			trim(pc_samba),
			trim(prn_samba),
			trim(ip) 
		FROM
			pos_cfg 
		WHERE
			impcierre = true
			AND trim(pos) = '$pos';";
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." - query: ".$sql." -".PHP_EOL); fclose($fp); 
	// end log
	
	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando POS\n";
		return false;
	}
	if ($sqlca->numrows()<1)
		return true;

	$row = $sqlca->fetchRow();
	$smbc = "lpr -H {$row[2]} -P {$row[1]} {$file}";

	//$fp = fopen("COMANDO.txt","a");
	//fwrite($fp, "-".$smbc."-".PHP_EOL);
	//fclose($fp);  

	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." ANTES DE IMPRIMIR POS: ".$pos." -".PHP_EOL); fclose($fp); 
	// end log

	//print_r($smbc);
	//eixt;

	exec($smbc);
	
	// add log
	//$hora = date("d-m-Y H:i:s"); $fp = fopen("log_cierre.txt","a"); fwrite($fp, "- Hora: ".$hora." DESPUES DE IMPRIMIR POS: ".$pos." -".PHP_EOL); fclose($fp); 
	// end log

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
