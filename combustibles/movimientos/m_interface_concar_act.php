<?php

function muestraError($mssql,$msg) {
	echo "<script>alert('" . $msg ."');</script>";
}

function verificaRUCS($rucs, $tabruc) {
	 
	$k = 0;
	$flag = 0;

	for ($i = 0; $i < count($rucs); $i++) {			

		if(trim($rucs[$i]['raz']) == ''){
			$rucs[$i]['raz'] = "X";
		}

		if(substr($rucs[$i]['ruc'],0,2)=="10" or substr($rucs[$i]['ruc'],0,2)=="15"){
			$atiptra = "N";
		}else{
			$atiptra = "J";		
		}

		$razo = $rucs[$i]['raz'];	
		$razonsocial = str_replace("'", "''",$razo);
						
		$inserta[$k] = "INSERT INTO $tabruc
						(avanexo,acodane,adesane,arefane,aruc,acodmon,aestado,atiptra,adocide,anumide) 
				VALUES
						('C', '".trim($rucs[$i]['ruc'])."', '".substr($razonsocial,0,39)."', 'X', '".trim($rucs[$i]['ruc'])."', 'MN','V','".$atiptra."','6', '".trim($rucs[$i]['ruc'])."');
				";
		$k++;		
	}
	return $inserta;
}

function ejecutarInsert($inserts, $tabcab, $tabdet, $tabcan) {
	// Parametros de conexión a SQL SERVER
	$objModel = new InterfaceConcarActModel();
	$Parametros = $objModel->obtenerParametros();

	$MSSQLDBHost = $Parametros[0];
	$MSSQLDBUser = $Parametros[1];
	$MSSQLDBPass = $Parametros[2];
	$MSSQLDBName = $Parametros[3];

	$temp_file = tempnam(sys_get_temp_dir(), 'phpmssqlbridge');
	//$temp_file = "/tmp/imprimir/phpmssqlbridge.log";
	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("file", $temp_file, "a")
	);

	$procpipes = array();

	$process = proc_open("java -jar /usr/local/lib/phpmssqlbridge.jar {$MSSQLDBHost} {$MSSQLDBUser} {$MSSQLDBPass} {$MSSQLDBName}", $descriptorspec, $procpipes);

	$arrInsertsHeader = @$inserts['tipo']['cabecera'];
	$arrInsertsDetail = @$inserts['tipo']['detalle'];
	$arrInsertsClient = @$inserts['clientes'];

	if (!is_resource($process)) {
		return 0;
	}

	if ( count($arrInsertsHeader) > 0 && count($arrInsertsDetail) > 0 ) {
		$k1 = "DELETE FROM ".$tabcab.";";//Cabecera
		$k2 = "DELETE FROM ".$tabdet.";";//Detalle
	} else {
		return 0;
	}

	if ( !empty($tabcan) ){
		$k3 = "DELETE FROM ".$tabcan.";";//Cliente
	}

	// Limpia tablas SQL
	fwrite($procpipes[0],$k1."\r\n");
	echo $k1 . "\r\n";
	fwrite($procpipes[0],$k2."\r\n");
	echo $k2 . "\r\n";
	fwrite($procpipes[0],$k3."\r\n");
	echo $k3 . "\r\n";

	//LOG - Insert's
	echo "<pre>";
	print_r($arrInsertsHeader);
	print_r($arrInsertsDetail);
	echo "</pre>";

	for ($i=0; $i<count($arrInsertsHeader); $i++) {
		fwrite($procpipes[0],$arrInsertsHeader[$i] . "\r\n");
	}

	for ($d=0; $d<count($arrInsertsDetail); $d++) {
		fwrite($procpipes[0],$arrInsertsDetail[$d] . "\r\n");
	}

	for ($c=0; $c<count($arrInsertsClient); $c++) {
		fwrite($procpipes[0],$arrInsertsClient[$c] . "\r\n");
	}

/*
	$ultima_linea = system("java -jar /usr/local/lib/phpmssqlbridge.jar {$MSSQLDBHost} {$MSSQLDBUser} {$MSSQLDBPass} {$MSSQLDBName}", $retval);

	// Imprimir informacion adicional
	echo '
	</pre>
	<hr />Ultima linea de la salida: ' . $ultima_linea . '
	<hr />Valor de retorno: ' . $retval;
*/

    fclose($procpipes[0]);
	$prv = proc_close($process);
	return 1;	
}

class InterfaceConcarActModel extends Model {

	function obtenerParametros() {
		global $sqlca;

		//$defaultparams = Array("0.0.0.0:1433","zidigital","20512963545","RSCONCAR_PRUEBA");

		$sql="
SELECT
 p1.par_valor,
 p2.par_valor,
 p3.par_valor,
 p4.par_valor
FROM
 int_parametros p1
 LEFT JOIN int_parametros p2 ON p2.par_nombre='concar_username'
 LEFT JOIN int_parametros p3 ON p3.par_nombre='concar_password'
 LEFT JOIN int_parametros p4 ON p4.par_nombre='concar_dbname'
WHERE
 p1.par_nombre='concar_ip'
		";

		if ($sqlca->query($sql) < 0)
			return $defaultparams;

		if ($sqlca->numrows() != 1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();

		return Array($reg[0],$reg[1],$reg[2],$reg[3]);
	}


	function agregaFecha($tipo, $almacen, $inicio, $fin) {
		global $sqlca;
		
		if($tipo == "1")
			$tip = "VEN"; //tipo : '1' => VENTAS Y CUENTAS POR COBRAR
		else
			$tip = "COM"; //tipo : '2' => COMPRAS

		$sql = "INSERT INTO mig_concar (tipo,ch_almacen,fecha_inicio,fecha_fin,ch_usuario, fec_actualizacion) 
				VALUES ('$tip','$almacen',to_date('$inicio','DD/MM/YYYY'),to_date('$fin','DD/MM/YYYY'),'{$_SESSION['auth_usuario']}', now());";		

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	}
	
	function verificaFecha($tipo, $almacen, $inicio, $fin) {
		global $sqlca;
		
		if($tipo == "1")
			$tip = "VEN"; //tipo : '1' => VENTAS Y CUENTAS POR COBRAR
		else
			$tip = "COM"; //tipo : '2' => COMPRAS

		$sql = "SELECT 1 FROM mig_concar WHERE ('$inicio' BETWEEN fecha_inicio AND fecha_fin OR '$fin' BETWEEN fecha_inicio AND fecha_fin) AND ch_almacen = '$almacen' AND tipo='$tip';";
		
		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();

		if ($sqlca->numrows() != 0)
			return 5;
	}

	function obtenerAlmacenes($codigo) {
		global $sqlca;
		
		$cond = '';
		if ($codigo != "") 
			$cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
		
		$sql = "SELECT ch_almacen, trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ".$cond." ORDER BY ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;
			
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}
		return $result;
	}

