<?php
function os_mssql_escape($str) {
	return str_replace("'","''",$str);
}

function mssql_scope_identity($mssql) {
	$res = mssql_query("SELECT SCOPE_IDENTITY();",$mssql);
	$row = mssql_fetch_row($res);
	$ret = $row[0];
	mssql_free_result($res);
	return $ret;
}

function onError_AIExit($mssql,$msg) {
	global $sqlca;
	mssql_query("ROLLBACK TRANSACTION;",$mssql);
	$sqlca->query("ROLLBACK;");
	return $msg;
}

function clientUpsert($mssql,$ruc,$razsocial) {
	global $sqlca;
	$res = mssql_query("SELECT 1 FROM Clientes WHERE RUC = '$ruc';",$mssql);
	if (mssql_num_rows($res)==0)
		return mssql_query("INSERT INTO Clientes (idCliente,Razon,RUC,Notificar,PorcArt,Precio) VALUES ('$ruc','$razsocial','$ruc',0,0,0);",$mssql);
	return TRUE;
}

function addslashes_mssql($str){
	if (is_array($str)) {
		foreach($str AS $id => $value) {
			$str[$id] = addslashes_mssql($value);
		}
	} else {
		$str = str_replace("'", "''", $str);
	}

	return $str;
}

class InterfaceExactusModel extends Model {
	function ListadoAlmacenes($codigo) {
		global $sqlca;

		$cond = '';
		if ($codigo != "")
			$cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
		$query = "SELECT ch_almacen ".
		"FROM inv_ta_almacenes ".
		"WHERE trim(ch_clase_almacen)='1' ".
		" ".$cond." ".
		"ORDER BY ch_almacen";

		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();

		$numrows = $sqlca->numrows();
		$x = 0;
		while( $reg = $sqlca->fetchRow()) {
			if($numrows>1) {
				if($x < $numrows-1) {
					$conc = ".";
				} else {
					$conc = "";
				}
			}
			$listado[''.$codigo.''] .= $reg[0].$conc;
			$x++;
		}
		return $listado;
	}