	function Empresa() {
		global $sqlca;
		
		$sql = "SELECT cod_empresa FROM concar_config;";

		if ($sqlca->query($sql) < 0) 
			return false;
			
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0];
		}
		return $result;
	}

	function interface_clientes_combustibles($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // CLIENTES COMBUSTIBLES
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabCan    = "CAN".$codEmpresa;

			
		//*************** Lista de Clientes RUC **************

		$cli = "
			SELECT 
				t.ruc as ruc, 
				SUBSTRING(first(r.razsocial),0,39) as razsocial
			FROM 
				$postrans t 
				LEFT JOIN ruc r USING (ruc) 
			WHERE 
				t.td = 'F'
				AND t.tipo = 'C'
				AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND t.es = '$almacen'
			GROUP BY 
				t.ruc;
			";

		//echo "CLIENTES COMBUSTIBLES: \n\n".$cli."\n\n";			
				
		if ($sqlca->query($cli) <= 0){
			$arrInserts['clientes'][0] = array();
			$arrClientesCombustible = array($arrInserts, $TabCan);
			return $arrClientesCombustible;
		}

		$rs = Array();
		$c7 = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rs[$i]['ruc'] = $a[0];
			$rs[$i]['raz'] = $a[1];
			++$c7;
		}	
	
		trigger_error("sql-CAN0081: {$c7}");
		$finalrucs = verificaRUCS($rs, $TabCan);
			
		for($j=0; $j<count($finalrucs); $j++) {
			$arrInserts['clientes'][$j] = $finalrucs[$j];
		}

		$arrClientesCombustible = array($arrInserts, $TabCan);
		return $arrClientesCombustible;
	}

	function interface_clientes_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // CLIENTES MARKET
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabCan    = "CAN".$codEmpresa;

			
		//*************** Lista de Clientes RUC **************

		$cli = "SELECT 
				t.ruc as ruc, 
				SUBSTRING(first(r.razsocial),0,39) as razsocial
			FROM 
				$postrans t 
				LEFT JOIN ruc r USING (ruc) 
			WHERE 
				t.td = 'F'
				AND t.tipo = 'M'
				AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND t.es = '$almacen'
				AND t.ruc NOT IN(SELECT 
							t.ruc
						FROM 
							$postrans t 
							LEFT JOIN ruc r USING (ruc) 
						WHERE 
							t.td = 'F'
							AND t.tipo = 'C'
							AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
							AND t.es = '$almacen'
						GROUP BY 
							t.ruc)
			GROUP BY 
				t.ruc;
			";

		//echo "CLIENTES MARKET: \n\n".$cli."\n\n";			
				
		if ($sqlca->query($cli) <= 0){
			$arrInserts['clientes'][0] = array();
			$arrClientesMarket = array($arrInserts, $TabCan);
			return $arrClientesMarket;
		}
			
		$rs = Array();
		$c7 = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rs[$i]['ruc'] = $a[0];
			$rs[$i]['raz'] = $a[1];
			++$c7;
		}	
	
		trigger_error("sql-CAN0081: {$c7}");
		$finalrucs = verificaRUCS($rs, $TabCan);
			
		for($j=0; $j<count($finalrucs); $j++) {
			$arrInserts['clientes'][$j] = $finalrucs[$j];
		}

		$arrClientesMarket = array($arrInserts, $TabCan);
		return $arrClientesMarket;
	}

	function interface_clientes_documentos($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // CLIENTES DOCUMENTOS MANUALES
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabCan    = "CAN".$codEmpresa;

			
		//*************** Lista de Clientes RUC **************

		$cli = "SELECT 
				cli.cli_ruc as ruc,
				SUBSTRING(first(cli.cli_razsocial),0,39) as razsocial
			FROM 
				fac_ta_factura_cabecera cab
				LEFT JOIN int_clientes cli USING (cli_codigo)
			WHERE 
				cab.ch_fac_tipodocumento IN ('10','35')
				AND cab.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND cab.ch_almacen = '$almacen'
				AND cli_codigo NOT IN(SELECT 
					t.ruc
				FROM 
					$postrans t 
					LEFT JOIN ruc r USING (ruc) 
				WHERE 
					t.td = 'F'
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND t.es = '$almacen'
				GROUP BY 
					t.ruc)
			GROUP BY
				cli.cli_ruc;
			";

		//echo "CLIENTES DOCUMENTOS MANUALES: \n\n".$cli."\n\n";			
				
		if ($sqlca->query($cli) <= 0){
			$arrInserts['clientes'][0] = array();
			$arrClientesManuales = array($arrInserts, $TabCan);
			return $arrClientesManuales;
		}

		$rs = Array();
		$c7 = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rs[$i]['ruc'] = $a[0];
			$rs[$i]['raz'] = $a[1];
			++$c7;
		}	
	
		trigger_error("sql-CAN0081: {$c7}");
		$finalrucs = verificaRUCS($rs, $TabCan);
			
		for($j=0; $j<count($finalrucs); $j++) {
			$arrInserts['clientes'][$j] = $finalrucs[$j];
		}

		$arrClientesManuales = array($arrInserts, $TabCan);
		return $arrClientesManuales;
	}

	function interface_ventas_combustible($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // VENTAS COMBUSTIBLE
		global $sqlca;

		$clientes = InterfaceConcarActModel::interface_clientes_combustibles($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual);
		
		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;

		$sql = "
SELECT
 venta_subdiario, 
 venta_cuenta_cliente, 
 venta_cuenta_impuesto, 
 venta_cuenta_ventas, 
 id_cencos_comb, 
 subdiario_dia,
 venta_cuenta_cliente_glp,
 venta_cuenta_ventas_glp,
 id_centro_costo_glp,
 cod_cliente,
 cod_caja    
FROM 
 concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		$vccliente   = $a[1];
		$vcimpuesto  = $a[2];
		$vcventas    = $a[3];	
		$vccencos    = $a[4]; 
		$opcion      = $a[5];
		$vclienteglp = $a[6];
		$vventasglp  = $a[7];
		$cencosglp   = $a[8];
		$cod_cliente = $a[9];
		$cod_caja    = $a[10];

		$sql = "
			SELECT * FROM (
				SELECT 
					to_char(date(dia),'YYMMDD') as dia,
					'$vccliente'::text as DCUENTA,
					'$cod_cliente'::text as codigo,
					' '::text as trans,
					'1'::text as tip,
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td 		= 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es		= '$almacen'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos 
				ORDER BY 
					dia
			) as A

			UNION 

			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos 
				ORDER BY 
					dia
			)
			UNION
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo  
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
				ORDER BY 
					dia 
			)--FIN DE LETRA A
			UNION--INICIO DE LETRA B
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vclienteglp'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo = 'C'  AND codigo='11620307' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos	
				ORDER BY 
					dia
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo = 'C'
					AND codigo ='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo 	= art_codigo
					AND es 		= '$almacen'
					AND t.usr 	= ''
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN TICKET BOLETA DE VENTA
			UNION --INICIO BOLETA ELECTRONICA DE VENTA
			(
			SELECT 
				to_char(date(dia),'YYMMDD') as dia,
				'$vccliente'::text as DCUENTA,
				'$cod_cliente'::text as codigo,
				' '::text as trans,
				'1'::text as tip,
				'D'::text as ddh, 
				round(sum(importe),2) as importe, 
				cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
				es as sucursal,
				SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				''::text as DCENCOS,
				'C'::text as tip2,
				'BV'::TEXT as doctype,
				''::text AS documento_referencia,
				''::text AS fe_referencia,
				0 AS nu_igv_referencia,
				0 AS nu_bi_referencia
			FROM 
				$postrans t
				LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE 
				td 			= 'B' 
				AND tipo	= 'C'
				AND codigo	!= '11620307'
				AND es		= '$almacen'
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND t.usr != ''
				AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
			GROUP BY 
				dia,
				es,
				cfp.nu_posz_z_serie,
				cfp.ch_posz_pos,
				SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			) 

			UNION 
			
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo  
					AND es = '$almacen'
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
				ORDER BY 
					dia 
			)--FIN DE LETRA C
			UNION--INICIO DE LETRA D
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vclienteglp'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo = 'C'  AND codigo='11620307' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh,
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo  
					AND es = '$almacen'
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN BOLETAS ELECTRONICAS DE VENTA Y LETRA DE D
			UNION --INICIO TICKETS FACTURAS DE VENTA Y LETRA DE E
			(
				SELECT
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)--FIN DE LETRA E
			UNION--INICIO DE LETRA F
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans 
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin'
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)--FIN DE TICKETS FACTURAS DE VENTAS Y LETRA F
			UNION --INICIO FACTURAS ELECTRONICAS DE VENTAS Y LETRA G
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION 
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia,
					'$vcimpuesto'::text as DCUENTA,
					''::text as codigo,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin'
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE LETRA G
			UNION--INICIO DE LETRA H
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'H'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe,  
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'H'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'H'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE FACTURAS ELECTRONICAS DE VENTAS Y LETRA H
			UNION --INICIO DE TICKETS FACTURA DE EXTORNOS Y LETRA I
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'I'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td 			= 'F'
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo 	= '11620307'
					AND es 		= '$almacen'
					AND t.usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'I'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS ,
					'I'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)--FIN DE LETRA I
			UNION--INICIO DE LETRA J
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'  AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS ,
					'D'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo != '11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)--FIN DE TICKETS FACTURA DE EXTORNOS
			UNION --INICIO DE FACTURAS ELECTRONICAS DE EXTORNOS
			(
				SELECT 
					to_char(DATE(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
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
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo = '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo = '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo 	= '11620307'
					AND es 		= '" . $almacen . "'
					AND t.usr != ''
					AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
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
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo = '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo = '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo 	= '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
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
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo = '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo = '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION--EXTORNOS DE FACTURAS ELECTRONICAS - LIQUIDOS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
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
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo != '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo != '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
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
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo != '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo != '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
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
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo != '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo != '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo != '11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo = art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS GLP TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vclienteglp'::text as DCUENTA, 
 '$cod_cliente'::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
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
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo = '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo = '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo = '11620307'
 AND date(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 AND es = '" . $almacen . "'
 AND t.usr != ''
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS GLP IGV
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vcimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
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
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo = '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo = '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo = '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS GLP SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vventasglp'::text as DCUENTA, 
 codigo_concar, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$cencosglp'::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
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
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo = '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo = '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
 interface_equivalencia_producto
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo = '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
 AND codigo = art_codigo 
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 codigo_concar,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS LIQUIDOS TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vclienteglp'::text as DCUENTA,
 '$cod_cliente'::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
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
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo != '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo != '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo != '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS LIQUIDOS IGV	
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vcimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
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
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo != '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo != '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo != '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS LIQUIDOS SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vventasglp'::text as DCUENTA, 
 codigo_concar, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$cencosglp'::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
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
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo != '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo != '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
 interface_equivalencia_producto
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo != '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND codigo = art_codigo
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 codigo_concar,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
			ORDER BY dia, venta, tip2, tip, trans, DCUENTA, ddh;";
			
		echo "VENTAS COMBUSTIBLE: \n\n".$sql."\n\n";	
		
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18), documento_referencia character varying(15) NULL, fe_referencia DATE NULL, nu_igv_referencia numeric(20,4), nu_bi_referencia numeric(20,4));";

		$sqlca->query($q1);

		$data = array();
		$correlativo = 0;
		$contador = '0000';  
		$k = 0; 		
	  
		echo "<script>console.log('" . json_encode( array($subdia) ) . "')</script>";
		echo "<script>console.log('" . json_encode( array($opcion) ) . "')</script>";
		echo "<script>console.log('" . json_encode( array($vccliente) ) . "')</script>";
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {		
				$data[] = $reg;		
	
				//reiniciar el numerador cuando sea un dia diferente
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				// if ((substr($reg[1],0,3) == substr($vccliente,0,3)) && $reg[12] != "C") { 
				if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 
					$k=1;

					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;					
				}
				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}			
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				if(empty($reg[15]))
					$fe_referencia = '01/01/1999';
				else
					$fe_referencia = trim($reg[15]);

				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[13])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '" . trim($reg[14]) . "', '" . $fe_referencia . "', " . trim($reg[16]) . ", " . trim($reg[17]) . ");", "concar_insert");	
			}
		}
		echo "<script>console.log('****** Etapa 1 ******')</script>";
		echo "<script>console.log('" . json_encode($data) . "')</script>";

		/* Verificar data */
		// $dataTmpConcar = array();
		// $que = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dcuenta, dsecue;";
		// if ($sqlca->query($que)>0){
		// 	while ($reg = $sqlca->fetchRow()){
		// 		$dataTmpConcar[] = $reg;
		// 	}			
		// }
		sleep(2);
		echo "<script>console.log('****** Etapa 2 ******')</script>";
		// echo "<script>console.log('" . json_encode($dataTmpConcar) . "')</script>";
		/* Fin verificar data */

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$que = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dcuenta, dsecue;";
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vccliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}

		// actualizar tabla tmp_concar sumando las diferencias al igv13818
		$k = 0;
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vcimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vcimpuesto' and trim(dcodane)='' AND dsubdia = '".trim($reg[0])."';", "queryaux1"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dcuenta, dsecue; ";					
		$arrInserts = Array();
		$pd = 0; 

		$fe_emision_referencia = "''";
		$no_tipo_documento_referencia = '';

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				$sNumeroCuenta = substr($reg['dcuenta'], 0, 2);
				//if($reg['dcuenta'] == '121203'){
				if ( $sNumeroCuenta == '12' ) {
					if(!empty($reg['documento_referencia'])){
						if(substr($reg['documento_referencia'],0,1) == 'F')
							$no_tipo_documento_referencia = 'FT';
						else if (substr($reg['documento_referencia'],0,1) == 'B')
							$no_tipo_documento_referencia = 'BV';
					}else{
						$no_tipo_documento_referencia = '';
					}
					if($reg['fe_referencia'] == '1999-01-01')
						$fe_emision_referencia = "''";
					else
						$fe_emision_referencia = "convert(datetime, '".substr($reg['fe_referencia'],0,19)."', 120)";
				}

				$ins = "INSERT INTO $TabSqlDet (
						dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc,dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
						) VALUES (
						'".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."', '".$reg[13]."', '', '".$reg[15]."', '".$reg[17]."', 0,0,'','',0,'','',0,0,0,'','','','','','" . $no_tipo_documento_referencia . "', '" . $reg['documento_referencia'] . "' , $fe_emision_referencia, " . $reg['nu_bi_referencia'] . ", " . $reg['nu_igv_referencia'] . ",'','', GETDATE());
				";
				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;						
			}
		}

		//+++++++++++++++++++++ Cabecera de Ventas PLAYA +++++++++++++++++++++
		
		$correlativo=0;		
		$pc = 0;
			
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
	
				//reiniciar el numerador cuando sea un dia diferente
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}

				if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 					
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=01 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
					$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);	
					$cabglosa = substr($reg[0],4,2)."-".substr($reg[0],2,2)."-".substr($reg[0],0,2);

					if($opcion==0) {
						$reg[10]=substr($reg[10],0,-2);
					}

					$ins = "INSERT INTO $TabSqlCab
						(	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
	       						'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$reg[7]."', '".$reg[6]."', 'V', 'S', '', '','','',''
	       					);";
					$arrInserts['tipo']['cabecera'][$pc] = $ins;
					$pc++;	
				}													
			}
		}			

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
				
		$arrInserts['clientes'] = $clientes[0]["clientes"];

		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet,$clientes[1]);

		return $rstado;		
	}

	function interface_ventas_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {// VENTAS MARKET
		global $sqlca;

		$clientes = InterfaceConcarActModel::interface_clientes_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual);

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3;// Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4;// Fecha inicio debe ser menor que fecha final					
		}

		$Anio 		= trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab 	= "CC".$codEmpresa.$Anio;// Tabla SQL Cabecera
		$TabSqlDet 	= "CD".$codEmpresa.$Anio;// Tabla SQL Detalle
		$TabCan    	= "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;		
		
		$sql = "
SELECT
 venta_subdiario_market,
 venta_cuenta_cliente_mkt,
 venta_cuenta_impuesto,
 venta_cuenta_ventas_mkt,
 id_centrocosto,
 subdiario_dia,
 venta_cuenta_cliente_lubri,
 codane_lubri,
 venta_cuenta_ventas_lubri,
 cod_cliente,
 cod_caja