	function obtenerParametros() {
		global $sqlca;

		$defaultparams = Array("216.244.153.93:1433","opencomb","cix","INMOBILIARIA2");

		$sql = "	SELECT
					p1.par_valor,
					p2.par_valor,
					p3.par_valor,
					p4.par_valor
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = 'exactus_username'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = 'exactus_password'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = 'exactus_dbname'
				WHERE
					p1.par_nombre = 'exactus_server'";
		if ($sqlca->query($sql)<0)
			return $defaultparams;

		if ($sqlca->numrows()!=1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();
		return Array($reg[0],$reg[1],$reg[2],$reg[3]);
	}

	function actualizarParametros($server,$user,$pass,$dbname) {
		global $sqlca;

		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$server}' WHERE par_nombre = 'exactus_server'")<0)
			return FALSE;
		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$user}' WHERE par_nombre = 'exactus_username'")<0)
			return FALSE;
		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$pass}' WHERE par_nombre = 'exactus_password'")<0)
			return FALSE;
		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$dbname}' WHERE par_nombre = 'exactus_dbname'")<0)
			return FALSE;

		return TRUE;
	}

	function ActualizarInterfaces($Parametros,$FechaIni,$FechaFin,$CodAlmacen) {
		include("/sistemaweb/include/mssqlemu.php");

		echo "<pre>";
		print_r( array($Parametros,$FechaIni,$FechaFin,$CodAlmacen) );
		echo "</pre>";

		global $sqlca;

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		if (("pos_trans".$FechaDiv[2].$FechaDiv[1])!=$postrans) {
			return "INVALID_DATE";
		}

		if (strlen($FechaIni)<10 || strlen($FechaFin)<10) {
			return "INDALID_DATE";
		}

		/*
		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
		if ($mssql===FALSE) {
			return "CONNECT_EXACTUS";
		}
		mssql_select_db($MSSQLDBName,$mssql);

		$Almacenes = Array();
		$res=mssql_query("SELECT OSID,WarehouseID FROM Opensoft.Opensoft_Warehouse WHERE OSID IS NOT NULL AND OSID != ''",$mssql);
		if ($res===FALSE)
			return "ERROR_PREPARE1";
		while ($row = mssql_fetch_row($res))
			$Almacenes[$row[0]] = $row[1];
		$CodExactusAlmacen = $Almacenes[$CodAlmacen];

		$CodExactusCliVarios = 1;	// El Cliente 1 es Clientes Varios

		if ($CodExactusAlmacen == NULL || $CodExactusCliVarios === NULL)
			return "ERROR_PREPARE2";

		mssql_query("BEGIN TRANSACTION;",$mssql);
		$sqlca->query("BEGIN;");
		$sqlca->query("LOCK TABLE \"exactus_migraciones\" IN ACCESS EXCLUSIVE MODE");

		if ($sqlca->query("SELECT 1 FROM \"exactus_migraciones\" WHERE ('$FechaIni' BETWEEN fecha_inicio AND fecha_fin OR '$FechaFin' BETWEEN fecha_inicio AND fecha_fin) AND ch_almacen = '$CodAlmacen'")<0)
			return onError_AIExit($mssql,"PG_SELECT_PRE01".$sqlca->get_error());

		if ($sqlca->numrows()!=0)
			return onError_AIExit($mssql,"PROCESS_EXECUTED");

		$WorkingInstance = "%%*OPENSOFT_WORKING_ES$CodAlmacen*%%";
		*/

/*******************************************************************************
* Clientes por RUC (pos_trans / ruc)                                           *
*******************************************************************************/
echo "========== SINCRONIZANDO CLIENTES ==========\n";
		// $Clientes = Array();
		// $res=mssql_query("SELECT RUC,ClientID FROM Opensoft.Opensoft_Client",$mssql);
		// if ($res===FALSE) {
		// 	return "ERROR_PREPARE_CLIENTSYNC";
		// }
		// while ($row = mssql_fetch_row($res))
		// 	$Clientes[trim($row[0])] = trim($row[1]);
		// mssql_free_result($res);

		$sql ="	SELECT
				q.RUC,
				max(r.razsocial) AS RazSocial
			FROM
				((
					SELECT
						trim(t.ruc) AS RUC
					FROM
						$postrans t
					WHERE
						td = 'F'
						AND t.ruc != ''
				) UNION (
					SELECT
						trim(c.cli_ruc) AS RUC
					FROM
						fac_ta_factura_cabecera fc
						JOIN int_clientes c ON fc.cli_codigo = c.cli_codigo
					WHERE
						fc.cli_codigo != '9999'
						AND fc.ch_fac_tipodocumento IN ('10','11','20','35')
						AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND fc.ch_almacen = '$CodAlmacen'
				)) q
				LEFT JOIN ruc r ON (q.RUC = r.ruc)
			GROUP BY
				1";
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			// die();
/*		$sql ="	SELECT
				DISTINCT trim(t.ruc),
				r.razsocial
			FROM
				$postrans t
				LEFT JOIN ruc r ON t.ruc = r.ruc
			WHERE
				t.ruc != '';";*/

		// if ($sqlca->query($sql)<0)
		// 	return OnError_AIExit($mssql,"PG_CLIENT_SYNC_Q01".$sqlca->get_error());

		// while ($reg = $sqlca->fetchRow())
		// 	if (!isset($Clientes[$reg[0]])) {
		// 		if (mssql_query("INSERT INTO Opensoft.Opensoft_Client (RUC,Name) VALUES ('{$reg[0]}','" . os_mssql_escape($reg[1]) . "');",$mssql)==FALSE)
		// 			return OnError_AIExit($mssql,"ERROR_CLIENTSYNC_INSERT");
		// 		$Clientes[$reg[0]] = mssql_scope_identity($mssql);
		// 	}

		// $Clientes['1'] = 1;
		// $Clientes['9999'] = 1;

/*******************************************************************************
* ARTICULOS (int_articulos)                                                    *
*******************************************************************************/
echo "========== SINCRONIZANDO ARTICULOS ==========\n";

		// $Articulos = Array();
		// $res=mssql_query("SELECT ProductID,ProductCode FROM Opensoft.Opensoft_Product",$mssql);
		// if ($res===FALSE) {
		// 	return "ERROR_PREPARE_ARTSYNC";
		// }
		// while ($row = mssql_fetch_row($res))
		// 	$Articulos[trim($row[1])] = trim($row[0]);
		// mssql_free_result($res);

		$sql ="	SELECT
				q.Codigo AS ProductCode,
				max(a.art_descbreve) AS Description
			FROM
				((
					SELECT
						trim(m.art_codigo) AS Codigo
					FROM
						inv_movialma m
					WHERE
						m.mov_fecha BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
						AND '$CodAlmacen' IN (m.mov_almaorigen,m.mov_almadestino)
					GROUP BY
						1
				) UNION (
					SELECT
						trim(fd.art_codigo) AS Codigo
					FROM
						fac_ta_factura_detalle fd
						JOIN fac_ta_factura_cabecera fc ON (fd.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento)
					WHERE
						fc.ch_fac_tipodocumento IN ('10','11','20','35')
						AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND fc.ch_almacen = '$CodAlmacen'
					GROUP BY
						1
				) UNION (
					SELECT
						t.codigo AS Codigo
					FROM
						$postrans t
					WHERE
						t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
						AND t.es = '$CodAlmacen'
				)) q
				LEFT JOIN int_articulos a ON (q.Codigo = a.art_codigo)
			GROUP BY
				1;";
		echo "<pre>";
		echo $sql;
		echo "</pre>";
		// die();

		// if ($sqlca->query($sql)<0)
		// 	return OnError_AIExit($mssql,"PG_ART_SYNC_Q01".$sqlca->get_error());

// echo "========== SINCRONIZANDO ARTICULOS  ==========\n";
		// while ($reg = $sqlca->fetchRow())
		// 	if (!isset($Articulos[$reg[0]])) {
		// 		if (mssql_query("INSERT INTO Opensoft.Opensoft_Product (ProductCode,Description) VALUES ('{$reg[0]}','" . addslashes_mssql($reg[1]) . "');",$mssql)==FALSE)
		// 			return OnError_AIExit($mssql,"ERROR_ARTSYNC_INSERT_1");
		// 		$Articulos[$reg[0]] = mssql_scope_identity($mssql);
		// 	}

/*******************************************************************************
* CABECERAS DE VENTAS (pos_trans GB)                                           *
*******************************************************************************/
echo "========== SINCRONIZANDO CABECERAS DE VENTAS ==========\n";

		$sql ="	SELECT
				'12'::text AS DocumentType,
				lpad(max(t.caja),3,'000'::text) || '-' || to_char(t.trans,'FM9999999999') AS DocumentNumber,
				{$Almacenes[$CodAlmacen]} AS WarehouseID,
				0 AS MovementType,
				max(t.dia) AS MovementDate,
				CASE
					WHEN max(t.td) = 'F' THEN max(t.ruc)
					ELSE '{$CodExactusCliVarios}'::text
				END AS Client,
				CASE
					WHEN max(t.fpago) = '1' THEN 0
					ELSE 1
				END AS TenderType,
				CASE
					WHEN max(t.fpago) = '1' THEN NULL
					ELSE max(t.at)
				END AS CardType,
				sum(t.importe)-sum(t.igv) AS Total,
				sum(t.igv) AS Taz,
				sum(t.importe) AS GrandTotal
			FROM
				{$postrans} t
			WHERE
				td NOT IN ('A','N')
				AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				AND t.es = '{$CodAlmacen}'
			GROUP BY
				t.trans,
				t.caja,
				t.dia
			ORDER BY
				t.trans,
				t.caja,
				t.dia";
		echo "<pre>";
		echo $sql;
		echo "</pre>";
		// die();

		// if ($sqlca->query($sql)<0)
		// 	return onError_AIExit($mssql,"PG_SELECT_Q01".$sqlca->get_error());
// echo "========== SINCRONIZANDO CABECERAS DE TICKETS ==========\n";
// 		$DocHead_ID = Array();
// 		while ($reg = $sqlca->fetchRow()) {
// 			$sql ="	INSERT INTO
// 					Opensoft.Opensoft_Document
// 				(
// 					DocumentType,
// 					DocumentNumber,
// 					WarehouseID,
// 					MovementType,
// 					MovementDate,
// 					ClientID,
// 					TenderType,
// 					CardType,
// 					Total,
// 					Tax,
// 					GrandTotal
// 				) VALUES (
// 					'{$reg[0]}',
// 					'{$reg[1]}',
// 					{$reg[2]},
// 					{$reg[3]},
// 					'{$reg[4]}',
// 					{$Clientes[$reg[5]]},
// 					{$reg[6]},
// 					" . ((strlen($reg[7])>0)?$reg[7]:"NULL") . ",
// 					{$reg[8]},
// 					{$reg[9]},
// 					{$reg[10]}
// 				);";
// 			if (mssql_query($sql,$mssql)===FALSE)
// 				return onError_AIExit($mssql,"ERROR_INSERT_Q01");
// 			$DocHead_ID[$reg[1]] = mssql_scope_identity($mssql);
// 		}

/*******************************************************************************
* DETALLE DE VENTAS (pos_trans)                                                *
*******************************************************************************/
echo "========== SINCRONIZANDO DETALLE DE VENTAS ==========\n";

		$sql ="	SELECT
				lpad(t.caja,3,'000'::text) || '-' || to_char(t.trans,'FM9999999999') AS DocumentNumber,
				trim(t.codigo) AS Product,
				t.precio AS UnitPrice,
				t.cantidad AS Quantity,
				t.importe AS LineTotal
			FROM
				$postrans t
			WHERE
				td NOT IN ('A','N')
				AND t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
				AND t.es = '{$CodAlmacen}'
			ORDER BY
				t.trans;";
		echo "<pre>";
		echo $sql;
		echo "</pre>";
		// die();

// 		if ($sqlca->query($sql)<0)
// 			return onError_AIExit($mssql,"PG_SELECT_Q02".$sqlca->get_error());
// //echo "========== SINCRONIZANDO DETALLE DE TICKETS MANUALES ==========\n";
// 		while ($reg = $sqlca->fetchRow()) {
// 			$sql ="	INSERT INTO
// 					Opensoft.Opensoft_DocumentLine
// 				(
// 					DocumentID,
// 					ProductID,
// 					UnitPrice,
// 					Quantity,
// 					LineTotal
// 				) VALUES (
// 					{$DocHead_ID[$reg[0]]},
// 					{$Articulos[$reg[1]]},
// 					{$reg[2]},
// 					{$reg[3]},
// 					{$reg[4]}
// 				);";
// 			if (mssql_query($sql,$mssql)===FALSE)
// 				return onError_AIExit($mssql,"ERROR_INSERT_Q02_$sql");
// 		}
/*******************************************************************************
* CABECERAS DE DOCUMENTOS MANUALES (fac_ta_factura_cabecera)                   *
*******************************************************************************/
echo "========== CABECERAS DE DOCUMENTOS MANUALES (fac_ta_factura_cabecera) ==========\n";

		$sql ="	SELECT
				CASE
					WHEN fc.ch_fac_tipodocumento = '10' THEN '01'::text
					WHEN fc.ch_fac_tipodocumento = '35' THEN '03'::text
					WHEN fc.ch_fac_tipodocumento = '11' THEN '04'::text
					WHEN fc.ch_fac_tipodocumento = '20' THEN '07'::text
					ELSE '00'::text
				END AS DocumentType,
				fc.ch_fac_seriedocumento || '-' || fc.ch_fac_numerodocumento AS DocumentNumber,
				{$Almacenes[$CodAlmacen]} AS WarehouseID,
				0 AS MovementType,
				fc.dt_fac_fecha AS MovementDate,
				CASE
					WHEN fc.cli_codigo = '9999' THEN '{$CodExactusCliVarios}'::text
					ELSE trim(c.cli_ruc)
				END AS Client,
				0 AS TenderType,
				NULL AS CardType,
				fc.nu_fac_valorbruto AS Total,
				fc.nu_fac_impuesto1 AS IGV,
				fc.nu_fac_valortotal AS GrandTotal
			FROM
				fac_ta_factura_cabecera fc
				JOIN int_clientes c ON fc.cli_codigo = c.cli_codigo
			WHERE
				fc.ch_fac_tipodocumento IN ('10','11','20','35')
				AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND fc.ch_almacen = '$CodAlmacen'";
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			// die();

// 		if ($sqlca->query($sql)<0)
// 			return onError_AIExit($mssql,"PG_SELECT_Q03".$sqlca->get_error());
// echo "========== SINCRONIZANDO CABECERAS DE DOCUMENTOS MANUALES ==========\n";
// 		$DocHead_ID = Array();
// 		while ($reg = $sqlca->fetchRow()) {
// 			$sql ="	INSERT INTO
// 					Opensoft.Opensoft_Document
// 				(
// 					DocumentType,
// 					DocumentNumber,
// 					WarehouseID,
// 					MovementType,
// 					MovementDate,
// 					ClientID,
// 					TenderType,
// 					CardType,
// 					Total,
// 					Tax,
// 					GrandTotal
// 				) VALUES (
// 					'{$reg[0]}',
// 					'{$reg[1]}',
// 					{$reg[2]},
// 					{$reg[3]},
// 					'{$reg[4]}',
// 					" . $Clientes[$reg[5]] . ",
// 					{$reg[6]},
// 					NULL,
// 					{$reg[8]},
// 					{$reg[9]},
// 					{$reg[10]}
// 				);";
// 			if (mssql_query($sql,$mssql)===FALSE)
// 				return onError_AIExit($mssql,"ERROR_INSERT_Q03");
// 			$DocHead_ID[$reg[1]] = mssql_scope_identity($mssql);
// 		}

/*******************************************************************************
* DETALLE DE DOCUMENTOS MANUALES (fac_ta_factura_detalle)                      *
*******************************************************************************/
echo "========== DETALLE DE DOCUMENTOS MANUALES (fac_ta_factura_detalle) ==========\n";

		$sql ="	SELECT
				fc.ch_fac_seriedocumento || '-' || fc.ch_fac_numerodocumento AS DocNro,
				trim(fd.art_codigo) AS Product,
				fd.nu_fac_precio AS UnitPrice,
				fd.nu_fac_cantidad AS Quantity,
				fd.nu_fac_importeneto AS GrandTotal
			FROM
				fac_ta_factura_detalle fd
				JOIN fac_ta_factura_cabecera fc ON (fd.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento)
			WHERE
				fc.ch_fac_tipodocumento IN ('10','11','20','35')
				AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND fc.ch_almacen = '$CodAlmacen';";
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			// die();

// 		if ($sqlca->query($sql)<0)
// 			return onError_AIExit($mssql,"PG_SELECT_Q04".$sqlca->get_error());
// echo "========== SINCRONIZANDO DETALLE DE DOCUMENTOS MANUALES ==========\n";
// 		while ($reg = $sqlca->fetchRow()) {
// 			$sql ="	INSERT INTO
// 					Opensoft.Opensoft_DocumentLine
// 				(
// 					DocumentID,
// 					ProductID,
// 					UnitPrice,
// 					Quantity,
// 					LineTotal
// 				) VALUES (
// 					{$DocHead_ID[$reg[0]]},
// 					{$Articulos[$reg[1]]},
// 					{$reg[2]},
// 					{$reg[3]},
// 					{$reg[4]}
// 				);";
// 			if (mssql_query($sql,$mssql)===FALSE)
// 				return onError_AIExit($mssql,"ERROR_INSERT_Q04");
// 		}

/*******************************************************************************
* KARDEX (inv_movialma)                                                        *
*******************************************************************************/
echo "========== KARDEX (inv_movialma) ==========\n";

		$sql ="	SELECT
				{$Almacenes[$CodAlmacen]} AS WarehouseID,
				m.mov_fecha AS MovementDate,
				m.mov_docurefe AS ReferenceDocument,
				m.mov_tipdocuref AS ReferenceDocumentType,
				trim(m.art_codigo) AS Product,
				m.mov_costounitario AS UnitPrice,
				CASE
					WHEN tt.tran_naturaleza IN ('1','2') THEN m.mov_cantidad
					ELSE m.mov_cantidad
				END AS Quantity,
				CASE
					WHEN tt.tran_naturaleza IN ('1','2') THEN m.mov_costototal
					ELSE (m.mov_costototal*-1)
				END AS MovementTotal
			FROM
				inv_movialma m
				JOIN inv_tipotransa tt ON m.tran_codigo = tt.tran_codigo
			WHERE
				m.mov_fecha BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
				AND m.mov_almacen = '$CodAlmacen';";
		echo "<pre>";
		echo $sql;
		echo "</pre>";
		die();

// 		if ($sqlca->query($sql)<0)
// 			return onError_AIExit($mssql,"PG_SELECT_Q05".$sqlca->get_error());
// echo "========== SINCRONIZANDO KARDEX ==========\n";
// 		while ($reg = $sqlca->fetchRow()) {
// 			$sql ="	INSERT INTO
// 					Opensoft.Opensoft_Movement
// 				(
// 					WarehouseID,
// 					MovementDate,
// 					ReferenceDocument,
// 					ReferenceDocumentType,
// 					ProductID,
// 					UnitPrice,
// 					Quantity,
// 					MovementTotal
// 				) VALUES (
// 					{$reg[0]},
// 					'{$reg[1]}',
// 					'{$reg[2]}',
// 					'{$reg[3]}',
// 					{$Articulos[$reg[4]]},
// 					{$reg[5]},
// 					{$reg[6]},
// 					{$reg[7]}
// 				);";
// 			if (mssql_query($sql,$mssql)===FALSE)
// 				return onError_AIExit($mssql,"ERROR_INSERT_Q05_$sql");
// 		}

		$sqlca->query("INSERT INTO exactus_migraciones (ch_almacen,fecha_inicio,fecha_fin,ch_usuario) VALUES ('$CodAlmacen','$FechaIni','$FechaFin','{$_SESSION['auth_usuario']}');");
		$sqlca->query("COMMIT;");
//		$sqlca->query("ROLLBACK;");

//		mssql_query("ROLLBACK TRANSACTION;",$mssql);
		mssql_query("COMMIT TRANSACTION;",$mssql);

		mssql_close($mssql);

		return TRUE;
	}

	function ConsultaProcesos ($desde,$hasta,$sucursal){
    		global $sqlca;
		if($sucursal=="all") {
				$query = "SELECT exactus_migracion_id as id,ch_almacen as almacen,to_char(fecha_inicio, 'DD/MM/YYYY') as inicio,to_char(fecha_fin, 'DD/MM/YYYY') as fin,to_char(fecha_actual, 'DD/MM/YYYY HH:MI:SS') as actual, ch_usuario as usuario  
				  FROM exactus_migraciones
				  WHERE fecha_inicio between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
				  ORDER BY fecha_inicio DESC;";
		} else {
			$query = "SELECT exactus_migracion_id as id,ch_almacen as almacen,to_char(fecha_inicio, 'DD/MM/YYYY') as inicio,to_char(fecha_fin, 'DD/MM/YYYY') as fin,to_char(fecha_actual, 'DD/MM/YYYY HH:MI:SS') as actual, ch_usuario as usuario  
				  FROM exactus_migraciones
				  WHERE ch_almacen = '".pg_escape_string($sucursal)."' and fecha_inicio between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
				  ORDER BY fecha_inicio DESC;";
		}
		echo "<pre>";
		echo $query;
		echo "</pre>";

         	if ($sqlca->query($query) < 0) return null;
		$resultado = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$fila = $sqlca->fetchRow();
			$resultado[$i] = $fila;
		}
    		return $resultado;
	}

	function Eliminar ($id){
    		global $sqlca;
		$query = "DELETE FROM exactus_migraciones WHERE exactus_migracion_id= ".$id.";";
    		return $sqlca->query($query);
	}
}