FROM
 concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];				
		$cuentalub   = $a[6];
		$codanelub   = $a[7];
		$ventaslub   = $a[8];
		$cod_cliente = $a[9];
		$cod_caja    = $a[10];

		$sql = "
		SELECT * FROM 
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M' AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			) as K
			UNION
			(
			SELECT 
				to_char(date(t.dia),'YYMMDD') as dia, 
				'$vmimpuesto'::text as DCUENTA, 
				''::text as codigo,
				' '::text as trans,
				'2'::text as tip,
				'H'::text as ddh,
				round(sum(t.igv),2) as importe,
				cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
				t.es as sucursal , 
				'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
				'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
				''::text as DCENCOS,
				'A'::text as tip2,
				'TK'::TEXT AS doctype,
				''::text AS documento_referencia,
				''::text AS fe_referencia,
				0 AS nu_igv_referencia,
				0 AS nu_bi_referencia
			FROM 
				$postrans  t 
				LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
				LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE
				t.td = 'B' 
				AND t.tipo='M'  AND art.art_tipo!='01' 
				AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				AND t.es = '$almacen'
				AND t.usr = ''
			GROUP BY
				t.dia,
				t.es,
				cfp.nu_posz_z_serie,
				cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					''::text as codigo,--701101C01 solo se activa cuando no tipo!='01' y es base imponible
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'  AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION--FIN DE LETRA A
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M' AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'  AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'  AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN DE TICKETS BOLETAS Y LETRA B
			UNION--INICIO DE BOLETAS ELECTRONICAS Y LETRA C
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo,
					' '::text as trans,
					'2'::text as tip,
					'H'::text as ddh,
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE LETRA C
			UNION--INICIO DE LETRA D
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS Y LETRA D
			UNION--INICIO DE TICKETS FACTURAS Y LETRA E
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					t.ruc::text as codigo, 
					t.trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'  AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					t.trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'  AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					''::text as codigo,
					t.trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 					 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN DE LETRA E
			UNION--INICIO DE LETRA F
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA,  
					t.ruc::text as codigo, 
					t.trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)	
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					t.trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA,  
					''::text as codigo,
					t.trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 					 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN DE TICKETS FACTURAS Y LETRA F
			UNION--INICIO DE FACTURAS ELECTRONICAS Y LETRA G
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					t.ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)	
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					''::text as codigo,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 					 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE LETRA G
			UNION--INICIO DE LETRA H
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA,  
					t.ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS ,
					'H'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)		
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'H'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA,  
					''::text as codigo,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS ,
					'H'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto AS q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm = 'V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE LETRA H
			UNION --INICIO DE FACTURAS ELECTRONICAS DE EXTORNOS != '01'
			(
				SELECT 
					to_char(DATE(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'I'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo != '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo != '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND art.art_tipo != '01'
					AND es 		= '" . $almacen . "'
					AND t.usr != ''
					AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'I'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo != '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo != '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo != '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					''::text AS codigo_concar,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'I'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								" . $postrans . " AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo != '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo != '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'D'
					AND tipo='M'
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art.art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo != '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION--EXTORNOS DE FACTURAS ELECTRONICAS - ='01'
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'J'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								" . $postrans . " AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo = '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo = '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo = '01'
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'J'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo = '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo = '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo = '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					'' AS codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'J'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								" . $postrans . " AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo = '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo = '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo = art.art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo = '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS != '01' TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmcliente'::text as DCUENTA, 
 '$cod_cliente'::text as codigo,
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'K'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans AS p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND art.art_tipo != '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo != '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 AND es = '" . $almacen . "'
 AND t.usr != ''
 AND art.art_tipo != '01'
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS !=01 IGV
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'K'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo != '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo != '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo != '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS !='01' SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmventas'::text as DCUENTA, 
 '' AS codigo_concar, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$vmcencos'::text as DCENCOS,
 'K'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans AS p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo != '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo != '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
 AND codigo = art.art_codigo 
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo != '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS ='01' TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmcliente'::text as DCUENTA,
 '$cod_cliente'::text as codigo,
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'L'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo = '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo = '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo = '01'
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS ='01' IGV	
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'L'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans AS p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo = '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo = '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo = '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS ='01' SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$ventaslub'::text as DCUENTA, 
 '' AS codigo_concar,
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$vmcencos'::text as DCENCOS,
 'L'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo = '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo = '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND codigo = art.art_codigo
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo = '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
			ORDER BY dia, venta, tip2, tip, trans, DCUENTA;";

		echo "\n\nVENTAS MARKET: ".$sql."\n\n";	

		$centimo = "CREATE TABLE tmp_concar_centimo (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18), documento_referencia character varying(15) NULL, fe_referencia DATE NULL, nu_igv_referencia numeric(20,4), nu_bi_referencia numeric(20,4));";

		$sqlca->query($centimo);

		$correlativo = 0;
		$contador = '0000';  
		$k = 0;  
		$md = 0;     
		
		if ($sqlca->query($sql)<=0) { 	
			return NULL;	
		}

		if ($sqlca->query($sql)>0) { 												
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
				//reiniciar el numerador cuando sea un dia diferente
				/*
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}
				*/
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}

				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}		
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
							
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);

				if(empty($reg[15]))
					$fe_referencia = '01/01/1999';
				else
					$fe_referencia = trim($reg[15]);

				//$centimo2 = $sqlca->query("INSERT INTO tmp_concar_centimo VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[13])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', '', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ');", "concar_insert");	
				$centimo2 = $sqlca->query("INSERT INTO tmp_concar_centimo VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[13])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '" . trim($reg[14]) . "', '" . $fe_referencia . "', " . trim($reg[16]) . ", " . trim($reg[17]) . ");", "concar_insert");	
			
			}
		}

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;

		$diferencia = "SELECT * FROM tmp_concar_centimo;";

		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vmcliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}			

		// actualizar tabla tmp_concar sumando las diferencias al igv
		$k = 0;
		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vmimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vmimpuesto' and trim(dcodane)='' AND dsubdia = '".trim($reg[0])."';", "queryaux1"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar_centimo ORDER BY dsubdia, dcompro, dcuenta, dsecue; ";				
		$arrInserts = Array();
		$pd = 0;
		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				$sNumeroCuenta = substr($reg['dcuenta'], 0, 2);
				$no_tipo_documento_referencia = '';
				$fe_emision_referencia = "''";
				$sSerieNumeroDocumento = '';
				$fBaseImponible = 0.00;
				$fIGV = 0.00;
				if ( $sNumeroCuenta == '12' ) {
					$sSerieNumeroDocumento = $reg['documento_referencia'];
					$fBaseImponible = $reg['nu_bi_referencia'];
					$fIGV = $reg['nu_igv_referencia'];
					if(!empty($reg['documento_referencia'])){
						if(substr($reg['documento_referencia'],0,1) == 'F')
							$no_tipo_documento_referencia = 'FT';
						else if (substr($reg['documento_referencia'],0,1) == 'B')
							$no_tipo_documento_referencia = 'BV';
					}
					if($reg['fe_referencia'] != '1999-01-01'){
						$fe_emision_referencia = "convert(datetime, '".substr($reg['fe_referencia'],0,19)."', 120)";
					}
				}

				$ins = "
INSERT INTO " . $TabSqlDet . "(
dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, 
dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, 
digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
) VALUES (
'".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."',
'".$reg[13]."', '', '".$reg[15]."', '".$reg[17]."', 0,0,'','',0,'','',0,0,
0,'','','','','','".$no_tipo_documento_referencia."','".$sSerieNumeroDocumento."', ".$fe_emision_referencia.", ".$fBaseImponible.",".$fIGV.",'','', GETDATE());
				";
				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;
			}
		}

		$correlativo=0;
		$mc = 0;
 
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {	

				//reiniciar el numerador cuando sea un dia diferente
				/*
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}
				*/
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
														
				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 					
					$correlativo = $correlativo + 1;

					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}

					$ins = "INSERT INTO $TabSqlCab 
						( 	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
       							'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$reg[7]."', '".$reg[6]."', 'V', 'S', '','','','',''
						);";
					$arrInserts['tipo']['cabecera'][$mc] = $ins;
					$mc++;	
				}													
			}
		}

		$q5 = $sqlca->query("DROP TABLE tmp_concar_centimo");
		$arrInserts['clientes'] = $clientes[0]["clientes"];

		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet,$clientes[1]);

		return $rstado;		
	}
	
	function interface_cobrar_combustible($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {// CUENTAS POR COBRAR COMBUSTIBLE
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio		= trim($Anio);
		$codEmpresa	= trim($codEmpresa);
		$TabSqlCab 	= "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet 	= "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    	= "CAN".$codEmpresa;

		// VALIDANDO FECHA SI YA HA SIDO MIGRADA O NO		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;
		
		$sql = "
SELECT
 ccobrar_subdiario,
 venta_cuenta_cliente,
 venta_cuenta_impuesto,
 venta_cuenta_ventas,
 id_cencos_comb,
 subdiario_dia,
 cod_cliente,
 cod_caja
FROM
 concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		$vccliente   = $a[1];
		$vcimpuesto  = $a[2];
		$vcventas    = $a[3];	
		$vccencos    = $a[4];
		$opcion      = $a[5];
		$cod_cliente = $a[6];
		$cod_caja    = $a[7];
			
		$sql = "
		SELECT * FROM (
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			ROUND(SUM(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta,
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans
		WHERE 
			td			= 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND at		= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	!= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND at		= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			at,
			dia,
			es,
			caja
		) as A
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			ROUND(SUM(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||cast(trans as integer)::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE 
			td			= 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307' 
			AND es 		= '$almacen'
			AND at		!= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	!= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND at		!= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			trans,
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcimpuesto'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(sum(igv),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE 
			td 			= 'B'
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT 
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcventas'::text as DCUENTA, 
			codigo_concar, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(sum(importe-igv),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 				
			''::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans p
			LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
		WHERE 
			td 			= 'B'
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			codigo_concar,
			es,
			caja
		)
		UNION
		(
		SELECT 
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			round(sum(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE
			td 			= 'B'
			AND tipo 	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND at 		= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at		= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			round(sum(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||cast(trans as integer)::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE
			td 			= 'B'
			AND tipo 	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND at 		!= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at		!= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			trans,
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcimpuesto'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(sum(igv),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE
			td 		= 'B'
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT 
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcventas'::text as DCUENTA, 
			codigo_concar, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 				
			''::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans p
			LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
		WHERE 
			td 			= 'B'
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			codigo_concar,
			trans,
			caja,
			sucursal
		)--FIN DE TICKETS BOLETAS DE VENTAS
			/*
			UNION--INICIO DE BOLETAS ELECTRONICAS DE VENTAS
			(
				--BOLETAS PAGADAS EN EFECTIVO (COMBUSTIBLE)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					ROUND(SUM(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td			= 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307' 
					AND es 		= '$almacen'
					AND at		= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				--BOLETAS PAGADAS CON TARJETA (COMBUSTIBLE)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 7)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo 	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND at 		!= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'B'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 		= 'B'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					codigo_concar,
					es,
					caja
			)
			UNION--FIN DE COMBUSTIBLES Y EMPIEZA GLP
			(
				--BOLETAS PAGADAS EN EFECTIVO (GLP)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal, 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo 	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at 		= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				--BOLETAS PAGADAS CON TARJETA (GLP)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal, 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 7)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo 	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at 		!= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 			= 'B'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					codigo_concar,
					es,
					caja
			)--FIN DE BOLETAS ELECTRONICAS DE VENTAS
			*/		
			UNION--INICIO DE BOLETAS ELECTRONICAS DE VENTAS ¡¡¡AGRUPADAS COMO ASIENTOS DE VENTAS!!!
			(				
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					ROUND(SUM(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					'' as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td			= 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307' 
					AND es 		= '$almacen'	
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 				
					AND t.usr 	!= ''	
					AND t.tm    = 'V'									
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					ROUND(SUM(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					'' as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td			= 'B' 
					AND tipo	= 'C'
					AND codigo	= '11620307' 
					AND es 		= '$almacen'	
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 				
					AND t.usr 	!= ''	
					AND t.tm    = 'V'
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS DE VENTAS ¡¡¡AGRUPADAS COMO ASIENTOS DE VENTAS!!!
			UNION--INICIO DE TICKETS FACTURAS DE VENTAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo 	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td			= 'F'
					AND tm		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					codigo_concar,
					trans,
					caja,
					sucursal 
			)
			UNION--FIN DE GLP E INICIO DE SOLO COMBUSTIBLES
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'GLP'::TEXT::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					codigo_concar,
					trans,
					caja,
					sucursal 
			)--FIN DE TICKETS FACTURAS DE VENTAS
			UNION--INICIO FACTURAS ELECTRONICAS DE VENTAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo 	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td			= 'F'
					AND tm		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					codigo_concar,
					caja,
					sucursal,
					usr
			)
			UNION--FIN DE GLP E INICIO DE SOLO COMBUSTIBLES
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					codigo_concar,
					caja,
					sucursal,
					usr
			)--FIN DE FACTURAS ELECTRONICAS DE VENTAS
			UNION--INICIO EXTORNOS LIQUIDOS Y GLP BOLETAS ELECTRONICAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA,
					'$cod_cliente'::text as codigo,
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'NA'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'B'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'NA'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'B'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)--FIN EXTORNOS LIQUIDOS Y GLP BOLETAS ELECTRONICAS
			UNION--INICIO EXTORNOS FACTURAS ELECTRONICAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia,
					'$vccliente'::text as DCUENTA,
					ruc::text as codigo,
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'NA'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'F'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'NA'::TEXT AS doctype,--15 Numero de Columna
					'GLP'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'F'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			ORDER BY dia, venta, tip2, tip, trans, DCUENTA, ddh;";

		echo "<pre>";
		echo "\n\n CTA. COBRAR CUENTAS: ".$sql."\n\n";
		echo "<pre>";
	
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(8), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18),
			tarjeta character varying(1), td character varying(1), no_tipo_producto character varying(5), doctype character varying(5), docrefer character varying(20));";

		$sqlca->query($q1);

		$correlativo = 0;
		$contador = '0000';  
		$k = 0;       
			
		if ($sqlca->query($sql)<=0) {
			$sqlca->get_error();
			error_log("Estapa 0");
			return NULL;	
		}

		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {	
				
				//reiniciar el numerador cuando sea diferente dia
				/*if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}*/

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
																	
				if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;						
				}

				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}
										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				if($reg[12] == 'E')
					$extorno = "A";
				else
					$extorno = "S";
					
				if(empty($reg[13]) || $reg[13] == '' || $reg[13] == NULL)
					$reg[13] = "0";

				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[15])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', '".trim($extorno)."', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '".$reg[13]."', '".trim($reg[14])."', '".trim($reg[16])."', '".trim($reg[15])."', '".trim($reg[17])."');", "concar_insert");

			}
		}	
				
		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$que = "SELECT * FROM tmp_concar; ";

		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vccliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}
			
		// actualizar tabla tmp_concar sumando las diferencias al igv
		$k = 0;
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vcimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vcimpuesto' and dcodane='99999999999';", "queryaux2");
				}
			}
		}	
		
		// return false; //

		//******************************** CUENTAS POR COBRAR *****************************	
		// Datos de cuenta por cobrar

		$sql_cc = "SELECT ccobrar_subdiario, ccobrar_cuenta_cliente, ccobrar_cuenta_caja, codane, subdiario_dia,ccobrar_cuenta_cliente_new, ccobrar_cuenta_caja_new FROM concar_config;";

		if ($sqlca->query($sql_cc) < 0){
			error_log('Etapa 1: Obtenemos datos de cuenta por cobrar');
			return false;	
		}
	
		$a = $sqlca->fetchRow();
		$ccsubdiario 	= $a[0];
		$cchaber 		= $a[1];
		$ccdebe 		= $a[2];
		$codane 		= $a[3];
		$opcion         = $a[4];
		$cchaberglp     = $a[5];//GLP
		$ccdebeglp 		= $a[6];//GLP
		
		$detalle_boletas_pagadas_efectivo = false;
		$where_agrupar_documentos_efectivo = "";

		// Datos de tarjetas para concar			
		if($detalle_boletas_pagadas_efectivo == true){
			$sql_cc = "SELECT
						id_config, idtipodoc, idformapago, descripcion, cuenta10, cuenta12 
					FROM  
						concar_config_caja 
					ORDER BY 
						idtipodoc, idformapago;";
				
			if ($sqlca->query($sql_cc) < 0){
				error_log('Etapa 2: Obtenemos datos de tarjetas para concar');
				return false;
			}

			$data_tarjetas_concar = array();
			while ($reg = $sqlca->fetchRow()) {
				/*
				* TIPO DE DOCUMENTO 
				* B: Boleta 
				* F: Factura
				* 
				* FORMA DE PAGO
				* 0: Ticket efectivo
				* 1, 2, 3, 4, 5: Tipos de tarjetas
				* 
				*/ 
				$data_tarjetas_concar[$reg['idtipodoc'] . "-" . $reg['idformapago']] = $reg;
			}
			error_log(json_encode($data_tarjetas_concar));

			$where_agrupar_documentos_efectivo = "AND tarjeta = '0'";
		}				

		$sqlcc = "
			SELECT * FROM(		
				SELECT 
					dfeccom as dfeccom,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$cchaber' ELSE '$cchaberglp' END) AS dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0002' ELSE '0004' END) as dsecue,
					dcodane as dcodane,
					dcencos as dcencos,
					'H' as ddh,	
					dimport as dimport,
					dnumdoc as dnumdoc,
					'COB. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' ELSE 'GLP' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$ccsubdiario'::text as subday,
					'' as doctype,
					tarjeta as tarjeta, --Para identificar el tipo de tarjeta
					td as td, --Para identificar el tipo de documento
					doctype as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='P'	AND doctype != 'NA'
				) as A
				UNION
				(
				SELECT 
					dfeccom as dfeccom,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$cchaber' ELSE '$cchaberglp' END) AS dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0002' ELSE '0004' END) as dsecue,
					dcodane as dcodane,
					dcencos as dcencos,
					'D' as ddh,	
					dimport as dimport,
					dnumdoc as dnumdoc, --NOTAS DE CREDITO
					'COB. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' ELSE 'GLP' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$ccsubdiario'::text as subday,
					'NA' as doctype,
					tarjeta as tarjeta, --Para identificar el tipo de tarjeta
					td as td, --Para identificar el tipo de documento
					doctype as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='P'	AND doctype = 'NA' --NOTAS DE CREDITO
				)
				UNION
				(	
				SELECT 
					t.dfeccom as dfeccom,
					(CASE WHEN t.no_tipo_producto = 'COMBU' THEN '$ccdebe' ELSE '$ccdebeglp' END) AS dcuenta,
					(CASE WHEN t.no_tipo_producto = 'COMBU' THEN '0001' ELSE '0003' END) as dsecue,
					'".$codane."' as dcodane,
					t.dcencos as dcencos,
					'D' as ddh,
					SUM(t.dimport) - ( SELECT 
										 	COALESCE(SUM(tc.dimport),0)
										 FROM 
										 	tmp_concar tc
										 WHERE 
										 	tc.ddh='D' AND tc.dvanexo='P' AND tc.doctype = 'NA'
									 		AND tc.no_tipo_producto = t.no_tipo_producto
									 		AND tc.ddate = t.ddate
								   ) as dimport, --NO SUMAMOS FACTURAS ORIGINALES DE LAS NOTAS DE CREDITO
					'000000' as dnumdoc,
					'COB. '||(CASE WHEN t.no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' ELSE 'GLP' END) || ' ' || TO_CHAR(t.ddate, 'DD/MM/YYYY')  as dxglosa,
					'$ccsubdiario'::text as subday,
					'' as doctype,
					'' as tarjeta, --Para identificar el tipo de tarjeta
					'' as td, --Para identificar el tipo de documento
					'VR' as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar t
				WHERE 
					t.ddh='D' AND t.dvanexo='P' AND t.doctype != 'NA'
					$where_agrupar_documentos_efectivo --SOLO AGRUPA DOCUMENTOS QUE FUERON PAGADOS EN EFECTIVO
				GROUP BY 
					t.dfeccom,t.dcencos,t.no_tipo_producto, t.ddate
				)
				ORDER BY dfeccom, dsecue, dcuenta, dcodane desc;
		";
			
		echo "<pre>";
		echo "\n\n QUERY FINAL CTAS. COBRAR COMBUSTIBLES: ".$sqlcc."\n\n";			
		echo "</pre>";
		// return false; //
			
		if ($sqlca->query($sqlcc) < 0){
			error_log('Etapa 3: QUERY FINAL CTAS. COBRAR COMBUSTIBLES');
			return false;	
		} 
	
		$md = 0;
		$pc = 0;
		$arrInserts = Array();
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		$counterserie = ($FechaDiv[1] * 10000) + $num_actual;
		$cons = 0;//secuencial de detalles cobrar
		$diaserie = '';
		//$dxglosa = '';
		$no_documento = null;
		$nu_secue = null;
		$nu_dia = null;
		$cod_dia = null;
		$nu_subdia = null;

		while ($reg = $sqlca->fetchRow()) {

			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
			if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}

			// if($reg[8] != $dxglosa){
			// 	$counter = $counter + 1;
			// 	$dxglosa = $reg[8];
			// }

			if($reg[1] == $ccdebe || $reg[1] == $ccdebeglp){
				$counter = $counter + 1;
			}

			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}

			$nu_subdia = $reg[9].substr($reg[0], -2);

			if($reg[5]=="D" && $reg[10]==""){ //INGRESAMOS IMPORTE TOTALIZADO EN CABECERA
				$ins1 = "INSERT INTO $TabSqlCab
					(
						CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
					) VALUES (
						'".$nu_subdia."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
					);";

				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;
			}

			if($secue != $nu_secue){
				$cons = 0;
				$nu_secue = $secue;
			}
			
			if($detalle_boletas_pagadas_efectivo == true){
				//SI ES BOLETA O FACTURA Y SI PAGO CON TARJETA
				if(($reg['td'] == 'B' || $reg['td'] == 'F') && $reg['tarjeta'] != '0'){ 
					//reiniciar el numerador cuando sea diferente dia DSECUE
					$cons++;
					if($cons>0 and $cons<=9) {
						$secdet = "000".$cons;				
					} elseif ($cons>=10 and $cons<=99){
						$secdet = "00".$cons;
					} elseif ($cons>=100 and $cons<=999){
						$secdet = "0".$cons;
					} else {
						$secdet = $cons;
					}
								
					$cuenta10 = $data_tarjetas_concar[$reg['td'] . "-" . $reg['tarjeta']]['cuenta10']; //Obtenemos cuenta de concar_config_caja si fuera factura y si pago con tarjeta
					$ddh = "D"; //Obtenemos D (Debe) o H (Haber)
					if(empty($cuenta10) || $cuenta10 == '' || $cuenta10 == NULL){
						$cuenta10 = $reg[1];					
						$ddh = "D";
					}
					
					//Agregamos insert adicional para documentos pagados con tarjeta con la cuenta cambiada y obtenida de concar_config_caja
					$ins2 = "INSERT INTO $TabSqlDet 
					(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
						darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
						dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
					) VALUES ( 
						'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$cuenta10."', '".$reg[3]."', '', 'MN', 
						'".$ddh."', ".$reg[6].", '".$reg[13]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
						0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
					);";
					$arrInserts['tipo']['detalle'][$md] = $ins2;
					$md++;
				}
				//CERRAR SI ES BOLETA O FACTURA Y SI PAGO CON TARJETA
			}			

			//reiniciar el numerador cuando sea diferente dia DSECUE
			$cons++;

			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
				
			$cuenta10 = $reg[1]; //Obtenemos cuenta por defecto		
			$ddh = $reg[5]; //Obtenemos D (Debe) o H (Haber)

			$ins2 = "INSERT INTO $TabSqlDet 
				(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
					darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
					dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
				) VALUES ( 
					'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$cuenta10."', '".$reg[3]."', '', 'MN', 
					'".$ddh."', ".$reg[6].", '".$reg[13]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
					0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
				);";
		
			$arrInserts['tipo']['detalle'][$md] = $ins2;

			$md++;

		}

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, '');
		return $rstado;
	}

	function interface_liquidacion_caja($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {// CUENTAS LIQUIDACION DE CAJA
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio		= trim($Anio);
		$codEmpresa	= trim($codEmpresa);
		$TabSqlCab 	= "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet 	= "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    	= "CAN".$codEmpresa;

		// VALIDANDO FECHA SI YA HA SIDO MIGRADA O NO		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;

		$sql = "
SELECT 
 venta_subdiario,
 --venta_cuenta_cliente,
 --venta_cuenta_impuesto,
 --venta_cuenta_ventas,
 id_cencos_comb,
 subdiario_dia,
 cod_cliente,
 cod_caja
FROM
 concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = '71'; //Esto puede traerse de forma dinamica, ya sea ventas, cobranzas, etc
		//$vccliente   = $a[1];
		//$vcimpuesto  = $a[2];
		//$vcventas    = $a[3];	
		$vccencos    = $a[1];
		$opcion      = $a[2];
		$cod_cliente = $a[3];
		$cod_caja    = $a[4];

		//CUENTAS PARA LOS ASIENTOS
		$combustible_cuenta_caja = "101101";
		$glp_cuenta_caja         = "101102";
		$market_cuenta_caja      = "101103";

		$cuenta_ticket_efectivo  = "103101";

		$cuenta_tarjeta_visa             = "162401";
		$cuenta_tarjeta_american_express = "162402";
		$cuenta_tarjeta_mastercard       = "162403";
		$cuenta_tarjeta_dinners          = "162404";
		$cuenta_tarjeta_cmr              = "162405";
		$cuenta_tarjeta_ripley           = "162406";
		$cuenta_tarjeta_cheques_otros    = "162407";
		$cuenta_tarjeta_cheques_bbva     = "162408";
		$cuenta_tarjeta_metroplazos      = "162409";

		$combustible_cod_anexo = "0001";
		$glp_cod_anexo         = "0002";
		$market_cod_anexo      = "0003";
		//CUENTAS PARA LOS ASIENTOS

		//OBTENEMOS WHERE PARA ASIENTOS MARKET
		$sqlD0 = "
		SELECT
			PT1.ch_sucursal,
			PT1.dt_dia,
			PT1.ch_posturno,
			PT1.ch_codigo_trabajador
		FROM
			pos_historia_ladosxtrabajador PT1
		WHERE
			PT1.ch_tipo = 'M'
			AND PT1.dt_dia BETWEEN '$FechaIni' AND '$FechaFin'
			AND PT1.ch_codigo_trabajador NOT IN (
			SELECT
				PT2.ch_codigo_trabajador
			FROM
				pos_historia_ladosxtrabajador PT2
			WHERE
				PT2.ch_tipo = 'C'
				AND PT2.dt_dia=PT1.dt_dia
				AND PT2.ch_posturno=PT1.ch_posturno
			GROUP BY
				PT2.ch_sucursal,
				PT2.dt_dia,
				PT2.ch_codigo_trabajador
			)
		GROUP BY
			PT1.ch_sucursal,
			PT1.dt_dia,
			PT1.ch_posturno,
			PT1.ch_codigo_trabajador;
		";
			
		if ($sqlca->query($sqlD0) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$d = $sqlca->fetchRow();
			$textoarmado = $textoarmado."(ch_almacen = '".trim($d[0])."' AND dt_dia = '".trim($d[1])."' AND ch_posturno = '".trim($d[2])."' AND ch_codigo_trabajador = '".trim($d[3])."') OR ";
		}

		$textofinal = substr($textoarmado,0,-3);
		//CERRAR OBTENEMOS WHERE PARA ASIENTOS MARKET

		$sql = "
		--INICIO DE ASIENTOS PARA COMBUSTIBLE
		SELECT * FROM ( --TARJETAS AGRUPADAS
			SELECT
				to_char(date(dia),'YYMMDD') as dia, 		
				CASE
					WHEN t.at = '1' THEN $cuenta_tarjeta_visa::text
					WHEN t.at = '2' THEN $cuenta_tarjeta_american_express::text
					WHEN t.at = '3' THEN $cuenta_tarjeta_mastercard::text
					WHEN t.at = '4' THEN $cuenta_tarjeta_dinners::text
					WHEN t.at = '5' THEN $cuenta_tarjeta_cmr::text
					WHEN t.at = '6' THEN $cuenta_tarjeta_ripley::text
					WHEN t.at = '7' THEN $cuenta_tarjeta_cheques_otros::text
					WHEN t.at = '8' THEN $cuenta_tarjeta_cheques_bbva::text
					WHEN t.at = '9' THEN $cuenta_tarjeta_metroplazos::text
				END as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				SUM(t.importe) as importe,
				'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(t.es) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				t.at as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'COMBU'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				t.at as at, 
				g.tab_descripcion as descripcion			
			FROM
				$postrans t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
			WHERE
				t.codigo != '11620307'
				AND t.es = '$almacen'    
				AND t.dia between '$FechaIni' and '$FechaFin'
				AND t.tipo = 'C'
				AND t.fpago = '2'  
				AND t.td != 'N'
			GROUP BY 
				t.at,g.tab_descripcion,t.dia
			ORDER BY 
				t.at	
		) as A
		UNION ALL --TOTAL EFECTIVOS
		( 			
			SELECT 
				to_char(date(D.dt_dia),'YYMMDD') as dia, 		
				$cuenta_ticket_efectivo::text as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				sum(D.importe) as importe,
				'VENTA COMBUSTIBLE ' || substring(D.dt_dia::text from 9 for 2) || '/' || substring(D.dt_dia::text from 6 for 2) || '-' || substring(D.dt_dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(ch_almacen) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(D.dt_dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				'' as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'COMBU'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				''::text as at,
				'EFECTIVO SOLES'::text as descripcion  
			FROM 
				(   
					SELECT
						pos.ch_almacen,pos.dt_dia,pos.ch_posturno,pos.ch_codigo_trabajador, CASE WHEN ch_moneda='01' THEN sum(nu_importe) WHEN ch_moneda!='01' THEN sum(pos.nu_importe * tc.tca_venta_oficial) END as importe            
					FROM
						pos_depositos_diarios pos
						LEFT JOIN int_tipo_cambio tc ON (pos.dt_dia = tc.tca_fecha)
					WHERE
						ch_almacen='$almacen'
						and ch_valida='S' 
						and dt_dia between '$FechaIni' and '$FechaFin'
					GROUP BY
						ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,ch_moneda
				) D
			INNER JOIN (
					SELECT 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
					FROM 
						pos_historia_ladosxtrabajador PT
						INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1!='GL'
					GROUP BY 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
			) T ON (T.ch_sucursal = D.ch_almacen and T.ch_posturno = D.ch_posturno and T.ch_codigo_trabajador = D.ch_codigo_trabajador and T.dt_dia = D.dt_dia)
			GROUP BY 
				D.dt_dia
		) 
		--INICIO DE ASIENTOS PARA GLP
		UNION ALL --TARJETAS AGRUPADAS
		( 			
			SELECT
				to_char(date(dia),'YYMMDD') as dia, 		
				CASE
					WHEN t.at = '1' THEN $cuenta_tarjeta_visa::text
					WHEN t.at = '2' THEN $cuenta_tarjeta_american_express::text
					WHEN t.at = '3' THEN $cuenta_tarjeta_mastercard::text
					WHEN t.at = '4' THEN $cuenta_tarjeta_dinners::text
					WHEN t.at = '5' THEN $cuenta_tarjeta_cmr::text
					WHEN t.at = '6' THEN $cuenta_tarjeta_ripley::text
					WHEN t.at = '7' THEN $cuenta_tarjeta_cheques_otros::text
					WHEN t.at = '8' THEN $cuenta_tarjeta_cheques_bbva::text
					WHEN t.at = '9' THEN $cuenta_tarjeta_metroplazos::text
				END as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				SUM(t.importe) as importe,
				'VENTA GLP ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(t.es) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				t.at as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'GLP'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				t.at as at, 
				g.tab_descripcion as descripcion			
			FROM
				$postrans t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
			WHERE
				t.codigo = '11620307'
				AND t.es = '$almacen'    
				AND t.dia between '$FechaIni' and '$FechaFin'
				AND t.tipo = 'C'
				AND t.fpago = '2'  
				AND t.td != 'N'
			GROUP BY 
				t.at,g.tab_descripcion,t.dia
			ORDER BY 
				t.at
		) 
		UNION ALL --TOTAL EFECTIVOS
		( 			
			SELECT 
				to_char(date(D.dt_dia),'YYMMDD') as dia, 		
				$cuenta_ticket_efectivo::text as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				sum(D.importe) as importe,
				'VENTA GLP ' || substring(D.dt_dia::text from 9 for 2) || '/' || substring(D.dt_dia::text from 6 for 2) || '-' || substring(D.dt_dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(ch_almacen) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(D.dt_dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				'' as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'GLP'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				''::text as at,
				'EFECTIVO SOLES'::text as descripcion  
			FROM 
				(   
					SELECT
						pos.ch_almacen,pos.dt_dia,pos.ch_posturno,pos.ch_codigo_trabajador, CASE WHEN ch_moneda='01' THEN sum(nu_importe) WHEN ch_moneda!='01' THEN sum(pos.nu_importe * tc.tca_venta_oficial) END as importe            
					FROM
						pos_depositos_diarios pos
						LEFT JOIN int_tipo_cambio tc ON (pos.dt_dia = tc.tca_fecha)
					WHERE
						ch_almacen='$almacen'
						and ch_valida='S' 
						and dt_dia between '$FechaIni' and '$FechaFin'
					GROUP BY
						ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,ch_moneda
				) D
			INNER JOIN (
					SELECT 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
					FROM 
						pos_historia_ladosxtrabajador PT
						INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1='GL'
					GROUP BY 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
			) T ON (T.ch_sucursal = D.ch_almacen and T.ch_posturno = D.ch_posturno and T.ch_codigo_trabajador = D.ch_codigo_trabajador and T.dt_dia = D.dt_dia)
			GROUP BY 
				D.dt_dia
		)
		--INICIO DE ASIENTOS PARA MARKET
		UNION ALL --TARJETAS AGRUPADAS
		( 			
			SELECT
				to_char(date(dia),'YYMMDD') as dia, 		
				CASE
					WHEN t.at = '1' THEN $cuenta_tarjeta_visa::text
					WHEN t.at = '2' THEN $cuenta_tarjeta_american_express::text
					WHEN t.at = '3' THEN $cuenta_tarjeta_mastercard::text
					WHEN t.at = '4' THEN $cuenta_tarjeta_dinners::text
					WHEN t.at = '5' THEN $cuenta_tarjeta_cmr::text
					WHEN t.at = '6' THEN $cuenta_tarjeta_ripley::text
					WHEN t.at = '7' THEN $cuenta_tarjeta_cheques_otros::text
					WHEN t.at = '8' THEN $cuenta_tarjeta_cheques_bbva::text
					WHEN t.at = '9' THEN $cuenta_tarjeta_metroplazos::text
				END as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				SUM(t.importe) as importe,
				'MKT ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(t.es) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				t.at as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'MKT'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				t.at as at, 
				g.tab_descripcion as descripcion			
			FROM
				$postrans t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
			WHERE				
				t.es = '$almacen'    
				AND t.dia between '$FechaIni' and '$FechaFin'
				AND t.tipo = 'M'
				AND t.fpago = '2'  
				AND t.td != 'N'
			GROUP BY 
				t.at,g.tab_descripcion,t.dia
			ORDER BY 
				t.at
		)
		UNION ALL --TOTAL EFECTIVOS
		( 	
			SELECT 
				to_char(date(D.dt_dia),'YYMMDD') as dia, 		
				$cuenta_ticket_efectivo::text as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				sum(D.importe) as importe,
				'MKT ' || substring(D.dt_dia::text from 9 for 2) || '/' || substring(D.dt_dia::text from 6 for 2) || '-' || substring(D.dt_dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(ch_almacen) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(D.dt_dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				'' as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
				'MKT'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				''::text as at,
				'EFECTIVO SOLES'::text as descripcion  
			FROM 
			(		
				SELECT
					ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador, CASE WHEN ch_moneda='01' THEN sum(nu_importe) WHEN ch_moneda!='01' THEN sum(nu_importe * tpc.tca_venta_oficial) END as importe   
				FROM
					pos_depositos_diarios
					LEFT JOIN int_tipo_cambio tpc ON (tpc.tca_fecha=dt_dia)
				WHERE
					ch_almacen = '$almacen'
					AND ch_valida='S'
					AND (
						$textofinal
					)
				GROUP BY
					ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,ch_moneda
			) D
			GROUP BY 
				D.dt_dia
		)	
		ORDER BY dia,no_tipo_producto,at;";

		echo "<pre>";
		echo "\n\n ASIENTOS LIQUIDACION CAJA: ".$sql."\n\n";
		echo "<pre>";

		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(8), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
        dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
        dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
        dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18),
        tarjeta character varying(1), td character varying(1), no_tipo_producto character varying(5), doctype character varying(5), docrefer character varying(20));";

		$sqlca->query($q1);

		$dia = NULL;
		$correlativo = 0;
		$contador = '0000';  
		$k = 0;       
			
		if ($sqlca->query($sql)<=0) {
			$sqlca->get_error();
			error_log("Estapa 0");
			return NULL;	
		}

		error_log("************************FechaDiv*********************");
		error_log(json_encode($FechaDiv));

		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {	
				
				//reiniciar el numerador cuando sea diferente dia
				/*if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}*/
	
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
				
				if ($reg[0] != $dia) { 
					$dia = $reg[0];
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;						
				}
	
				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}
										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
	
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				if($reg[12] == 'E')
					$extorno = "A";
				else
					$extorno = "S";
					
				if(empty($reg[13]) || $reg[13] == '' || $reg[13] == NULL)
					$reg[13] = "0"; //AQUI CONVIERTE at '' de pagos en efectivo a 0
	
				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[15])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', '".trim($extorno)."', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '".$reg[13]."', '".trim($reg[14])."', '".trim($reg[16])."', '".trim($reg[15])."', '".trim($reg[17])."');", "concar_insert");
	
			}
		}

		//******************************** ASIENTOS LIQUIDACION CAJA *****************************	
		// Datos de asientos liquidacion caja

		$sql_cc = "SELECT codane, subdiario_dia FROM concar_config;";

		if ($sqlca->query($sql_cc) < 0){
			error_log('Etapa 1: Obtenemos datos de cuenta por cobrar');
			return false;	
		}

		$a = $sqlca->fetchRow();
		$codane 		= $a[0];
		$opcion         = $a[1];

		$sqlcc = "
			SELECT * FROM(		
				SELECT 
					dfeccom as dfeccom,
					dcuenta AS dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0002' WHEN no_tipo_producto = 'GLP' THEN '0004' WHEN no_tipo_producto = 'MKT' THEN '0006' END) as dsecue,
					--dcodane as dcodane,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$combustible_cod_anexo' WHEN no_tipo_producto = 'GLP' THEN '$glp_cod_anexo' WHEN no_tipo_producto = 'MKT' THEN '$market_cod_anexo' END) as dcodane,
					dcencos as dcencos,
					'D' as ddh,	
					dimport as dimport,
					--dnumdoc as dnumdoc,
					dfeccom as dnumdoc,
					'LIQ. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' WHEN no_tipo_producto = 'GLP' THEN 'GLP' WHEN no_tipo_producto = 'MKT' THEN 'MARKET' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$vcsubdiario'::text as subday,
					'' as doctype,
					tarjeta as tarjeta,
					td as td,
					'VR' as dtipdoc
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='P'	AND doctype != 'NA'
				) as A
				UNION ALL
				(	
				SELECT 
					t.dfeccom as dfeccom,
					CASE
						WHEN no_tipo_producto = 'COMBU' THEN '$combustible_cuenta_caja'::text
						WHEN no_tipo_producto = 'GLP' THEN '$glp_cuenta_caja'::text
						WHEN no_tipo_producto = 'MKT' THEN '$market_cuenta_caja'::text        
					END as dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0001' WHEN no_tipo_producto = 'GLP' THEN '0003' WHEN no_tipo_producto = 'MKT' THEN '0005' END) as dsecue,
					--'$codane' as dcodane,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$combustible_cod_anexo' WHEN no_tipo_producto = 'GLP' THEN '$glp_cod_anexo' WHEN no_tipo_producto = 'MKT' THEN '$market_cod_anexo' END) as dcodane,
					t.dcencos as dcencos,
					'H' as ddh,
					SUM(t.dimport) as dimport, 
					--'000000' as dnumdoc,
					dfeccom as dnumdoc,
					'LIQ. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' WHEN no_tipo_producto = 'GLP' THEN 'GLP' WHEN no_tipo_producto = 'MKT' THEN 'MARKET' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$vcsubdiario'::text as subday,
					'' as doctype,
					'' as tarjeta, --Para identificar el tipo de tarjeta
					'' as td, --Para identificar el tipo de documento
					'VR' as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar t
				WHERE 
					t.ddh='D' AND t.dvanexo='P' AND t.doctype != 'NA'
				GROUP BY 
					t.dfeccom,t.dcencos,t.no_tipo_producto, t.ddate
				)
				ORDER BY dfeccom, dsecue, dcuenta, dcodane desc;
		";
			
		echo "<pre>";
		echo "\n\n QUERY FINAL CTAS. COBRAR COMBUSTIBLES: ".$sqlcc."\n\n";			
		echo "</pre>";
		// return false; //
			
		if ($sqlca->query($sqlcc) < 0){
			error_log('Etapa 3: QUERY FINAL CTAS. COBRAR COMBUSTIBLES');
			return false;	
		} 

		$md = 0;
		$pc = 0;
		$arrInserts = Array();
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		$counterserie = ($FechaDiv[1] * 10000) + $num_actual;
		$cons = 0;//secuencial de detalles cobrar
		$diaserie = '';
		//$dxglosa = '';
		$no_documento = null;
		$nu_secue = null;
		$nu_dia = null;
		$cod_dia = null;
		$nu_subdia = null;

		while ($reg = $sqlca->fetchRow()) {

			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
			if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}

			// if($reg[8] != $dxglosa){
			// 	$counter = $counter + 1;
			// 	$dxglosa = $reg[8];
			// }

			if($reg[1] == $combustible_cuenta_caja || $reg[1] == $glp_cuenta_caja || $reg[1] == $market_cuenta_caja){
				$counter = $counter + 1;
			}

			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}

			$nu_subdia = $reg[9].substr($reg[0], -2);

			if($reg[5]=="H" && $reg[10]==""){ //INGRESAMOS IMPORTE TOTALIZADO EN CABECERA
				$ins1 = "INSERT INTO $TabSqlCab
					(
						CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
					) VALUES (
						'".$nu_subdia."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
					);";

				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;
			}

			if($secue != $nu_secue){
				$cons = 0;
				$nu_secue = $secue;
			}

			//reiniciar el numerador cuando sea diferente dia DSECUE
			$cons++;

			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
				
			$cuenta10 = $reg[1]; //Obtenemos cuenta por defecto		
			$ddh = $reg[5]; //Obtenemos D (Debe) o H (Haber)

			$ins2 = "INSERT INTO $TabSqlDet 
				(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
					darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
					dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
				) VALUES ( 
					'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$cuenta10."', '".$reg[3]."', '', 'MN', 
					'".$ddh."', ".$reg[6].", '".$reg[13]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
					0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
				);";
		
			$arrInserts['tipo']['detalle'][$md] = $ins2;

			$md++;

		}

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, '');
		return $rstado;
	}
	
	function interface_cobrar_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {  // CUENTAS POR COBRAR MARKET
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; //Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; //Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);
		
		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;		
		
		$sql = "
SELECT
 ccobrar_subdiario_mkt,
 venta_cuenta_cliente_mkt,
 venta_cuenta_impuesto,
 venta_cuenta_ventas_mkt,
 id_centrocosto,
 subdiario_dia,
 cod_cliente,
 cod_caja
FROM
 concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];
		$cod_cliente = $a[6];				
		$cod_caja    = $a[7];

		$sql = "
		SELECT * FROM 
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal, 
					'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago IN('1', '')
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja
			) as K
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal, 
					'$cod_caja'|| caja || '-' ||cast(trans as integer)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago='2'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					trans, 
					at,
					dia,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					codigo_concar,
					caja
			)
			UNION--INICIO DE BOLETAS ELECTRONICAS VENTA = V
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago IN('1', '')
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago='2'
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					codigo_concar,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS VENTA = V
			UNION--INICIO DE BOLETAS ELECTRONICAS TM (A y D)
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					-round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago IN('1', '')
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					-round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago='2'
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'D'::text as ddh, 
					-round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					codigo_concar,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS TM (A y D)
			UNION--INICIO DE TICKETS FACTURAS TM VENTAS = V
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal, 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,             
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND importe > 0
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					trans,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND importe > 0
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					trans,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND importe > 0
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					trans,
					es,
					codigo_concar,
					caja
			)--FIN DE TICKETS FACTURAS
			UNION--INICIO DE FACTURAS ELECTRONIOS VENTAS TM = 'V'
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm ='V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm ='V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm ='V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					es,
					codigo_concar,
					caja
			)--FIN DE FACTURAS ELECTRONICOS VENTAS TM = 'V'
			UNION--INICIO DE FACTURAS ELECTRONIOS VENTAS TM IN('A','D')
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					-round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A','D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					-round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A','D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip,  
					'D'::text as ddh, 
					-round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A','D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					es,
					codigo_concar,
					caja
			)--FIN DE FACTURAS ELECTRONICOS IN('A','D')
			ORDER BY venta,dia, tip, trans, ddh, DCUENTA;";
		
		echo "<pre>";
		echo "\n\n CTA. COBRAR MARKET: ".$sql."\n\n";
		echo "<pre>";

		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18),
			tarjeta character varying(1), td character varying(1), doctype character varying(5));";

		$sqlca->query($q1);
		
		$correlativo = 0;
		$contador = '0000';
		$k = 0;
		$md = 0;

		if ($sqlca->query($sql)<=0) { 	
			return NULL;	
		}    
			
		if ($sqlca->query($sql)>0) { 												
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {

				//reiniciar el numerador cuando sea diferente dia
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				if (trim($reg[1]) == $vmcliente) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);
				
				if(empty($reg[12]) || $reg[12] == '' || $reg[12] == NULL)
					$reg[12] = "0";

				$qx = $sqlca->query("INSERT INTO tmp_concar values ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[14])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'M', ' ', '".$reg[12]."', '".trim($reg[13])."', '".trim($reg[14])."');", "qxs");

			}
		}			

		//******************************** CUENTAS POR COBRAR *****************************

		$sql_cc = "SELECT ccobrar_subdiario_mkt, ccobrar_cuenta_cliente_mkt, ccobrar_cuenta_caja_mkt, codane, subdiario_dia FROM concar_config;";

		if ($sqlca->query($sql_cc) < 0) 
			return false;

		$a = $sqlca->fetchRow();

		$ccsubdiario 	= $a[0];
		$cchaber 		= $a[1];
		$ccdebe 		= $a[2];		
		$codane 		= $a[3];
		$opcion         = $a[4];

		$sqlcc = "
		SELECT * FROM(		
		SELECT
			dfeccom as dfeccom,
			'$cchaber' AS dcuenta,
			'0002' as dsecue,
			dcodane as dcodane,
			dcencos as dcencos,
			'H' as ddh,	
			dimport as dimport,
			dnumdoc as dnumdoc,
			'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
			'$ccsubdiario'::text as subday,
			doctype as doctype
		FROM 
			tmp_concar 
		WHERE 
			ddh='D' AND dvanexo='M'				
		) as A
		UNION
		(	
		SELECT 
			dfeccom as dfeccom,
			'$ccdebe' AS dcuenta,
			'0001' as dsecue,
			'".$codane."' as dcodane,
			dcencos as dcencos,
			'D' as ddh,
			SUM(dimport) as dimport,
			'000000' as dnumdoc,
			'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
			'$ccsubdiario'::text as subday,
			'VR' as doctype
		FROM 
			tmp_concar
		WHERE 
			ddh='D' AND dvanexo='M'
		GROUP BY 
			dfeccom,dcencos,dxglosa
		)
		ORDER BY dfeccom, dxglosa, dcuenta, dcodane desc;
		";
			
		echo "\n\n FINAL CTA. COBRAR MARKET: ".$sqlcc."\n\n";			
			
		if ($sqlca->query($sqlcc) < 0) 
			return false;	
	
		$md = 0;
		$pc = 0;	
		$cons = 0;	
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		$counterserie = ($FechaDiv[1] * 10000) + $num_actual;
		$cons = 0;//secuencial de detalles cobrar
		$diaserie = '';
		$no_documento = null;
		$nu_secue = null;
		$nu_subdia = null;
		
		while ($reg = $sqlca->fetchRow()) {	
			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
			if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}

			if($reg[1] == $ccdebe)	
				$counter = $counter + 1;

			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}
		
			$nu_subdia = $ccsubdiario.substr($reg[0], -2);

			if($reg[5]=="D"){
				$ins1 = "
				INSERT INTO $TabSqlCab (
					CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
				) VALUES (
					'".$nu_subdia."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
				);
				";
				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;
			}

			//reiniciar el numerador cuando sea diferente dia DSECUE
			$cons++;

			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
		
			$ins2 = "
			INSERT INTO $TabSqlDet (
				dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
				darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
				dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
			) VALUES ( 
				'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$reg[1]."', '".$reg[3]."', '', 'MN', 
				'".$reg[5]."', ".$reg[6].", '".$reg[10]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
				0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
			);
			";
			$arrInserts['tipo']['detalle'][$md] = $ins2;
			$md++;
		}
				
		$q5 = $sqlca->query("DROP TABLE tmp_concar");
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, '');
		return $rstado;
	}
	
	function Ventas_docManual($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {  // VENTAS DOC MANUAL
		global $sqlca;

		$clientes = InterfaceConcarActModel::interface_clientes_documentos($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual);

		if(trim($num_actual) == "")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3;// Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4;// Fecha inicio debe ser menor que fecha final					
		}

		$Anio 		= trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;		
		
		$sql = "
SELECT
 venta_subdiario_docManual, 	
 venta_cuenta_cliente_dMa, 	
 venta_cuenta_impuesto, 	
 venta_cuenta_ventas_dMa, 	
 id_centro_cos_dMa, 	
 subdiario_dia,	
 venta_cuenta_cliente_glp2,
 codane_glp2,
 venta_cuenta_ventas_glp2,
 cod_cliente 
FROM 
 concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];				
		$cuentalub   = $a[6];
		$codanelub   = $a[7];
		$ventaslub   = $a[8];
		$codcliente  = $a[9];

		$sql = "
			SELECT * FROM (
				SELECT 
					to_char(date(t.dt_fac_fecha),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$codcliente'::text as codigo, 
					' '::text as trans, 
					'3'::text as tip, 
					'D'::text as ddh, 
					round(sum(d.nu_fac_valortotal),2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal, 
					t.ch_fac_seriedocumento || '-' ||MIN(cast(t.ch_fac_numerodocumento as integer))||'-'||MAX(cast(t.ch_fac_numerodocumento as integer))::text as dnumdoc,
					'$vmsubdiario'::text as subdiario,
					''::text as DCENCOS ,
					'B'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref  
				FROM 
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
					LEFT JOIN int_articulos art ON (d.art_codigo=art.art_codigo) 
				WHERE 
					t.ch_fac_tipodocumento = '35' 
					AND date(t.dt_fac_fecha) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen, t.ch_fac_tipodocumento, t.ch_fac_seriedocumento
			) as k
			UNION(
				SELECT 
					to_char(date(t.dt_fac_fecha),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'3'::text as tip,  
					'H'::text as ddh, 
					round(sum(d.nu_fac_impuesto1),2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||MIN(cast(t.ch_fac_numerodocumento as integer))||'-'||MAX(cast(t.ch_fac_numerodocumento as integer))::text as dnumdoc,
					'$vmsubdiario'::text as subdiario,
					''::text as DCENCOS ,
					'B'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref    
				FROM 
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
					LEFT JOIN int_articulos art ON (d.art_codigo=art.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento = '35' 
					AND date(t.dt_fac_fecha) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					 t.dt_fac_fecha, t.ch_almacen, t.ch_fac_tipodocumento, t.ch_fac_seriedocumento
			)UNION(
				SELECT 
					to_char(date(t.dt_fac_fecha),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					FIRST(codigo_concar)::text as codigo, 
					' '::text as trans, 
					'3'::text as tip,  
					'H'::text as ddh, 
					CASE WHEN sum(d.nu_fac_importeneto) <= 0 THEN cast(0.00 as integer) ELSE round(sum(d.nu_fac_valortotal),2)-round(sum(d.nu_fac_impuesto1),2) END as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta, 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||MIN(cast(t.ch_fac_numerodocumento as integer))||'-'||MAX(cast(t.ch_fac_numerodocumento as integer))::text::text as dnumdoc,
					'$vmsubdiario'::text as subdiario,
					'$vmcencos'::text as DCENCOS ,
					'B'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref    
				FROM 
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
					LEFT JOIN int_articulos art ON (d.art_codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento = '35'  
					AND date(t.dt_fac_fecha) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen, t.ch_fac_tipodocumento, t.ch_fac_seriedocumento
			)UNION(--- EMPIEZA FACTURAS, NOTAS DE DEBITOS Y NOTAS DE CRÉDITOS
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					(CASE WHEN SUBSTRING(t.ch_fac_seriedocumento FROM '[A-Z]+') = 'B' THEN '$codcliente'::text ELSE c.cli_ruc::text END) as codigo, 
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip, 
					CASE
						WHEN t.ch_fac_tipodocumento = '20' THEN 'H'::text 
						ELSE 'D'
					END AS ddh,
					round(t.nu_fac_valortotal,2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal, 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,             
					''::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					(string_to_array(r.ch_fac_observacion2, '*'))[3]::text as tiporef,
					(string_to_array(r.ch_fac_observacion2, '*'))[2]::text as serieref,
					(string_to_array(r.ch_fac_observacion2, '*'))[1]::text as docuref
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					LEFT JOIN fac_ta_factura_complemento r ON (r.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND r.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND r.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN ('10','11','20') 
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.nu_fac_valortotal > 0.00 
					AND t.ch_almacen = '$almacen'
			)UNION(
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip, 
					CASE
						WHEN t.ch_fac_tipodocumento = '20' THEN 'D'::text 
						ELSE 'H'
					END AS ddh,
					round(t.nu_fac_impuesto1,2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref 
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
					LEFT JOIN int_articulos art ON (d.art_codigo = art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN ('10','11','20') 
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.nu_fac_valortotal>0 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen,t.ch_fac_seriedocumento, t.ch_fac_numerodocumento,t.nu_fac_impuesto1, t.ch_fac_tipodocumento
				ORDER BY 
					t.dt_fac_fecha
			)UNION(
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA,  
					CASE WHEN codigo_concar is null THEN '701101C01' ELSE codigo_concar::text END as codigo,
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip,  
					CASE
						WHEN t.ch_fac_tipodocumento = '20' THEN 'D'::text 
						ELSE 'H'
					END AS ddh,
					--CASE WHEN sum(d.nu_fac_importeneto) is null THEN cast(0.00 as decimal) ELSE round(FIRST(t.nu_fac_valortotal),2)-round(FIRST(t.nu_fac_impuesto1),2) END as importe, --Agregado para que redondee correctamente
					CASE WHEN sum(d.nu_fac_importeneto) is null THEN cast(0.00 as decimal) ELSE round(sum(d.nu_fac_valortotal) - sum(d.nu_fac_impuesto1),2) END as importe, --CAMBIO
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta, 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,
					'$vmcencos'::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref  
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
					LEFT JOIN int_articulos art ON (d.art_codigo = art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN ('10','11','20') 
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.nu_fac_valortotal > 0
					AND t.ch_almacen = '$almacen'
					--AND q.art_codigo IS NULL
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen,t.ch_fac_seriedocumento, t.ch_fac_numerodocumento, codigo_concar, t.ch_fac_tipodocumento
				ORDER BY 
					t.dt_fac_fecha
			)UNION(--- EMPIEZA DOCUMENTOS ANULADOS FACTURAS, NOTAS DE DEBITOS Y NOTAS DE CRÉDITOS
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					'0001'::text as codigo,
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip, 
					'D'::text as ddh,
					round(t.nu_fac_valortotal,2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal, 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,             
					''::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref    
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN('10','11','20','35')
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
					AND t.nu_fac_valortotal <= 0
				GROUP BY 
					t.dt_fac_fecha, t.ch_fac_numerodocumento,t.ch_fac_seriedocumento,c.cli_ruc,t.ch_almacen,t.nu_fac_valortotal, t.ch_fac_tipodocumento 
				ORDER BY 
					t.dt_fac_fecha
			)

		ORDER BY dia, tip2, tip, tipodoc, dnumdoc, DCUENTA, ddh;";

		echo "<pre>";
		echo "VENTAS MANUALES: \n\n".$sql."\n\n";	
		echo "</pre>";

		$centimo = "CREATE TABLE tmp_concar_centimo (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(20), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(15), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18), dtidref character varying(2), dndoref character varying(30), dfecref date);";

		$sqlca->query($centimo);
                  
		$correlativo = 0;
		$contador = '0000';  
		$k = 0;  
		$md = 0;     
			
		if ($sqlca->query($sql)>0) {										
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
				//validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}		
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
						
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
						
				if($reg[13] == '10')
					$tipodoc = 'FT';
				elseif($reg[13] == '11')
					$tipodoc = 'ND';
				elseif($reg[13] == '20')
					$tipodoc = 'NA';
				elseif($reg[13] == '35')
					$tipodoc = 'BV';

				if($reg[6] <= 0){
					$cflag = "N";
					$cabglosam = "ANULADA ".$tipodoc." - ".$reg[9];
				}else{
					$cflag = "S";
					$cabglosam = "VENTA MANUAL ".substr($reg[0],4,2)."-".substr($reg[0],2,2)."-".substr($reg[0],0,2);				
				}

				if(empty($reg[14]))
					$tiporef = "";
				else
					$tiporef = $reg[14];

				if(empty($reg[15]))
					$serieref = "";
				else
					$serieref = $reg[15];

				if(empty($reg[16]))
					$docuref = "";
				else
					$docuref = $reg[16];

				$centimo2 = $sqlca->query("INSERT INTO tmp_concar_centimo VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($tipodoc)."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."','".trim($serieref)."', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', '".trim($docuref)."', '".trim($tiporef)."');", "concar_insert");
			}
		}

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$diferencia = "SELECT * FROM tmp_concar_centimo ORDER BY dsubdia, dcompro, dcuenta, dsecue;";
		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vmcliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = $imp;
		}

		// error_log( json_encode($vec) );
		// return false;

		// actualizar tabla tmp_concar sumando las diferencias al igv
		$k = 0;
		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vmimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vmimpuesto' and trim(dcodane)='' AND trim(dsubdia) = '".trim($reg[0])."';", "queryaux2"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// return false;

		// Nueva forma de corrección de centimos
		$arrData = array();
	    $sSerieNumeroDocumento = '';
	    $fTotal4070 = 0;
	    $fTotal12 = 0;
		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
		        if( substr($reg[4],0,2) == '12' && $sSerieNumeroDocumento != $reg[11]){
		            $fTotal4070 = 0.00;
		            $fTotal12 = $reg[9];
		            $sSerieNumeroDocumento = $reg[11];
		        }

		        if( substr($reg[4],0,2) != '12' && $sSerieNumeroDocumento == $reg[11]){
		            $fTotal4070 += $reg[9];
		        }

		        if ( substr($reg[4],0,2) == '40' && $sSerieNumeroDocumento == $reg[11] ){//Solo se restará a la cuenta IGV 40
		            $arrData = array(
		            	"subdia" => $reg[0],
		            	"dcompro" => $reg[1],
		                "documento" => $reg[11],
		                "cuenta" => $reg[4],
		                "importe" => $reg[9],
		            );
		        }

		        if ( $sSerieNumeroDocumento == $reg[11] && ($fTotal4070 == ($fTotal12 + 0.01) || $fTotal4070 == ($fTotal12 - 0.01)) ) {
		            if( $fTotal12 > $fTotal4070) {
		            	$fTotal = (double)$arrData['importe'] + 0.01;
						$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$fTotal." WHERE dcompro='".trim($arrData['dcompro'])."' AND dcuenta='" . $arrData['cuenta'] . "' AND trim(dcodane)='' AND trim(dsubdia)='".trim($arrData['subdia'])."';", "queryaux2");
		            } else {
		            	$fTotal = (double)$arrData['importe'] - 0.01;
		            	$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$fTotal." WHERE dcompro='".trim($arrData['dcompro'])."' AND dcuenta='" . $arrData['cuenta'] . "' AND trim(dcodane)='' AND trim(dsubdia)='".trim($arrData['subdia'])."';", "queryaux2");
		            }
		        }
		    }
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar_centimo ORDER BY dsubdia, dcodane2, dcompro, dcuenta, dsecue; ";
		$arrInserts = Array();
		$pd = 0;
		$rc = 0;

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){

				if($reg[14] != ''){

					if($reg[25] == "01" || $reg[25] == "10")
						$dtidref = "10";
					elseif($reg[25] == "03" || $reg[25] == "35")
						$dtidref = "35";

					$reftip[$rc] 	= $dtidref;
					$refserie[$rc] 	= $reg[14];
					$refnum[$rc] 	= $reg[24];

					$rc++;
				}
			}
		}

		/* ESTO ES PARA SACAR EL IGV Y BASE IMPONIBLE DE UNA NOTA DE CREDITO AMARRADA A UNA FACTURA MANUAL */

		$complemento = "
		SELECT * FROM (
			SELECT
				'".$vmcliente."'::TEXT AS tipo,
				ch_fac_seriedocumento||'-'||ch_fac_numerodocumento AS docu,
				nu_fac_impuesto1 AS igv,
				nu_fac_valorbruto AS bi,
				dt_fac_fecha AS fe_emision_ref,
				'' AS orden
			FROM
				fac_ta_factura_cabecera
			WHERE
		";

		if (count($refserie) > 0) { 
			$complemento .= "
				ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN(";
			for ($i = 0; $i < count($refserie); $i++) {
				$serdocu = $reftip[$i].$refserie[$i].$refnum[$i];
				if ($i > 0)
					$complemento .= ",";
				$complemento .= "'" . pg_escape_string($serdocu) . "'";
			}
			$complemento .= ") ";
		}

		$complemento .= "
		) AS A UNION ALL(
		SELECT
			'".$vmcliente."'::TEXT AS tipo,
			usr AS docu,
			ROUND(igv, 2) AS igv,
			ROUND((importe - igv), 2) AS bi,
			fecha::date AS fe_emision_ref,
			SUBSTR(TRIM(usr), 6) AS orden
		FROM
			pos_trans201612
		WHERE
		";
		if (count($refserie) > 0) { 
			$complemento .= "
				'10'||SUBSTR(TRIM(usr), 0, 5)||SUBSTR(TRIM(usr), 6) IN (";
			for ($i = 0; $i < count($refserie); $i++) {
				$serdocu = $reftip[$i].$refserie[$i].$refnum[$i];
				if ($i > 0)
					$complemento .= ",";
				$complemento .= "'" . pg_escape_string($serdocu) . "'";
			}
			$complemento .= ") OR '35'||SUBSTR(TRIM(usr), 0, 5)||SUBSTR(TRIM(usr), 6) IN (";
			for ($i = 0; $i < count($refserie); $i++) {
				$serdocu = $reftip[$i].$refserie[$i].$refnum[$i];
				if ($i > 0)
					$complemento .= ",";
				$complemento .= "'" . pg_escape_string($serdocu) . "'";
			}
			$complemento .= ")";
		}

		$complemento .= "
		)
		ORDER BY
			orden;
		";

//		echo "Complemento: ".$complemento;

		$sqlca->query($complemento);

		$datos = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$r = $sqlca->fetchRow();
			$datos[$i]['tipo'] 				= $r['tipo'];
			$datos[$i]['docu'] 				= $r['docu'];
			$datos[$i]['igv'] 				= $r['igv'];
			$datos[$i]['bi'] 				= $r['bi'];
			$datos[$i]['fe_emision_ref'] 	= $r['fe_emision_ref'];
		}

		$cr				= 0;
		$dtidref 		= null;
		$serieynumeref 	= null;
		$fe_emision_ref = "''";
		$compleigv 		= 0.00;
		$complebi 		= 0.00;
/*
		echo "<pre>";
		var_dump($datos);
		echo "</pre>";
*/
		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				if($reg[9] <= 0)
					$cflag = 'N';
				else
					$cflag = 'S';

				if($reg[14] != ''){//SERIE REFERENCIA

					if($reg[25] == "01" || $reg[25] == "10")
						$dtidref = "FT";
					elseif($reg[25] == "03" || $reg[25] == "35")
						$dtidref = "BV";
					else
						$dtidref = "";

//echo "\n1:".$datos[$cr]['tipo'].'='.$reg[4]."\n";
//echo "2:".$datos[$cr]['docu'].'='.$reg['darea'].'-'.$reg[24]."\n";

					if($datos[$cr]['tipo'] == $reg[4] && $datos[$cr]['docu'] == $reg['darea'].'-'.$reg[24]){
						$serieynumeref 	= $reg['darea'].'-'.$reg[24];
						$fe_emision_ref = "convert(datetime, '".substr($datos[$cr]['fe_emision_ref'],0,19)."', 120)";
						$compleigv 		= $datos[$cr]['igv'];
						$complebi 		= $datos[$cr]['bi'];

						$cr++;

					}else{
						$dtidref = '';
						$serieynumeref = null;
						$fe_emision_ref = "''";
						$compleigv = 0.00;
						$complebi = 0.00;
					}
				}else{
					$dtidref = '';
					$serieynumeref = null;
					$fe_emision_ref = "''";
					$compleigv = 0.00;
					$complebi = 0.00;
				}

				$ins = "INSERT INTO $TabSqlDet 
					(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, 
						dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, 
						digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
					) VALUES (
						'".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."', '".$reg[13]."', '', '".$cflag."', '".$reg[17]."', 0,0,'','',0,'','',0,0,0,'','','','','', '".$dtidref."', '".$serieynumeref."', $fe_emision_ref, ".$complebi.", ".$compleigv.",'','', GETDATE());";

				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;					
			}
		}

		$correlativo=0;
		$mc = 0; 
		if ($sqlca->query($sql)>0) {		
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {	
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}		
															
				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 							

					$correlativo = $correlativo + 1;

					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
							
					$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);			
						
					if($reg[13] == '10')
						$tipodoc = 'FT';
					elseif($reg[13] == '11')
						$tipodoc = 'ND';
					elseif($reg[13] == '20')
						$tipodoc = 'NA';
					elseif($reg[13] == '35')
						$tipodoc = 'BV';

					if($reg[6] <= 0){
						$cflag = "N";
						$cabglosam = "ANULADA ".$tipodoc." - ".$reg[9];
					}else{
						$cflag = "S";
						$cabglosam = "VENTA MANUAL ".substr($reg[0],4,2)."-".substr($reg[0],2,2)."-".substr($reg[0],0,2);			
					}

					$ins = "INSERT INTO $TabSqlCab 
						( 	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
       							'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$cabglosam."', '".$reg[6]."', 'M','".$cflag."', '','','','',''
						);";

					$arrInserts['tipo']['cabecera'][$mc] = $ins;
					$mc++;	
				}													
			}
		} 

		$q5 = $sqlca->query("DROP TABLE tmp_concar_centimo");

		$arrInserts['clientes'] = $clientes[0]["clientes"];

		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, $clientes[1]);	
		
		return $rstado;		
	}

	function Cobrar_docManual($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {  // CUENTAS POR COBRAR DOC MANUAL
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}
				
///////////////////// de prueba luego borrar
//$Anio = "12";		
		
		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);
		if ($val == 5)
			return 5;		
		
		$sql = "SELECT 
				venta_subdiario_docManual, 	
				venta_cuenta_cliente_dMa, 	
				venta_cuenta_impuesto, 	
				venta_cuenta_ventas_dMa, 	
				id_centro_cos_dMa, 
				subdiario_dia 
			FROM 
				concar_config;";
		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];				

		$sql = "SELECT * FROM 
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'99999999999'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal, 
					MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'B' 
					AND tipo = 'M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
				GROUP BY 
					dia, es 
				ORDER BY 
					dia
			) as K 
			
			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'99999999999'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal , 
					MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'B' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
				GROUP BY 
					 dia, es 
				ORDER BY 
					dia
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta, 
					es as sucursal , 
					MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans
				WHERE 
					td = 'B' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
				GROUP BY 
					dia, es, codigo_concar 
				ORDER BY 
					dia
			) 
		
			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal, 
					''||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,             
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'F' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND importe>0 
					AND es = '$almacen' 
				GROUP BY 
					dia, trans,ruc,es 
				ORDER BY 
					dia
			)
						
			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal , 
					''||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'F' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND importe>0 
					AND es = '$almacen' 
				GROUP BY 
					dia, trans, es, ruc 
				ORDER BY 
					dia
			) 

			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta, 
					es as sucursal , 
					''||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans
				WHERE 
					td = 'F' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND importe>0  
					AND es = '$almacen' 
				GROUP BY 
					dia, trans, es, codigo_concar 
				ORDER BY 
					dia
			) 

			ORDER BY dia, tip, trans, ddh, DCUENTA;";
						
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(20), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18));";
		$sqlca->query($q1);
		
		$correlativo = 0;
		$contador = '0000';  
		$k = 0;  
		$md = 0;     
			
		if ($sqlca->query($sql)>0) { 												
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				if (trim($reg[1]) == $vmcliente) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);
				
				$qx = $sqlca->query("INSERT INTO tmp_concar values ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', 'TK', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'M', ' ');", "qxs");
			}
		}			

		//******************************** CUENTAS POR COBRAR *****************************
		
		$sql_cc = "SELECT ccobrar_subdiario_mkt, ccobrar_cuenta_cliente_mkt, ccobrar_cuenta_caja_mkt, codane, subdiario_dia FROM concar_config;";
		if ($sqlca->query($sql_cc) < 0) 
			return false;		
		$a = $sqlca->fetchRow();
		$ccsubdiario	= $a[0];
		$cchaber 	= $a[1];
		$ccdebe 	= $a[2];		
		$codane 	= $a[3];
		$opcion         = $a[4];

		$sqlcc = "SELECT * FROM	
			
			(		
				SELECT 
					dfeccom as dfeccom,
					'".$cchaber."' as dcuenta,
					'0002' as dsecue,	
					dcodane as dcodane,
					dcencos as dcencos,
					'H' as ddh,	
					dimport as dimport,
					dnumdoc as dnumdoc,	
					'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
					'$ccsubdiario'||substring(dfeccom::text from 5 for 2)::text as subday 	
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='M'				
			) as A
			
			UNION
			
			(	
				SELECT 
					dfeccom as dfeccom,
					'".$ccdebe."' as dcuenta,
					'0001' as dsecue,
					'".$codane."' as dcodane,
					dcencos as dcencos,
					'D' as ddh,
					SUM(dimport) as dimport,
					'000000' as dnumdoc,
					'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
					'$ccsubdiario'||substring(dfeccom::text from 5 for 2)::text as subday 
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='M'
				GROUP BY 
					dfeccom,dcencos,dxglosa 	
			)
			ORDER BY dfeccom, dcuenta, dcodane desc;	";
			
		//echo "+++ ".$sqlcc." +++\n\n";			
			
		if ($sqlca->query($sqlcc) < 0) 
			return false;	
	
		$md = 0;
		$pc = 0;	
		$cons = 1;	
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		
		while ($reg = $sqlca->fetchRow()) {	
			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
		/*	if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}*/
		
			if($reg[1] == $ccdebe)	
				$counter = $counter + 1;
			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}
			
			if($reg[5]=="D"){
				$ins1 = "INSERT INTO $TabSqlCab
					(
						CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
					) VALUES (
						'".$reg[9]."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
					);";
				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;	
			}
			//$reg[6]:dimport
			
			if($reg[3]==$codane) {
				$cons = 1;
			} else {
				$cons++;
			}
			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
			
			$ins2 = "INSERT INTO $TabSqlDet 
				(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
					darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
					dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
				) VALUES ( 
					'".$reg[9]."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$reg[1]."', '".$reg[3]."', '', 'MN', 
					'".$reg[5]."', ".$reg[6].", 'TK', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
					0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
				);";
			$arrInserts['tipo']['detalle'][$md] = $ins2;
			$md++;												
		}
				
		$q5 = $sqlca->query("DROP TABLE tmp_concar");	
		
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, $TabCan);	
		
		return $rstado;		
	}

	function interface_compras($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // COMPRAS
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio 		= trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;

		$sql = "
			SELECT 
				compra_subdiario,
				compra_cuenta_proveedor,
				compra_cuenta_impuesto,
				compra_cuenta_mercaderia,
				id_cencos_comb, 
				subdiario_dia,
				venta_cuenta_cliente_glp,
				venta_cuenta_ventas_glp,
				id_centro_costo_glp,
				cod_cliente,
				cod_caja    
			FROM 
				concar_config;
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		// $vccliente   = $a[1];
		// $vcimpuesto  = $a[2];
		// $vcventas    = $a[3];
		$vccencos    = $a[4];
		$opcion      = $a[5];
		$vclienteglp = $a[6];
		$vventasglp  = $a[7];
		$cencosglp   = $a[8];
		$cod_cliente = $a[9];
		$cod_caja    = $a[10];

		//CUENTAS PARA LOS ASIENTOS
		$compra_combustible_cuenta_proveedor = "421201";
		$compra_combustible_cuenta_bi        = "201101";

		$compra_glp_cuenta_proveedor = "421202";
		$compra_glp_cuenta_bi        = "201102";

		$compra_market_cuenta_proveedor = "421203";
		$compra_cuenta_impuesto         = "401101";
		$compra_market_cuenta_bi        = "201103";
		//CUENTAS PARA LOS ASIENTOS

		$sql = "
			SELECT * FROM(
				--COMPRAS DE COMBUSTIBLE
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_combustible_cuenta_proveedor'::text as DCUENTA,
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(sum(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				) AS t

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_cuenta_impuesto'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(c.pro_cab_impto1), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_combustible_cuenta_bi'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				--COMPRA DE GLP
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_glp_cuenta_proveedor'::text as DCUENTA,
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(sum(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_cuenta_impuesto'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(c.pro_cab_impto1), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_glp_cuenta_bi'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				--COMPRA DE MARKET
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_market_cuenta_proveedor'::text as DCUENTA,
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(sum(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles)
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_cuenta_impuesto'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(c.pro_cab_impto1), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles)
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_market_cuenta_bi'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles)
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)
			ORDER BY dia, trans, pro, ddh DESC;
		";
			
		echo "<pre>";		
		echo "COMPRAS: \n\n".$sql."\n\n";	
		echo "</pre>";
		
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(20), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18));";

		$sqlca->query($q1);

		$correlativo 	= 0;
		$contador 		= '0000';  
		$k 				= 0;
		$subdia 		= null;
      
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {
	
				//reiniciar el numerador cuando sea un dia diferente
				/*if($subdia != $reg[10]){ ANTES 12/10/2016
					$correlativo 	= 0;
					$correlativo 	= ($FechaDiv[1] * 10000) + $num_actual;
					$subdia 		= $reg[10];
				}*/

				/*if($subdia != $reg[10]){
					$correlativo 	= $correlativo + 1;
					$subdia 		= $reg[10];
				}*/

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
				
				//if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 
				if (substr($reg[1],0,3) == substr($compra_combustible_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_glp_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_market_cuenta_proveedor,0,3)) { 
					$correlativo 	= $correlativo + 1;
					$k=1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;						
				}
				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}			
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				if($reg[13] == '10')
					$tipodoc = 'FT';
				elseif($reg[13] == '11')
					$tipodoc = 'ND';
				elseif($reg[13] == '20')
					$tipodoc = 'NA';
				elseif($reg[13] == '35')
					$tipodoc = 'BV';

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".$tipodoc."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ');", "concar_insert");
			}
		}

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$que = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dsecue;  ";
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				//if (substr($reg[4],0,3) == substr($vccliente,0,3)){
				if (substr($reg[4],0,3) == substr($compra_combustible_cuenta_proveedor,0,3) || substr($reg[4],0,3) == substr($compra_glp_cuenta_proveedor,0,3) || substr($reg[4],0,3) == substr($compra_market_cuenta_proveedor,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}

		// actualizar tabla tmp_concar sumando las diferencias al igv13818
		$k = 0;
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				//if (trim($reg[4] == $vcimpuesto)){
				if (trim($reg[4] == $compra_cuenta_impuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$compra_cuenta_impuesto' and trim(dcodane)='' AND dsubdia = '".trim($reg[0])."';", "queryaux1"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar ORDER BY dsubdia,dcompro,dsecue; ";					
		$arrInserts = Array();
		$pd = 0; 

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				$ins = "INSERT INTO $TabSqlDet (dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc,dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2 ) VALUES ( '".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."', '".$reg[13]."', '', '".$reg[15]."', '".$reg[17]."', 0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE());";
				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;						
			}
		}

		//+++++++++++++++++++++ Cabecera de Compras +++++++++++++++++++++
		
		$correlativo 	= 0;		
		$pc 			= 0;
		$cons 			= 0;
			
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {

				//reiniciar el numerador cuando sea un dia diferente
				/*if($subdia != $reg[10]){ ANTES 12/10/2016
					$correlativo 	= 0;
					$correlativo 	= ($FechaDiv[1] * 10000) + $num_actual;
					$subdia 		= $reg[10];
				}*/

				/*if($subdia != $reg[10]){
					$correlativo 	= $correlativo + 1;
					$subdia 		= $reg[10];
				}*/

				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}

				//if (substr($reg[1],0,3) == substr($vccliente,0,3)) {
				if (substr($reg[1],0,3) == substr($compra_combustible_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_glp_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_market_cuenta_proveedor,0,3)) {

					$correlativo = $correlativo + 1;

					if($FechaDiv[1]>=01 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}

					$ins = "INSERT INTO $TabSqlCab
						(	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
	       						'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$reg[7]."', '".$reg[6]."', 'V', 'S', '', '','','',''
	       					);";
					$arrInserts['tipo']['cabecera'][$pc] = $ins;
					$pc++;	
				}													
			}
		}			

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
				
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet);

		return $rstado;		
	}

}

?>

