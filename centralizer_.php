<?php

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");
$r = ob_start(true);

/**
 * pg_escape_string
 * para entrada de datos GET/POST
 */

function SQLImplode($sql) {
		global $sqlca;

		if ($sqlca->query($sql) < 0)
				return FALSE;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$rR = $sqlca->fetchRow();
				foreach ($rR as $k => $v) {
						if (is_numeric($k))
								echo (($k == 0) ? "" : "|") . $v;
				}
				echo "\n";
		}
}

function SQLImplodeSerialize($sql, $debug = false) {
		global $sqlca;
		if ($sqlca->query($sql) < 0) {
			return FALSE;
		}
		$result = array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$result[] = $sqlca->fetchRow();
		}
		if($debug == true){
			error_log( json_encode($result) );
			error_log( serialize($result) );
		}
		echo serialize($result);
}

function SQLImplodeJSON($sql) {
		global $sqlca;
		if ($sqlca->query($sql) < 0) {
			return FALSE;
		}
		$result = array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$result[] = $sqlca->fetchRow();
		}
		return json_encode($result);
}

function SQLImplodeArray($sql) {
	global $sqlca;
	if ($sqlca->query($sql) < 0) {
		return FALSE;
	}
	$result = array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$result[] = $sqlca->fetchRow();
	}
	return $result;
}

function SQLImplodeMultiple($sqls) {
		global $sqlca;
		$result = '';

		foreach ($sqls as $key => $sql) {    
			if ($sqlca->query($sql) < 0)
					return FALSE;

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
					$rR = $sqlca->fetchRow();
					foreach ($rR as $k => $v) {
							if (is_numeric($k))
									$result += (($k == 0) ? "" : "|") . $v;
					}
					$result += "\n\n";
			}
		}
		echo $result;
}

function SQLImplode2($sql) {
		global $sqlca;

		if ($sqlca->query($sql) < 0)
				return FALSE;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$rR = $sqlca->fetchRow();
				foreach ($rR as $k => $v) {
						if (is_numeric($k))
								echo (($k == 0) ? "" : "¿") . $v;
				}
				echo "\n";
		}
}

function argRangedCheck() {
		global $CxBegin, $CxEnd, $PosTransTable, $BeginDate, $EndDate;
		//el mismo mes y periodo para las consultas
		global $BeginYear, $BeginMonth, $BeginDay, $EndYear, $EndMonth, $EndDay, $_BeginYear, $_BeginMonth, $_BeginDay, $_EndYear, $_EndMonth, $_EndDay;
		global $_BeginDate, $_EndDate;
		if (!isset($_REQUEST['from']) || !isset($_REQUEST['to']))
				die("ERR_INVALID_ARGS_RANGED");

		$CxBegin = $_REQUEST['from'];
		$CxEnd = $_REQUEST['to'];

		if (strlen($CxBegin) != 8 || strlen($CxEnd) != 8 || !is_numeric($CxBegin) || !is_numeric($CxEnd))
				die("ERR_INVALID_DATE");

		if (!isset($_REQUEST['isvaliddiffmonths'])) {
			if(substr($CxBegin, 0, 6) != substr($CxEnd, 0, 6))
					die("ERR_DATE_DIFFERENT_MONTHS");
		}

		$PosTransTable = "pos_trans" . substr($CxBegin, 0, 6);
		//año - mes - dia
		$BeginDate = substr($CxBegin, 0, 4) . "-" . substr($CxBegin, 4, 2) . "-" . substr($CxBegin, 6, 2);
		$EndDate = substr($CxEnd, 0, 4) . "-" . substr($CxEnd, 4, 2) . "-" . substr($CxEnd, 6, 2);

		$BeginYear = substr($CxBegin, 0, 4);
		$BeginMonth = substr($CxBegin, 4, 2);
		$BeginDay = substr($CxBegin, 6, 2);

		$EndYear = substr($CxEnd, 0, 4);
		$EndMonth = substr($CxEnd, 4, 2);
		$EndDay = substr($CxEnd, 6, 2);

		//actualizacion para estadistica con 4 rangos de fecha 2017-05-24
		//rango anterior
		$_BeginDate = '';
		$_EndDate = '';
		$_PosTransTable = '';
		if (isset($_REQUEST['mod'])) {
			if ($_REQUEST['mod'] == 'TOTALS_STATISTICS_SALE') {
				$_CxBegin = $_REQUEST['_from'];
				$_CxEnd = $_REQUEST['_to'];

				$_BeginDate = substr($_CxBegin, 0, 4) . "-" . substr($_CxBegin, 4, 2) . "-" . substr($_CxBegin, 6, 2);
				$_EndDate = substr($_CxEnd, 0, 4) . "-" . substr($_CxEnd, 4, 2) . "-" . substr($_CxEnd, 6, 2);
				$_PosTransTable = "pos_trans" . substr($_CxBegin, 0, 6);


				$_BeginYear = substr($_CxBegin, 0, 4);
				$_BeginMonth = substr($_CxBegin, 4, 2);
				$_BeginDay = substr($_CxBegin, 6, 2);
				$_EndYear = substr($_CxEnd, 0, 4);
				$_EndMonth = substr($_CxEnd, 4, 2);
				$_EndDay = substr($_CxEnd, 6, 2);
			}
		}
}

function argKeyCheck() {
		global $CxSearchKey;

		if (!isset($_REQUEST['sk']))
				die("ERR_INVALID_ARGS_KEY");

		$CxSearchKey = $_REQUEST['sk'];
		if ($CxSearchKey == "" || strlen($CxSearchKey) < 5)
				die("ERR_INVALID_KEY");
}

//**************************************** Reporte Caja y Banco ****************************************
//echo "<script>console.log('POST: " . json_encode($_POST) . "')</script>";							

$fecha_explode = explode("/", $_POST['dia_al']);

$iAlmacen    = $_POST['almacen'];
$dYear       = $fecha_explode['2'];
$dMonth      = $fecha_explode['1'];
$dDay        = $fecha_explode['0'];
$pos_transYM = "pos_trans" . $dYear . $dMonth;

function listadoCajaBanco($resultados, $iAlmacen, $dYear, $dMonth, $dDay = "31") {			
	//Get Class
	$objModelCajaBanco = new CajaBancoModel();

	//Si el mes es Enero, debemos de mostrar Diciembre, ya que el sistema debe de verificar si existe saldo en el mes anterior
	if ($dMonth == '01') {
		$dYear = $dYear - 1;
		$dMonth = '12';
	} else
		$dMonth = $dMonth - 1;

	// Mostrar saldo inicial por mes
	$arrData = array(
		'Nu_Warehouse' => $iAlmacen,
		'Fe_Validate_Previous_Year' => $dYear,
		'Fe_Validate_Previous_Month' => $dMonth,
	);
	$arrResponse = $objModelCajaBanco->getBalance($arrData);

	$saldo_acu += (float)$arrResponse['fSaldoInicial'];
	// ./ Saldo Inicial por Mes

	for ($i = 0; $i < count($resultados); $i++) {

		$a 	= $resultados[$i];

		if(empty($a['af_comb']))
			$data_af_comb = $a['manual_af_comb'];
		else
			$data_af_comb = $a['af_comb'];

		if(empty($a['af_glp']))
			$data_af_glp = $a['manual_af_glp'];
		else
			$data_af_glp = $a['af_glp'];

		//Obtenemos dia de data "fecha" del array $resultados
		$fecha_explode = explode('/', $a['fecha']);
		$dia_explode = $fecha_explode['0'];

		//Solo sumara si el dia de data "fecha" del array $resultados, es menor o igual a la "Fecha Final" indicada en el reporte al presionar "Consultar"
		//if($dia_explode <= $dDay){
			$saldo_acu += $a['total_venta_comb'] - $data_af_comb + $a['total_venta_gnv'] + $a['total_venta_glp'] - $data_af_glp + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] - $a['clientescredito'] - $a['creditognv'] - $a['tarjetascredito'] - $a['egresos'] + ($a['faltante'] + $a['sobrante'] + $a['sobragnv'] - $a['faltagnv']) - $a['bcp'] - $a['bbva'] - $a['scotiabank'] - $a['interbank'] + $a['descuentos']  - $a['otherimp'];
			$saldo_market += ($a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones']);
		//}		
	}

	return $saldo_acu;
}

//**************************************** Cerrar Reporte Caja y Banco ****************************************

//**************************************** Inventario de combustible ****************************************
function obtieneParte($desde, $hasta, $estaciones) {
	global $sqlca;

	$propiedad = obtenerPropiedadAlmacenes();
	$almacenes = obtieneListaEstaciones();

	$sqlA = "SELECT 
			Co.ch_codigocombustible as Codigo,
			Co.ch_nombrebreve as Producto,
			MD1.nu_medicion as Stock_Inicial,
			MA.compras, 
			C.ventas,
			MD1.nu_medicion - C.ventas as Stock_Final,
			MD2.nu_medicion as Medicion,
			MD2.nu_medicion - (MD1.nu_medicion - C.ventas) as Dia, ";
	if($desde == $hasta)		
	$sqlA .= "	
			(CASE WHEN SAL.cantidad > 0 THEN  M.mes + SAL.cantidad ELSE M.mes END) AS Mes, ";
	else
	$sqlA .= "	
			M.mes AS Mes, ";
	$sqlA .= "
			(C.valval-C.afeafe) as importe,
			ENT.cantidad,
			SAL.cantidad
		FROM 
			(select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechamedicion = to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')-1) MD1

			inner join (select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechamedicion = to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) MD2
			on MD1.ch_tanque = MD2.ch_tanque

			inner join (select ch_tanque,ch_codigocombustible from comb_ta_tanques where ch_sucursal= '" . pg_escape_string($estaciones) . "') T
			on T.ch_tanque = MD1.ch_tanque and T.ch_tanque = MD2.ch_tanque

			left join (select art_codigo,sum(mov_cantidad) as compras from inv_movialma where tran_codigo='21' and date(mov_fecha) between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') AND mov_almacen='$estaciones' group by art_codigo) MA
			on MA.art_codigo = T.ch_codigocombustible
			
			LEFT JOIN
				(SELECT
					art_codigo codigo,
					round(sum(mov_cantidad),2) as cantidad
				FROM
					inv_movialma alma
				WHERE
					mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND tran_codigo = '27'
				GROUP BY
					art_codigo) ENT ON ENT.codigo = T.ch_codigocombustible

			LEFT JOIN
				(SELECT
					art_codigo codigo,
					round(sum(mov_cantidad),2) as cantidad
				FROM
					inv_movialma alma
				WHERE
					mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND tran_codigo = '28'
				GROUP BY
					art_codigo) SAL ON SAL.codigo = T.ch_codigocombustible

			inner join 
				(select com1.ch_codigocombustible as codcod, sum(com1.nu_ventagalon) - (sum(com1.nu_afericionveces_x_5) * 5) as ventas, sum(com1.nu_ventavalor) valval, 
					(SELECT COALESCE(sum(afe.importe),0) FROM pos_ta_afericiones afe WHERE afe.codigo = com1.ch_codigocombustible AND afe.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') ) as afeafe 
				from comb_ta_contometros com1 
				 where com1.ch_sucursal='" . pg_escape_string($estaciones) . "' and com1.dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
				group by com1.ch_codigocombustible) C 

			on C.codcod = T.ch_codigocombustible
			
			

			INNER JOIN comb_ta_combustibles Co ON Co.ch_codigocombustible = C.codcod

			INNER JOIN(SELECT
					T.ch_codigocombustible as combustible,
					sum(MD2.nu_medicion-(MD1.nu_medicion+CASE WHEN MA.compras>0 THEN MA.compras ELSE 0.00 END-C.ventas)) - (CASE WHEN first(ENT.cantidad) > 0 THEN first(ENT.cantidad) ELSE 0 END - CASE WHEN first(SAL.cantidad) > 0 THEN first(SAL.cantidad) ELSE 0 END) as Mes
				FROM  
					comb_ta_mediciondiaria MD1

				inner join (select ch_sucursal,ch_tanque,ch_codigocombustible from comb_ta_tanques) T
				on T.ch_tanque = MD1.ch_tanque and T.ch_sucursal=MD1.ch_sucursal

				left join (	select date(mov_fecha) as fecha,art_codigo,sum(mov_cantidad) as compras 
						from inv_movialma 
						where tran_codigo='21'  AND mov_almacen='$estaciones'
						group by art_codigo,date(mov_fecha)) MA
				on MA.art_codigo = T.ch_codigocombustible and MA.fecha=MD1.dt_fechamedicion+1

				inner join (select ch_sucursal,dt_fechaparte,ch_codigocombustible,sum(nu_afericionveces_x_5) as afericion,sum(nu_ventagalon)as venta,sum(nu_ventagalon-(nu_afericionveces_x_5*5)) as ventas from comb_ta_contometros group by ch_sucursal,dt_fechaparte,ch_codigocombustible) C
				on C.ch_sucursal = MD1.ch_sucursal and C.dt_fechaparte = MD1.dt_fechamedicion+1 and C.ch_codigocombustible=T.ch_codigocombustible

				inner join comb_ta_combustibles Co
				on Co.ch_codigocombustible = T.ch_codigocombustible

				inner join (select dt_fechamedicion,ch_sucursal,ch_tanque,nu_medicion from comb_ta_mediciondiaria) MD2
				on MD2.dt_fechamedicion=MD1.dt_fechamedicion+1 and MD2.ch_sucursal=MD1.ch_sucursal and MD2.ch_tanque = T.ch_tanque

				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						mov_fecha::date BETWEEN to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1
						AND tran_codigo = '27'
					GROUP BY
						art_codigo) ENT ON ENT.codigo = T.ch_codigocombustible

				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						mov_fecha::date BETWEEN to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1
						AND tran_codigo = '28'
					GROUP BY
						art_codigo) SAL ON SAL.codigo = T.ch_codigocombustible
			 
		WHERE  ";
	
	if($desde == $hasta)		
	$sqlA .= "	
			MD1.dt_fechamedicion between to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1 ";
	else
	$sqlA .= "	
			MD1.dt_fechamedicion between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY')-2 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1 ";
	$sqlA .= "

			AND MD1.ch_sucursal='" . pg_escape_string($estaciones) . "'
		GROUP BY 
			T.ch_codigocombustible) M on M.combustible = T.ch_codigocombustible";

	$sqlA .= " ORDER BY Co.ch_nombrebreve ;";
	$debug = false;
	if($debug){
		echo "<pre>";
		echo "<b>Query Inventario de Combustible</b><br>";
		echo $sqlA;
		echo "</pre>";
	}

//		echo '- PARTE: '.$sqlA.' -';

	if ($sqlca->query($sqlA) < 0) 
		return false;
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();		    
		$ch_sucursal = pg_escape_string($estaciones);
		/**
		 * La query ordena por nombre de combustible, pero el indice era solo $a[0], que es el codigo
		 * Al recibir esta informacion en JS, cambia el orden al del indice, no respetando el orden de la query
		 * Se realizo el cambio a $a[1] . "|" . $a[0] para que ordene por el nombre al enviar la data a JS en OCS Manager
		 */ 
		$producto = $a[1] . "|" . $a[0];
		$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
		$ch_sucursal = $almacenes[$ch_sucursal];

		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['producto'] = $a[1];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['inicial'] = $a[2];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['compras'] = $a[3];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['transfe'] = $a[10] - $a[11];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['ventas'] = $a[4];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = $a[4] * 100;
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['transfesalida'] = $a[11];

		if ($a[3]!=''){
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5] + $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7] - $a[3];
		}else{
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7] ;
		}
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['medicion'] = $a[6];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = $a[8];
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['importe'] = $a[9];

		//TOTALES
		if ($a[1] == 'GLP'){
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = "100";
		} else {
			if ($a[3]!=''){
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += ($a[5] + $a[3]);
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += (($a[7] - $a[3]) + $a[11]);

			} else {
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += $a[5];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += $a[7] + $a[11];
			}
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['producto'] = "TOTAL";
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['inicial'] += $a[2];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['compras'] += $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['ventas'] += $a[4];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['porcentaje'] = "100";
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['transfe'] = $a[10] - $a[11];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['medicion'] += $a[6] ;
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['mes'] += $a[8];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['importe'] += $a[9];
		}
	}
	return $result;	
}

function obtenerPropiedadAlmacenes() {
	global $sqlca;

	$sql = "SELECT ch_almacen, 'S' AS ch_almacen_propio
		FROM inv_ta_almacenes
		WHERE ch_clase_almacen='1'; ";

	if ($sqlca->query($sql) < 0) 
		return false;	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();		    
		$result[$a[0]] = $a[1];
	}	
	return $result;
}   

function obtieneListaEstaciones() {
	global $sqlca;

	$sql = "SELECT ch_almacen, trim(ch_nombre_almacen)
		FROM inv_ta_almacenes
		WHERE ch_clase_almacen='1'
		ORDER BY ch_almacen ; ";
	if ($sqlca->query($sql) < 0) 
		return false;	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
			 $a = $sqlca->fetchRow();
			 $result[$a[0]] = $a[0] . " - " . $a[1];
	}	
	return $result;
}
	 
function validaDia($dia, $almacen) {
	global $sqlca;

	/*$sql = "SELECT CASE WHEN ch_poscd='A' THEN ch_posturno ELSE ch_posturno-1 END FROM pos_aprosys WHERE da_fecha='$dia'";
	if ($sqlca->query($sql) < 0) 
		return false;
	$a = $sqlca->fetchRow();
	$maxturno = $a[0];
	
	if(trim($maxturno) == "")
		$maxturno = 0;*/

	$turno = 0;

	$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";
//echo $sql;
	$sqlca->query($sql);

	$estado = $sqlca->fetchRow();

//		echo "devolvio:\n";
//		var_dump($estado);

	if($estado[0] == 1){
		return 0;//No Consolidado
	}else{
		return 1;//Consolidado
	}
}
//**************************************** Cerrar Inventario de combustible ****************************************

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

if (!isset($_REQUEST['mod']))
		die("ERR_INVALID_ARGS");


$CxModule = $_REQUEST['mod'];


switch ($CxModule) {

		/**
		***************************
		** Casos para OCSMANAGER **
		***************************
		*/

		case 'ERR':
			echo 'ERR';
		break;

		case 'TOTALS_SALE_COMB':
			argRangedCheck();
			//pg_escape_string

			$warehouse_id = $_REQUEST['warehouse_id'];

			/**
			 * pos_trans
			 * fpago = 2
			 * td = 'B'
			 * td = 'F'
			 */
			$sql = "SELECT
				(
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS total_ventagalon, --0 cantidad
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) AS total_ventavalor, --1 soles
				C.codigo AS codigo, --2
				COST.costo_comb * (
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS costo, --3 costo promedio
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) / (1 + (COMB.igv/100)) AS venta_sin_igv --4 valor venta sin igv
			FROM
				(
					SELECT
						ch_codigocombustible AS codigo
					FROM
						comb_ta_tanques
					WHERE
						ch_sucursal = '$warehouse_id'
				) C
			INNER JOIN (
				SELECT
					comb.ch_codigocombustible AS codigo,
					cmb.ch_nombrecombustible AS descripcion,
					SUM (
						CASE 
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventavalor
						ELSE
							0
						END
					) AS total_venta,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventagalon
						ELSE
							0
						END
					) AS total_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							(comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_soles,
					ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
					nu_factor_igv AS igv
				FROM
					comb_ta_contometros comb
				LEFT JOIN comb_ta_combustibles cmb ON (
					comb.ch_codigocombustible = cmb.ch_codigocombustible
				)
				WHERE
					comb.dt_fechaparte BETWEEN '$BeginDate'
				AND '$EndDate'
				AND comb.ch_sucursal = TRIM ('$warehouse_id')
				GROUP BY
					comb.ch_codigocombustible,
					cmb.ch_nombrecombustible,
					comb.nu_factor_igv
			) COMB ON COMB.codigo = C .codigo
			LEFT JOIN (
				SELECT
					af.codigo AS codigo,
					SUM (af.importe) AS af_total,
					ROUND(SUM(af.cantidad), 3) AS af_cantidad
				FROM
					pos_ta_afericiones af
				WHERE
					af.dia BETWEEN '$BeginDate'
				AND '$EndDate'
				AND af.es = TRIM ('$warehouse_id')
				GROUP BY
					af.codigo
			) AFC ON AFC.codigo = C .codigo
			LEFT JOIN (
					SELECT stk_costo$BeginMonth as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '$BeginYear'
					AND stk_almacen = TRIM ('$warehouse_id')
			) COST ON (COST.art_codigo =  C.codigo)
			UNION ALL
			SELECT
				tot_cantidad AS total_ventagalon,
				CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
					tot_surtidor_soles
				ELSE
					tot_surtidor_soles - tot_afericion
				END
				AS total_ventavalor,
				'11620308' AS codigo,
				nu_costo_unitario*tot_cantidad AS costo,
				CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
					tot_surtidor_soles
				ELSE
					tot_surtidor_soles - tot_afericion
				END / (1 + (util_fn_igv()/100)) AS venta_sin_igv
			FROM comb_liquidaciongnv
			WHERE ch_almacen = '$warehouse_id'
				AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate';";
			error_log("QUERY TOTALS_SALE_COMB: ".$sql);

				//ultima compra del mes (inv_saldoalma)

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'TOTALS_SALE_MARKET':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];

			$sql = "
SELECT
 SUM(nu_fac_importeneto) AS nu_venta_soles,
 SUM(nu_fac_cantidad) AS nu_cantidad,
 SUM(ss_kardex_promedio_total) AS nu_costo_total,
 SUM(nu_fac_importeneto) - SUM(ss_kardex_promedio_total) AS nu_margen
FROM
 (SELECT
 d.nu_fac_cantidad,
 d.nu_fac_importeneto,
 (ROUND
  ((CASE
   WHEN sal.stk_costo" . $BeginMonth . " = 0.0000 THEN
    COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = d.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
   ELSE
    sal.stk_costo" . $BeginMonth . "
   END),2)
  *
  ROUND(d.nu_fac_cantidad, 2)
 ) AS ss_kardex_promedio_total
FROM
 fac_ta_factura_cabecera AS c
 RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
 JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
 LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
WHERE
 c.ch_fac_tipodocumento='45'
 AND c.ch_almacen = '" . $warehouse_id . "'
 AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
 AND art.art_unidad NOT IN('000GLN', '0000GL')--
) AS TOT_MARKET;
			";

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'DETAIL_SALE_COMB':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$sql = "SELECT
				C .codigo AS codigo,--0
				COMB.descripcion AS descripcion,--1
				ROUND(COMB.total_cantidad, 3) AS total_cantidad,--2
				ROUND(COMB.total_venta, 2) AS total_venta,--3
				(
					CASE
					WHEN AFC.af_cantidad IS NULL THEN
						COMB.af_cantidad
					ELSE
						AFC.af_cantidad
					END
				) AS af_cantidad,--4
				(
					CASE
					WHEN AFC.af_total IS NULL THEN
						COMB.af_soles
					ELSE
						AFC.af_total
					END
				) AS af_total,--5
				--0.000 AS consumo_galon,--
				--0.000 AS consumo_valor,--

				COST.costo_comb * (
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS costo, -- costo promedio 6
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) / (1 + (COMB.igv/100)) AS venta_sin_igv, --7 valor venta sin igv

				COMB.descuentos AS descuentos,--8
				(
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS neto_cantidad,--9
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) AS neto_soles--10
			FROM
				(
					SELECT
						ch_codigocombustible AS codigo
					FROM
						comb_ta_tanques
					WHERE ch_sucursal = '$warehouse_id'
				) C
			INNER JOIN (
				SELECT
					comb.ch_codigocombustible AS codigo,
					cmb.ch_nombrecombustible AS descripcion,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventavalor
						ELSE
							0
						END
					) AS total_venta,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventagalon
						ELSE
							0
						END
					) AS total_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							(comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_soles,
					ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
					nu_factor_igv AS igv
				FROM
					comb_ta_contometros comb
				LEFT JOIN comb_ta_combustibles cmb ON (
					comb.ch_codigocombustible = cmb.ch_codigocombustible
				)
				WHERE
					comb.dt_fechaparte BETWEEN '$BeginDate'
				AND '$EndDate'
				AND comb.ch_sucursal = TRIM ('$warehouse_id')
				GROUP BY
					comb.ch_codigocombustible,
					cmb.ch_nombrecombustible,
					comb.nu_factor_igv
			) COMB ON COMB.codigo = C .codigo
			LEFT JOIN (
				SELECT
					af.codigo AS codigo,
					SUM (af.importe) AS af_total,
					ROUND(SUM(af.cantidad), 3) AS af_cantidad
				FROM
					pos_ta_afericiones af
				WHERE
					af.dia BETWEEN '$BeginDate'
				AND '$EndDate'
				AND af.es = TRIM ('$warehouse_id')
				GROUP BY
					af.codigo
			) AFC ON AFC.codigo = C .codigo
			LEFT JOIN (
				SELECT stk_costo$BeginMonth as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '$BeginYear'
				AND stk_almacen = TRIM ('$warehouse_id')
			) COST ON (COST.art_codigo =  C.codigo)
			UNION ALL
			SELECT
			'11620308' AS codigo,
			'GNV' AS descripcion,
			SUM(0) AS total_cantidad,
			SUM(0) AS total_venta,
			SUM(0) AS af_cantidad,
			SUM(0) AS af_total,
			SUM(nu_costo_unitario*tot_cantidad) AS costo,
			SUM( CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END / (1 + (util_fn_igv()/100)) ) AS venta_sin_igv,
			SUM(0) AS descuentos,
			SUM(tot_cantidad) AS neto_cantidad,
			SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END)
			AS neto_soles
			FROM comb_liquidaciongnv
			WHERE ch_almacen = '$warehouse_id'
			AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate';";
			error_log("QUERY DETAIL_SALE_COMB: ".$sql);

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'DETAIL_SALE_MARKET':
			//corregido
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];

			$sql = "
SELECT
 codigo_linea AS co_linea,
 nombre_linea AS no_linea,
 nu_fac_cantidad AS nu_cantidad,
 0.0 AS nu_costo_promedio,
 ss_kardex_promedio_total AS nu_costo_total,
 nu_fac_importeneto AS nu_venta_soles,
 nu_fac_importeneto - ss_kardex_promedio_total AS nu_margen
FROM
 (SELECT
  linea.tab_elemento AS codigo_linea,
  FIRST(linea.tab_descripcion) AS nombre_linea,
  SUM(d.nu_fac_cantidad) AS nu_fac_cantidad,
  SUM(d.nu_fac_importeneto) AS nu_fac_importeneto,
  ROUND((COALESCE(FIRST(ITEMEST.ss_kardex_promedio_total), 0) + COALESCE(FIRST(ITEMPLU.ss_kardex_promedio_total), 0)), 2) AS ss_kardex_promedio_total
 FROM
  fac_ta_factura_cabecera AS c
  RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
  JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
  LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
  LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
  LEFT JOIN (
  SELECT
   art_linea,
   SUM(ss_kardex_promedio_total) AS ss_kardex_promedio_total
  FROM
   (SELECT
    art.art_linea,
    ROUND(
    CASE WHEN sal.stk_costo" . $BeginMonth . " = 0.0000 THEN
    COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = d.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
    ELSE
    sal.stk_costo" . $BeginMonth . "
    END
    , 2) * ROUND(d.nu_fac_cantidad, 2) AS ss_kardex_promedio_total
   FROM
    fac_ta_factura_cabecera AS c
    RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
    JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
    LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
   WHERE
    c.ch_fac_tipodocumento='45'
    AND c.ch_almacen = '" . $warehouse_id . "'
    AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
    AND art.art_plutipo='1'
   ) AS EST
  GROUP BY
   1
  ) AS ITEMEST ON(ITEMEST.art_linea = art.art_linea)
  LEFT JOIN (
  SELECT
   art_linea,
   SUM(ss_kardex_promedio_total) AS ss_kardex_promedio_total
  FROM
   (SELECT
    art.art_linea,
    ROUND(
    CASE WHEN sal.stk_costo" . $BeginMonth . " = 0.0000 THEN
    COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = ENLA.ch_item_estandar GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
    ELSE
    sal.stk_costo" . $BeginMonth . "
    END
    , 2) * ROUND(d.nu_fac_cantidad * COALESCE(ENLA.nu_cantidad_descarga,1), 2) AS ss_kardex_promedio_total
   FROM
    fac_ta_factura_cabecera AS c
    RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
    JOIN int_ta_enlace_items AS ENLA ON(ENLA.art_codigo = d.art_codigo)
    JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
    LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = ENLA.ch_item_estandar AND sal.stk_periodo = '" . $BeginYear . "')
   WHERE
    c.ch_fac_tipodocumento='45'
    AND c.ch_almacen = '" . $warehouse_id . "'
    AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
    AND art.art_plutipo='2'
   ) AS PLU
  GROUP BY
   1
  ) AS ITEMPLU ON(ITEMPLU.art_linea = art.art_linea)
 WHERE
  c.ch_fac_tipodocumento='45'
  AND c.ch_almacen = '" . $warehouse_id . "'
  AND art.art_unidad NOT IN('000GLN', '0000GL')--
  AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
 GROUP BY
  linea.tab_elemento
) AS DETAIL_MARKET
ORDER BY
nu_margen DESC
			";

/*
			$sql = "
SELECT
 codigo_linea AS co_linea,
 nombre_linea AS no_linea,
 nu_fac_cantidad AS nu_cantidad,
 0.0 AS nu_costo_promedio,
 ss_kardex_promedio_total AS nu_costo_total,
 nu_fac_importeneto AS nu_venta_soles,
 nu_fac_importeneto - ss_kardex_promedio_total AS nu_margen
FROM
 (SELECT
  linea.tab_elemento AS codigo_linea,
  FIRST(linea.tab_descripcion) AS nombre_linea,
  SUM(d.nu_fac_cantidad) AS nu_fac_cantidad,
  SUM(d.nu_fac_importeneto) AS nu_fac_importeneto,
  (ROUND
   ((CASE
    WHEN FIRST(sal.stk_costo" . $BeginMonth . ") = 0.0000 THEN
     COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = FIRST(d.art_codigo) GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
    ELSE
     FIRST(sal.stk_costo" . $BeginMonth . ")
    END),2)
   *
   ROUND(SUM(d.nu_fac_cantidad), 2)
  ) AS ss_kardex_promedio_total
 FROM
  fac_ta_factura_cabecera AS c
  RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
  JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
  LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
  LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
 WHERE
  c.ch_fac_tipodocumento='45'
  AND c.ch_almacen = '" . $warehouse_id . "'
  AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
  AND art.art_unidad NOT IN('000GLN', '0000GL')
 GROUP BY
  linea.tab_elemento
 ) AS DETAIL_MARKET
 ORDER BY
  nu_margen DESC;
			";
*/

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'STOCK_COMB':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$days = $_REQUEST['days'];
		
			/*Datos para querie Stock Diario (Modo Proyección)
			Cuadro adjunto se agregarían 2 columnas:
			1. Proyeccion: Cantidad de Venta Proyectada por N días
			2. Utilidad: Seria igual a (Proyeccion x Ultimo Precio de Venta) – (Proyecccion x Costo Promedio)
			*/
			$checkProyeccion = $_REQUEST['checkProyeccion'];
			$desdeProyeccion = $_REQUEST['desdeProyeccion'];
			$hastaProyeccion = $_REQUEST['hastaProyeccion'];
			$diasDiferenciaProyeccion = $_REQUEST['diasDiferenciaProyeccion'];
			$diasProyeccion = $_REQUEST['diasProyeccion'];
			
			$fecha_desde_proyeccion_explode = explode("/", $desdeProyeccion);
			$fecha_hasta_proyeccion_explode = explode("/", $hastaProyeccion);

			$fechadproy  = $fecha_desde_proyeccion_explode['2'] . "-" . $fecha_desde_proyeccion_explode['1'] . "-" . $fecha_desde_proyeccion_explode['0'];
			$fechaaproy  = $fecha_hasta_proyeccion_explode['2'] . "-" . $fecha_hasta_proyeccion_explode['1'] . "-" . $fecha_hasta_proyeccion_explode['0'];
			
			$daysProyeccion     = "0";
			$sql_costo_promedio = ",0 AS costo_comb";
			$sql_precio_venta   = ",0 AS precio_venta";			

			//Usar datos de Modo Proyección
			if ($checkProyeccion == 'true') {
				//Reemplazar datos
					$BeginDate = $fechadproy;
					$EndDate = $fechaaproy;
					$days = $diasDiferenciaProyeccion;
					$daysProyeccion = $diasProyeccion;

				//Datos para calcular utilidad
					//Datos para query Costo Promedio
					$stk_periodo = $fecha_desde_proyeccion_explode['2'];
					$stk_mes = $fecha_desde_proyeccion_explode['1'];

					//Datos para query Ultimo Precio de Venta
					$fechadproy = $fechadproy;
					$fechaaproy = $fechaaproy;

					//SQL Costo Promedio
					$sql_costo_promedio = "
						,(SELECT
							stk_costo$stk_mes AS costo_comb
						FROM
							inv_saldoalma 
						WHERE
							stk_periodo = '$stk_periodo'
							AND stk_almacen = TRIM ('$warehouse_id')
							AND art_codigo = tanques.ch_codigocombustible
						LIMIT 1) AS costo_comb
					";

					//SQL Ultimo Precio de Venta
					$sql_precio_venta = "
						,(SELECT
							(nu_ventavalor/1.18) / nu_ventagalon AS precio_venta
						FROM
							comb_ta_contometros 
						WHERE
							dt_fechaparte BETWEEN '$fechadproy' AND '$fechaaproy'
							AND ch_sucursal = TRIM ('$warehouse_id')
							AND ch_codigocombustible = tanques.ch_codigocombustible
							AND nu_ventavalor != 0
							AND nu_ventagalon != 0	
						ORDER BY 
							dt_fechaparte DESC
						LIMIT 1) AS precio_venta
					";
			}			
			/*Cerrar*/

			$sql = "SELECT
				tanques.ch_codigocombustible AS cod_comb,
				combustibles.ch_nombrecombustible AS desc_comb,
				tanques.nu_capacidad,
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days AS nu_venta, -- Promedio diario vendido en galones
				contometros.ch_tanque,
				mediciondiaria.nu_medicion,
				CASE
					WHEN tanques.nu_capacidad > 0 THEN
						(mediciondiaria.nu_medicion / tanques.nu_capacidad) * 100
					ELSE
						0
				END AS porcentaje_existente,
			 	--mediciondiaria.nu_medicion / ((tanques.nu_capacidad - mediciondiaria.nu_medicion) / $days) AS dias,

			 	--mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) AS tiempo,			
			 	CASE
					WHEN ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) > 0 THEN
						mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days)
					ELSE
						0
				END AS tiempo, --Calculamos tiempo pero validando error de division / 0
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) AS suma,
				$days AS dia, -- Dias a proyectar
				compra.mov_cantidad AS cantidad_ultima_compra,
				compra.mov_fecha AS fecha_ultima_compra,
				'$BeginDate' AS BeginDate,
				'$EndDate' AS EndDate,
				((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) * $daysProyeccion AS nu_venta_proyeccion -- Proyeccion: Promedio diario vendido en galones * Dias a proyectar
				
				-- Costo Promedio
				$sql_costo_promedio

				-- Ultimo Precio de Venta
				$sql_precio_venta
				
			FROM
				comb_ta_contometros contometros
			JOIN comb_ta_tanques tanques                    ON (contometros.ch_tanque = tanques.ch_tanque AND tanques.ch_sucursal = '$warehouse_id' )
			LEFT JOIN comb_ta_mediciondiaria mediciondiaria ON (contometros.ch_tanque = mediciondiaria.ch_tanque AND mediciondiaria.ch_sucursal = '$warehouse_id' AND mediciondiaria.dt_fechamedicion = '$EndDate' )
			JOIN comb_ta_combustibles combustibles          ON (tanques.ch_codigocombustible = combustibles.ch_codigocombustible)
			JOIN inv_ta_compras_devoluciones compra         ON (TRIM(tanques.ch_codigocombustible) = TRIM(compra.art_codigo) AND compra.mov_fecha = (SELECT MAX(mov_fecha) FROM inv_ta_compras_devoluciones WHERE TRIM(art_codigo) = TRIM(tanques.ch_codigocombustible)))
			WHERE
				contometros.ch_sucursal = '$warehouse_id'
			AND contometros.dt_fechaparte BETWEEN '$BeginDate'
			AND '$EndDate'
			GROUP BY
				contometros.ch_tanque, tanques.ch_codigocombustible, tanques.nu_capacidad, mediciondiaria.nu_medicion, combustibles.ch_nombrecombustible,
				compra.mov_cantidad, compra.mov_fecha
			ORDER BY
				tanques.ch_codigocombustible ASC;";
			//echo $sql;
			error_log("QUERY STOCK_COMB: ".$sql);

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'STOCK_COMB_R':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$days = $_REQUEST['days'];
		
			/*Datos para querie Stock Diario (Modo Proyección)
			Cuadro adjunto se agregarían 2 columnas:
			1. Proyeccion: Cantidad de Venta Proyectada por N días
			2. Utilidad: Seria igual a (Proyeccion x Ultimo Precio de Venta) – (Proyecccion x Costo Promedio)
			*/
			$checkProyeccion = $_REQUEST['checkProyeccion'];
			$desdeProyeccion = $_REQUEST['desdeProyeccion'];
			$hastaProyeccion = $_REQUEST['hastaProyeccion'];
			$diasDiferenciaProyeccion = $_REQUEST['diasDiferenciaProyeccion'];
			$diasProyeccion = $_REQUEST['diasProyeccion'];
			
			$fecha_desde_proyeccion_explode = explode("/", $desdeProyeccion);
			$fecha_hasta_proyeccion_explode = explode("/", $hastaProyeccion);

			$fechadproy  = $fecha_desde_proyeccion_explode['2'] . "-" . $fecha_desde_proyeccion_explode['1'] . "-" . $fecha_desde_proyeccion_explode['0'];
			$fechaaproy  = $fecha_hasta_proyeccion_explode['2'] . "-" . $fecha_hasta_proyeccion_explode['1'] . "-" . $fecha_hasta_proyeccion_explode['0'];
			
			$daysProyeccion     = "0";
			$sql_costo_promedio = ",0 AS costo_comb";
			$sql_precio_venta   = ",0 AS precio_venta";			

			//Usar datos de Modo Proyección
			if ($checkProyeccion == 'true') {
				//Reemplazar datos
					$BeginDate = $fechadproy;
					$EndDate = $fechaaproy;
					$days = $diasDiferenciaProyeccion;
					$daysProyeccion = $diasProyeccion;

				//Datos para calcular utilidad
					//Costo Promedio
					$stk_periodo = $fecha_desde_proyeccion_explode['2'];
					$stk_mes = $fecha_desde_proyeccion_explode['1'];

					//Ultimo Precio de Venta
					$fechadproy = $fechadproy;
					$fechaaproy = $fechaaproy;

					//SQL para Costo Promedio
					$sql_costo_promedio = "
						,(SELECT
							stk_costo$stk_mes AS costo_comb
						FROM
							inv_saldoalma 
						WHERE
							stk_periodo = '$stk_periodo'
							AND stk_almacen = TRIM ('$warehouse_id')
							AND art_codigo = tanques.ch_codigocombustible
						LIMIT 1) AS costo_comb
					";

					//SQL para Ultimo Precio de Venta
					$sql_precio_venta = "
						,(SELECT
							(nu_ventavalor/1.18) / nu_ventagalon AS precio_venta
						FROM
							comb_ta_contometros 
						WHERE
							dt_fechaparte BETWEEN '$fechadproy' AND '$fechaaproy'
							AND ch_sucursal = TRIM ('$warehouse_id')
							AND ch_codigocombustible = tanques.ch_codigocombustible
							AND nu_ventavalor != 0
							AND nu_ventagalon != 0	
						ORDER BY 
							dt_fechaparte DESC
						LIMIT 1) AS precio_venta
					";
			}			
			/*Cerrar*/

			$sql = "SELECT
				tanques.ch_codigocombustible AS cod_comb,
				combustibles.ch_nombrecombustible AS desc_comb,
				tanques.nu_capacidad,
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days AS nu_venta, -- Promedio diario vendido en galones
				contometros.ch_tanque,
				mediciondiaria.nu_medicion,
				CASE
					WHEN tanques.nu_capacidad > 0 THEN
						(mediciondiaria.nu_medicion / tanques.nu_capacidad) * 100
					ELSE
						0
				END AS porcentaje_existente,
			 	--mediciondiaria.nu_medicion / ((tanques.nu_capacidad - mediciondiaria.nu_medicion) / $days) AS dias,

			 	--mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) AS tiempo,			
			 	CASE
					WHEN ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) > 0 THEN
						mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days)
					ELSE
						0
				END AS tiempo, --Calculamos tiempo pero validando error de division / 0
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) AS suma,
				$days AS dia, -- Dias a proyectar
				compra.mov_cantidad AS cantidad_ultima_compra,
				compra.mov_fecha AS fecha_ultima_compra,
				'$BeginDate' AS BeginDate,
				'$EndDate' AS EndDate,
				((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) * $daysProyeccion AS nu_venta_proyeccion -- Proyeccion: Promedio diario vendido en galones * Dias a proyectar
				
				-- Costo Promedio
				$sql_costo_promedio

				-- Ultimo Precio de Venta
				$sql_precio_venta
				
			FROM
				comb_ta_contometros contometros
			JOIN comb_ta_tanques tanques                    ON (contometros.ch_tanque = tanques.ch_tanque AND tanques.ch_sucursal = '$warehouse_id' )
			LEFT JOIN comb_ta_mediciondiaria mediciondiaria ON (contometros.ch_tanque = mediciondiaria.ch_tanque AND mediciondiaria.ch_sucursal = '$warehouse_id' AND mediciondiaria.dt_fechamedicion = '$EndDate' )
			JOIN comb_ta_combustibles combustibles          ON (tanques.ch_codigocombustible = combustibles.ch_codigocombustible)
			JOIN inv_ta_compras_devoluciones compra         ON (TRIM(tanques.ch_codigocombustible) = TRIM(compra.art_codigo) AND compra.mov_fecha = (SELECT MAX(mov_fecha) FROM inv_ta_compras_devoluciones WHERE TRIM(art_codigo) = TRIM(tanques.ch_codigocombustible)))
			WHERE
				contometros.ch_sucursal = '$warehouse_id'
			AND contometros.dt_fechaparte BETWEEN '$BeginDate'
			AND '$EndDate'
			GROUP BY
				contometros.ch_tanque, tanques.ch_codigocombustible, tanques.nu_capacidad, mediciondiaria.nu_medicion, combustibles.ch_nombrecombustible,
				compra.mov_cantidad, compra.mov_fecha
			ORDER BY
				tanques.ch_codigocombustible ASC;";
			//echo $sql;
			error_log("QUERY STOCK_COMB: ".$sql);

			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		/**
		 * Demo de data serializada
		 */
		case 'DEMO_SERIAL':
			$sql = "SELECT 'Kewin' AS name, 'Serquen' AS surname, 'KWN' AS username;";
			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'DEMO_':
			$sql = "SELECT
				tanques.ch_codigocombustible AS cod_comb,
				combustibles.ch_nombrecombustible AS desc_comb,
				tanques.nu_capacidad,
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / 7 AS nu_venta,
				contometros.ch_tanque,
				mediciondiaria.nu_medicion,
				CASE
			WHEN tanques.nu_capacidad > 0 THEN
				(mediciondiaria.nu_medicion / tanques.nu_capacidad) * 100
			ELSE
				0
			END AS porcentaje_existente,
			 --mediciondiaria.nu_medicion / ((tanques.nu_capacidad - mediciondiaria.nu_medicion) / 7) AS dias,
			 mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / 7) AS tiempo,
			 (SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) AS suma,
			 7 AS dia,
			 compra.mov_cantidad AS cantidad_ultima_compra,
			 compra.mov_fecha AS fecha_ultima_compra,
			 '2017-02-05' AS BeginDate,
			 '2017-02-12' AS EndDate
			FROM
				comb_ta_contometros contometros
			JOIN comb_ta_tanques tanques ON (contometros.ch_tanque = tanques.ch_tanque AND tanques.ch_sucursal = '205' )
			LEFT JOIN comb_ta_mediciondiaria mediciondiaria ON (contometros.ch_tanque = mediciondiaria.ch_tanque AND mediciondiaria.ch_sucursal = '205' AND mediciondiaria.dt_fechamedicion = '2017-02-12' )
			JOIN comb_ta_combustibles combustibles ON (tanques.ch_codigocombustible = combustibles.ch_codigocombustible)
			JOIN inv_ta_compras_devoluciones compra ON (TRIM(tanques.ch_codigocombustible) = TRIM(compra.art_codigo) AND compra.mov_fecha = (SELECT MAX(mov_fecha) FROM inv_ta_compras_devoluciones WHERE TRIM(art_codigo) = TRIM(tanques.ch_codigocombustible)))
			WHERE
				contometros.ch_sucursal = '205'
			AND contometros.dt_fechaparte BETWEEN '2017-02-05'
			AND '2017-02-12'
			GROUP BY
				contometros.ch_tanque, tanques.ch_codigocombustible, tanques.nu_capacidad, mediciondiaria.nu_medicion, combustibles.ch_nombrecombustible,
				compra.mov_cantidad, compra.mov_fecha
			ORDER BY
				tanques.ch_codigocombustible ASC;";

			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		
		case 'TOTALS_SUMARY_SALE':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$sql = "SELECT
				C .codigo AS codigo,--0
				COMB.descripcion AS descripcion,--1
				ROUND(COMB.total_cantidad, 3) AS total_cantidad,--2
				ROUND(COMB.total_venta, 2) AS total_venta,--3
				(
					CASE
					WHEN AFC.af_cantidad IS NULL THEN
						COMB.af_cantidad
					ELSE
						AFC.af_cantidad
					END
				) AS af_cantidad,--4
				(
					CASE
					WHEN AFC.af_total IS NULL THEN
						COMB.af_soles
					ELSE
						AFC.af_total
					END
				) AS af_total,--5
				--0.000 AS consumo_galon,--
				--0.000 AS consumo_valor,--

				COST.costo_comb * (
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS costo, -- costo promedio 6
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) / (1 + (COMB.igv/100)) AS venta_sin_igv, --7 valor venta sin igv

				COMB.descuentos AS descuentos,--8
				(
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS neto_cantidad,--9
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) AS neto_soles--10
				, gasto_interno.nu_importe as importe_ci
				, gasto_interno.nu_cantidad as cantidad_ci
			FROM
				(
					SELECT
						ch_codigocombustible AS codigo
					FROM
						comb_ta_tanques
					WHERE ch_sucursal = '$warehouse_id'
				) C
			INNER JOIN (
				SELECT
					comb.ch_codigocombustible AS codigo,
					cmb.ch_nombrecombustible AS descripcion,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventavalor
						ELSE
							0
						END
					) AS total_venta,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventagalon
						ELSE
							0
						END
					) AS total_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							(comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_soles,
					ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
					nu_factor_igv AS igv
				FROM
					comb_ta_contometros comb
				LEFT JOIN comb_ta_combustibles cmb ON (
					comb.ch_codigocombustible = cmb.ch_codigocombustible
				)
				WHERE
					comb.dt_fechaparte BETWEEN '$BeginDate'
				AND '$EndDate'
				AND comb.ch_sucursal = TRIM ('$warehouse_id')
				GROUP BY
					comb.ch_codigocombustible,
					cmb.ch_nombrecombustible,
					comb.nu_factor_igv
			) COMB ON COMB.codigo = C .codigo
			LEFT JOIN (
				SELECT
					af.codigo AS codigo,
					SUM (af.importe) AS af_total,
					ROUND(SUM(af.cantidad), 3) AS af_cantidad
				FROM
					pos_ta_afericiones af
				WHERE
					af.dia BETWEEN '$BeginDate'
				AND '$EndDate'
				AND af.es = TRIM ('$warehouse_id')
				GROUP BY
					af.codigo
			) AFC ON AFC.codigo = C .codigo
			LEFT JOIN (
				SELECT stk_costo$BeginMonth as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '$BeginYear'
				AND stk_almacen = TRIM ('$warehouse_id')
			) COST ON (COST.art_codigo =  C.codigo)
			LEFT JOIN (
				SELECT
					detalle.ch_articulo, SUM(detalle.nu_importe) AS nu_importe, SUM(detalle.nu_cantidad) AS nu_cantidad
				FROM
					val_ta_cabecera cabecera
				JOIN int_clientes clientes ON (
					cabecera.ch_cliente = clientes.cli_ruc
				)
				JOIN val_ta_detalle detalle ON (
					cabecera.ch_documento = detalle.ch_documento AND cabecera.ch_sucursal = detalle.ch_sucursal AND cabecera.dt_fecha = detalle.dt_fecha
				)
				/*JOIN int_ta_sucursales sucursales ON ( //ESTE JOIN NO TIENE SENTIDO, NUNCA OBTENDRA NADA
					clientes.cli_ruc = sucursales.ruc
				)*/
				WHERE
				TRIM(detalle.ch_sucursal) = TRIM('$warehouse_id')
				AND detalle.dt_fecha BETWEEN '$BeginDate' AND '$EndDate'
				--AND detalle.ch_articulo = TRIM('11620304')
				GROUP BY detalle.ch_articulo
			) gasto_interno ON ( C.codigo = gasto_interno.ch_articulo)
			UNION ALL
			SELECT
			'11620308' AS codigo,
			'GNV' AS descripcion,
			SUM(0) AS total_cantidad,
			SUM(0) AS total_venta,
			SUM(0) AS af_cantidad,
			SUM(0) AS af_total,
			SUM(nu_costo_unitario*tot_cantidad) AS costo,
			SUM( CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END / (1 + (util_fn_igv()/100)) ) AS venta_sin_igv,
			SUM(0) AS descuentos,
			SUM(tot_cantidad) AS neto_cantidad,
			SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END)
			AS neto_soles
			, SUM(0) AS importe_ci
			, SUM(0) AS cantidad_ci
			FROM comb_liquidaciongnv
			WHERE ch_almacen = '$warehouse_id'
			AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate';";
			error_log( $sql );

			if(isset($_REQUEST['unserialize'])) {
				SQLImplodeSerialize($sql, true);
			} else {
				SQLImplode($sql);
			}

			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			// error_log($contenido);
			// error_log($comprimido);
			echo $comprimido;
		break;

		case 'TOTALS_SALE_FOR_HOURS':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];
			$local = $_REQUEST['local'];
			$producto = $_REQUEST['productos'];
			$unidadmedida = $_REQUEST['unidadmedida'];
			$factor = 3.785411784;
			
			$sql = "SELECT  
						es as ch_sucursal,
						importe as nu_ventavalor,

						CASE
							WHEN TRIM(codigo) = '11620307' AND 'Litros_a_Galones' = '$unidadmedida' THEN cantidad/$factor
							WHEN TRIM(codigo) = '11620307' AND 'Galones_a_Litros' = '$unidadmedida' THEN cantidad*$factor							
							ELSE cantidad
						END AS nu_ventagalon,

						'0.00' nu_afericion,
						precio as nu_preciogalon,
						TRIM(codigo) as ch_codigocombustible,
						fecha::DATE as dt_fechaparte,
						EXTRACT(HOUR FROM fecha),
						Case EXTRACT(DOW FROM fecha)
							when 0 then 'DOMINGO'
							when 1 then 'LUNES'
							when 2 then 'MARTES'
							when 3 then 'MIERCOLES'
							when 4 then 'JUEVES'
							when 5 then 'VIERNES'
							when 6 then 'SABADO'
						end as dia ,
						pump AS lado,
						tipo,
						
						(SELECT
							ch_almacen || ' - ' || trim(ch_nombre_almacen)
						FROM
							inv_ta_almacenes
						WHERE
							ch_clase_almacen='1'
							AND ch_almacen = es
						ORDER BY
							ch_almacen) as ch_almacen_completo

						".
					" 
					FROM pos_trans". substr($desde,6,4) . substr($desde,3,2) . " ".
					"
					WHERE
						dia::DATE between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')  
						--AND importe > 0
						--AND cantidad > 0 
					";
			if ($local == "COMBUSTIBLE") {
				$sql .= " AND tipo='C'";

				if ($producto != "TODOS") {
					$sql .= " AND codigo='" . pg_escape_string($producto) . "'";
				}
			} else if ($local == "MARKET") {
				$sql .= " AND tipo='M'";
			}

			// $sql .= " 
			// 	ORDER BY
			// 		ch_sucursal,
			// 		dt_fechaparte
			// 	";

			/*UNION*/
			$sql .= "
					UNION ALL
					";
			$sql .= "SELECT  
						es as ch_sucursal,
						importe as nu_ventavalor,
						
						CASE
							WHEN TRIM(codigo) = '11620307' AND 'Litros_a_Galones' = '$unidadmedida' THEN cantidad/$factor
							WHEN TRIM(codigo) = '11620307' AND 'Galones_a_Litros' = '$unidadmedida' THEN cantidad*$factor
							ELSE cantidad
						END AS nu_ventagalon,

						'0.00' nu_afericion,
						precio as nu_preciogalon,
						TRIM(codigo) as ch_codigocombustible,
						fecha::DATE as dt_fechaparte,
						EXTRACT(HOUR FROM fecha),
						Case EXTRACT(DOW FROM fecha)
							when 0 then 'DOMINGO'
							when 1 then 'LUNES'
							when 2 then 'MARTES'
							when 3 then 'MIERCOLES'
							when 4 then 'JUEVES'
							when 5 then 'VIERNES'
							when 6 then 'SABADO'
						end as dia ,
						pump AS lado,
						tipo,
						
						(SELECT
							ch_almacen || ' - ' || trim(ch_nombre_almacen)
						FROM
							inv_ta_almacenes
						WHERE
							ch_clase_almacen='1'
							AND ch_almacen = es
						ORDER BY
							ch_almacen) as ch_almacen_completo

						".
					" 
					FROM pos_transtmp"." ".
					"
					WHERE
						dia::DATE between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')  
						--AND importe > 0
						--AND cantidad > 0 
					";
			if ($local == "COMBUSTIBLE") {
				$sql .= " AND tipo='C'";

				if ($producto != "TODOS") {
					$sql .= " AND codigo='" . pg_escape_string($producto) . "'";
				}
			} else if ($local == "MARKET") {
				$sql .= " AND tipo='M'";
			}

			$sql .= " 
				ORDER BY
					ch_sucursal,
					dt_fechaparte
				;";
			/*CERRAR UNION*/
			error_log( $sql );
			
			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = $contenido; // $comprimido = gzcompress($contenido);
			// error_log($contenido);
			// error_log($comprimido);		
			echo $comprimido;
		break;

		/**
		 * Para ejecutar en modo prueba:
		 * http://172.18.8.12/sistemaweb/centralizer_.php?mod=TOTALS_LIQUIDACION_DIARIA&from=20200715&to=20200715&warehouse_id=003&desde=15/07/2020&hasta=15/07/2020
		 */
		case 'TOTALS_LIQUIDACION_DIARIA':
			include_once('/sistemaweb/include/mvc_sistemaweb.php');
			include_once('/sistemaweb/combustibles/movimientos/m_cajabanco.php');

			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];

			/*Datos para obtener Saldo acumulado Caja y Banco*/
			$fecha_explode = explode("/", $hasta);

			$iAlmacen    = $warehouse_id;
			$dYear       = $fecha_explode['2'];
			$dMonth      = $fecha_explode['1'];
			$dDay        = $fecha_explode['0'];
			$pos_transYM = "pos_trans" . $dYear . $dMonth;
			/*Cerrar Datos para obtener Saldo acumulado Caja y Banco*/

			/*Datos para obtener Invetario de Combustible*/
			$estaciones = $_REQUEST['warehouse_id'];
			/*Cerrar Datos para obtener Invetario de Combustible*/

			$porciones_desde = explode("/", $desde);
			$desde_ = $porciones_desde = $porciones_desde[2] . "-" . $porciones_desde[1] . "-" . $porciones_desde[0];

			$porciones_hasta = explode("/", $hasta);
			$hasta_ = $porciones_desde = $porciones_hasta[2] . "-" . $porciones_hasta[1] . "-" . $porciones_hasta[0];
			
			$sql1_venta_combustible = "
				SELECT
					SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as liquido,
					SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as liquido_canti,
					SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as glp,
					SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as glp_canti
				FROM
					comb_ta_contometros
				WHERE
					ch_sucursal='" . pg_escape_string($warehouse_id) . "' AND 
					dt_fechaparte BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "' 
			";			
			$contenido['1_venta_vombustible'] = SQLImplodeArray($sql1_venta_combustible);
			
			$sql2_venta_productos_promociones = "
				SELECT
					SUM(d.nu_fac_valortotal) AS ventatienda,
					SUM(d.nu_fac_cantidad) AS cantienda
				FROM
					fac_ta_factura_cabecera f 
					LEFT JOIN int_clientes c on (f.cli_codigo=c.cli_codigo AND c.cli_ndespacho_efectivo != 1)
					LEFT JOIN fac_ta_factura_detalle d ON (f.ch_fac_tipodocumento=d.ch_fac_tipodocumento and f.ch_fac_seriedocumento=d.ch_fac_seriedocumento
										and f.ch_fac_numerodocumento=d.ch_fac_numerodocumento and f.cli_codigo=d.cli_codigo)
				WHERE
					f.ch_fac_seriedocumento='" . pg_escape_string($warehouse_id) . "' AND 
					f.ch_fac_tipodocumento='45' AND
					f.dt_fac_fecha BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'	
			";
			$contenido['2_venta_productos_promociones'] = SQLImplodeArray($sql2_venta_productos_promociones);

			$sql2_venta_productos_promociones_detalle = "
				SELECT
					art.art_linea AS linea,
					max(tab.tab_descripcion) AS descripcion_linea,
					sum(d.nu_fac_cantidad) AS cantidad,
					sum(d.nu_fac_valortotal) AS importe
				FROM
					fac_ta_factura_cabecera c
					RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
					RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
					LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
					LEFT JOIN int_clientes k on c.cli_codigo=k.cli_codigo AND k.cli_ndespacho_efectivo != 1
				WHERE
					c.ch_fac_tipodocumento='45'
					AND c.ch_fac_seriedocumento='" . pg_escape_string($warehouse_id) . "'
					AND c.dt_fac_fecha BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
					AND c.ch_almacen='" . pg_escape_string($warehouse_id) . "'
				GROUP BY
					art.art_linea
				ORDER BY
					art.art_linea;
			";
			$contenido['2_venta_productos_promociones_detalle'] = SQLImplodeArray($sql2_venta_productos_promociones_detalle);

			$sql3_vales_credito_detalle = "
				SELECT   
					VC.ch_cliente as codcliente,
					C.cli_ruc as ruc,
					C.cli_razsocial	as cliente,
					sum(VD.galones) as cantidad,
					sum(VC.nu_importe) as importe 
				FROM
					val_ta_cabecera VC 
					inner join (SELECT ch_sucursal,dt_fecha,ch_documento,sum(nu_cantidad) as galones FROM val_ta_detalle VTD GROUP BY ch_sucursal,dt_fecha,ch_documento) VD 
					on VC.ch_sucursal = VD.ch_sucursal and VC.dt_fecha = VD.dt_fecha and VC.ch_documento = VD.ch_documento 
					inner join (SELECT cli_codigo,cli_ruc,cli_razsocial FROM int_clientes WHERE cli_ndespacho_efectivo != 1) C 
					on VC.ch_cliente = C.cli_codigo 
				WHERE		
					VC.ch_sucursal='" . pg_escape_string($warehouse_id) . "' 
					AND VC.dt_fecha between '" . pg_escape_string($desde_) . "' and '" . pg_escape_string($hasta_) . "' 
					AND VC.ch_estado='1'
				GROUP BY	
					VC.ch_cliente,
					C.cli_ruc,
					C.cli_razsocial 
				ORDER BY 	
					VC.ch_cliente
			";
			$contenido['3_vales_credito_detalle'] = SQLImplodeArray($sql3_vales_credito_detalle);

			$sql4_tarjetas_credito_detalle = "
				SELECT 
					g.tab_descripcion as descripciontarjeta,
					SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta
				FROM
					".$pos_transYM." t
					LEFT JOIN int_tabla_general g ON (trim(t.at) = substring(g.tab_elemento,6,6) AND g.tab_tabla='95' AND g.tab_elemento != '000000')
				WHERE
					t.es		= '" . pg_escape_string($warehouse_id) . "'
					AND t.fpago	= '2'
					AND t.dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
				GROUP BY
					1
			";
			$contenido['4_tarjetas_credito_detalle'] = SQLImplodeArray($sql4_tarjetas_credito_detalle);

			$sql4_tarjetas_credito_total = "
				SELECT
					SUM(t.importe)-SUM(COALESCE(t.km,0)) AS tarjetascredito
				FROM
					".$pos_transYM." t
				WHERE
					t.es		= '" . pg_escape_string($warehouse_id) . "'
					AND t.fpago	= '2'
					AND t.dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'				
			";
			error_log($sql4_tarjetas_credito_total);
			$contenido['4_tarjetas_credito_total'] = SQLImplodeArray($sql4_tarjetas_credito_total);

			$sql5_descuentos = "
				SELECT
					SUM(importe) AS descuentos
				FROM
					".$pos_transYM."
				WHERE
					es='" . pg_escape_string($warehouse_id) . "'
					AND td IN('B','F')
					AND dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "' 
					AND grupo='D'
			";
			$contenido['5_descuentos'] = SQLImplodeArray($sql5_descuentos);

			$sql6_diferencias_precio_vales = "
				SELECT
					SUM(t.importe*-1) AS difprecio
				FROM
					".$pos_transYM." AS t
					LEFT JOIN int_clientes AS c
					ON (c.cli_codigo = t.cuenta AND c.cli_ndespacho_efectivo != 1)
				WHERE
					t.es='" . pg_escape_string($warehouse_id) . "'
					AND t.td='N'
					AND t.dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "' 
					AND grupo='D'
			";
			$contenido['6_diferencias_precio_vales'] = SQLImplodeArray($sql6_diferencias_precio_vales);

			$sql7_afericiones = "
				SELECT 
					SUM(importe) AS afericiones
				FROM
					pos_ta_afericiones
				WHERE
					es='" . pg_escape_string($warehouse_id) . "' AND
					dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
			";
			$contenido['7_afericiones'] = SQLImplodeArray($sql7_afericiones);

			$sql_total_depositos_pos = "
				SELECT 
					SUM(
						CASE 
							WHEN ch_moneda='01'THEN nu_importe 
							WHEN ch_moneda='02'THEN nu_importe * nu_tipo_cambio
						END) AS depositospos
				FROM 
					pos_depositos_diarios
				WHERE 
					ch_almacen='" . pg_escape_string($warehouse_id) . "' AND 
					(ch_valida='S' or ch_valida='s' ) AND
					dt_dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "' 
			";
			$contenido['total_depositos_pos'] = SQLImplodeArray($sql_total_depositos_pos);

			$sql8_sobrantes_faltantes_por_trabajador = "
				SELECT 
					c.ch_codigo_trabajador cod_trabajador, 
					t.ch_apellido_paterno||' '||t.ch_apellido_materno||' '||t.ch_nombre1||' '||t.ch_nombre2 as nom_trabajador,
					c.importe,
					c.flag
				FROM 
					comb_diferencia_trabajador c
					LEFT JOIN pla_ta_trabajadores t ON (c.ch_codigo_trabajador = t.ch_codigo_trabajador)
				WHERE
					es='" . pg_escape_string($warehouse_id) . "' AND
					dia BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
			";
			$contenido['8_sobrantes_faltantes_por_trabajador'] = SQLImplodeArray($sql8_sobrantes_faltantes_por_trabajador);

			$sql10_ingresos = "				
				SELECT
					i.pay_number || ' - ' || CASE WHEN cli.cli_rsocialbreve IS NULL THEN c.reference ELSE cli.cli_rsocialbreve END AS documento,
					(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS ingresos
				FROM
					c_cash_transaction c
					INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
				WHERE
					c.ware_house = '" . pg_escape_string($warehouse_id) . "'
					AND c.d_system BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
					AND c.type = '0'
				ORDER BY
					documento desc 
			";
			$contenido['10_ingresos'] = SQLImplodeArray($sql10_ingresos);

			$sql10_1_ingresos_contado_dia = "				
				SELECT
					c_op.name || ' - ' || c_mp.name || ' - ' ||  i.pay_number || ' - ' || CASE WHEN cli.cli_rsocialbreve IS NULL THEN c.reference ELSE cli.cli_rsocialbreve END AS documento,
					(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS ingresos,	
					c_mp.c_cash_mpayment_id AS c_cash_mpayment_id,
					c_mp.name AS metodo_pago,
					c_ba.name AS banco
				FROM
					c_cash_transaction c
					INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
					LEFT JOIN c_cash_operation c_op ON (c.c_cash_operation_id = c_op.c_cash_operation_id AND c_op.type = 0) 
					LEFT JOIN c_cash_mpayment c_mp ON (i.c_cash_mpayment_id = c_mp.c_cash_mpayment_id)
					LEFT JOIN c_bank c_ba ON (i.c_bank_id = c_ba.c_bank_id)
				WHERE
					c.ware_house = '" . pg_escape_string($warehouse_id) . "'
					AND c.d_system BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
					AND c.type = '0'
					AND c_op.c_cash_operation_id = '2' --VENTAS TICKETS
				ORDER BY
					documento desc 
			";
			$contenido['10_1_ingresos_contado_dia'] = SQLImplodeArray($sql10_1_ingresos_contado_dia);

			$sql10_2_ingresos_cobranzas_amortizaciones_por_cc = "				
				SELECT
					c_op.name || ' - ' || c_mp.name || ' - ' ||  i.pay_number || ' - ' || CASE WHEN cli.cli_rsocialbreve IS NULL THEN c.reference ELSE cli.cli_rsocialbreve END AS documento,
					(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS ingresos
				FROM
					c_cash_transaction c
					INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
					LEFT JOIN c_cash_operation c_op ON (c.c_cash_operation_id = c_op.c_cash_operation_id AND c_op.type = 0) 
					LEFT JOIN c_cash_mpayment c_mp ON (i.c_cash_mpayment_id = c_mp.c_cash_mpayment_id)
					LEFT JOIN c_bank c_ba ON (i.c_bank_id = c_ba.c_bank_id)
				WHERE
					c.ware_house = '" . pg_escape_string($warehouse_id) . "'
					AND c.d_system BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
					AND c.type = '0'
					AND c_op.c_cash_operation_id != '2' --VENTAS TICKETS
				ORDER BY
					documento desc 
			";
			$contenido['10_2_ingresos_cobranzas_amortizaciones_por_cc'] = SQLImplodeArray($sql10_2_ingresos_cobranzas_amortizaciones_por_cc);

			$sql12_egresos = "				
				SELECT
					i.pay_number || ' - ' || CASE WHEN pro.pro_rsocialbreve IS NULL THEN c.reference ELSE pro.pro_rsocialbreve END AS documento,
					(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS egresos
				FROM
					c_cash_transaction c
					INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					LEFT JOIN int_proveedores pro ON (pro.pro_codigo = c.bpartner)
				WHERE
					c.ware_house = '" . pg_escape_string($warehouse_id) . "'
					AND c.d_system BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
					AND i.c_bank_id = 0
					AND c.type = '1'
				ORDER BY
					documento desc
			";
			$contenido['12_egresos'] = SQLImplodeArray($sql12_egresos);

			$sql13_documento_venta_manual_total = "				
				SELECT 
					sum(nu_fac_valortotal) AS total
				FROM
					fac_ta_factura_cabecera 
				WHERE 
					ch_fac_tipodocumento IN ('10','35','20') AND 
					ch_almacen='" . pg_escape_string($warehouse_id) . "' AND 
					dt_fac_fecha BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "' 
			";
			$contenido['13_documento_venta_manual_total'] = SQLImplodeArray($sql13_documento_venta_manual_total);
			
			$sql13_documento_venta_manual_detalle = "				
				SELECT 
					TDOCU.tab_desc_breve||' - '||cab.ch_fac_seriedocumento||' - '||cab.ch_fac_numerodocumento||' - '||cab.cli_codigo||' '||cli.cli_rsocialbreve AS documento,
					cab.nu_fac_valortotal AS importe
				FROM
					fac_ta_factura_cabecera AS cab
					LEFT JOIN int_clientes AS cli
					USING(cli_codigo)
					LEFT JOIN int_tabla_general AS TDOCU
					ON(SUBSTRING(TDOCU.tab_elemento, 5) = cab.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
				WHERE 
					cab.ch_fac_tipodocumento IN ('10','35','20')
					AND cab.ch_almacen='" . pg_escape_string($warehouse_id) . "'
					AND cab.dt_fac_fecha BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
					--AND (cab.ch_liquidacion='' OR cab.ch_liquidacion IS NULL)
				ORDER BY 
					cab.ch_fac_tipodocumento,
					cab.ch_fac_seriedocumento,
					cab.ch_fac_numerodocumento,
					cab.cli_codigo
			";
			$contenido['13_documento_venta_manual_detalle'] = SQLImplodeArray($sql13_documento_venta_manual_detalle);			

			/*15. Saldo acumulado Caja y Banco*/
			$objModelCajaBanco = new CajaBancoModel();
			$resultado_ = $objModelCajaBanco->search($iAlmacen, $dYear, $dMonth, $pos_transYM);
			$result_ = listadoCajaBanco($resultado_, $iAlmacen, $dYear, $dMonth, $dDay);
			$saldo_acumulado_caja_banco = $result_;
			$contenido['15_saldo_acumulado_caja_banco'] = array(0 => $saldo_acumulado_caja_banco);
			/*Cerrar 15. Saldo acumulado Caja y Banco*/

			/*Inventario de Combustible*/
			$results1 = obtieneParte($desde, $hasta, $estaciones);
			$contenido['inventario_combustible'] = $results1;
			/*Cerrar Inventario de Combustible*/

			$data = json_encode($contenido);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		/**
		 * Para ejecutar en modo prueba:
		 * http://172.18.8.12/sistemaweb/centralizer_.php?mod=TOTALS_SALDO_SOCIO&from=20220223&to=20220223&warehouse_id=003&desde=23/02/2022&hasta=23/02/2022&socios=20100167892&vales=0&vista=RES
		 */
		case 'TOTALS_SALDO_SOCIO':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];

			/*Datos para query de cuentas por cobrar*/
			$fecha = $_REQUEST['hasta'];
			$socios = $_REQUEST['socios'];
			$vales = $_REQUEST['vales'];
			$vista = $_REQUEST['vista'];
			/*Cerrar*/

			/*Condicion de socios*/
			$where_socios = '';
			if(!empty($socios)) {
				$socios = explode("|", $socios);
									
				foreach ($socios as $key => $socio){
					$where_socios .= "'" . $socios[$key] . "',";
				}
				$where_socios_cuentas = 'AND cab.cli_codigo IN (' . substr($where_socios, 0, -1) . ')';
				$where_socios_vales = 'AND cli.cli_codigo IN (' . substr($where_socios, 0, -1) . ')';
			}			
			/*Cerrar*/

			error_log("TOTALS_SALDO_SOCIO");
			error_log( json_encode( $_REQUEST ) );
			// die();

			$sql_cuentas_por_cobrar = "
SELECT 
	CLIENTE,
	RAZONSOCIAL,
	TIPODOCUMENTO,
	SERIEDOCUMENTO,
	NUMDOCUMENTO,
	FIRST(MONEDA) AS MONEDA,
	FIRST(FECHAEMISION) AS FECHAEMISION,
	FIRST(FECHAVENCIMIENTO) AS FECHAVENCIMIENTO,
	FIRST(DOCUMENTO) AS DOCUMENTO,
	SUM(IMPORTEINICIAL_SOLES) AS IMPORTEINICIAL_SOLES,
	SUM(IMPORTEINICIAL_DOLARES) AS IMPORTEINICIAL_DOLARES,
	SUM(Nu_Importe_Pagos_Soles) AS PAGO_SOLES,
	SUM(Nu_Importe_Pagos_Dolares) AS PAGO_DOLARES,
	--SUM(saldo_soles) AS SALDO_SOLES, --COMENTADO POR QUE NO OBTENDRIA EL SALDO REAL
	--SUM(saldo_dolares) AS SALDO_DOLARES --COMENTADO POR QUE NO OBTENDRIA EL SALDO REAL
	SUM(IMPORTEINICIAL_SOLES) - SUM(Nu_Importe_Pagos_Soles) as SALDO_SOLES,	
	SUM(IMPORTEINICIAL_DOLARES) - SUM(Nu_Importe_Pagos_Dolares) as SALDO_DOLARES,
	FIRST(TIPOCAMBIO) AS TIPOCAMBIO
FROM (

	SELECT
		cab.cli_codigo AS CLIENTE,
		cli.cli_razsocial AS RAZONSOCIAL,
		cab.ch_tipdocumento AS TIPODOCUMENTO,
		cab.ch_seriedocumento AS SERIEDOCUMENTO,
		cab.ch_numdocumento AS NUMDOCUMENTO,
		mone.tab_desc_breve AS MONEDA,
		TO_CHAR(cab.dt_fechaemision, 'DD/MM/YYYY') AS FECHAEMISION,
		TO_CHAR(cab.dt_fechavencimiento, 'DD/MM/YYYY') AS FECHAVENCIMIENTO,
		gen.tab_desc_breve||' - '||trim(cab.ch_seriedocumento)||' - '||trim(cab.ch_numdocumento) as DOCUMENTO,
			
			(CASE WHEN det.ch_tipmovimiento = '1' AND det.ch_moneda = '01' THEN cab.nu_importetotal ELSE 0.00 END) AS IMPORTEINICIAL_SOLES, --Los anticipos deben ser considerados negativos?
			
			(CASE WHEN det.ch_tipmovimiento = '1' AND det.ch_moneda = '02' THEN cab.nu_importetotal ELSE 0.00 END) AS IMPORTEINICIAL_DOLARES, --Los anticipos deben ser considerados negativos?
			
			(CASE WHEN det.ch_tipmovimiento = '2' AND det.ch_moneda = '01' THEN nu_importemovimiento ELSE 0.00 END) AS Nu_Importe_Pagos_Soles,
			
			(CASE WHEN det.ch_tipmovimiento = '2' AND det.ch_moneda = '02' THEN nu_importemovimiento ELSE 0.00 END) AS Nu_Importe_Pagos_Dolares,

			(CASE WHEN --SI ES CANCELACION Y HAY MAS DE UN MOVIMIENTO EN DETALLE, ES DECIR SI HAY PAGOS
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) > 1
			THEN
				(SELECT --SUMA DE INCLUSION
					SUM(det2.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det2
				WHERE
					det2.ch_tipmovimiento 		= '1' --INCLUSION
					AND det2.ch_tipdocumento	= det.ch_tipdocumento
					AND det2.ch_seriedocumento	= det.ch_seriedocumento
					AND det2.ch_numdocumento	= det.ch_numdocumento
					AND det2.cli_codigo 		= det.cli_codigo
					AND det2.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				) - 
				(CASE WHEN --SI ES CANCELACION DE NUEVO???
					det.ch_tipmovimiento = '2'
				THEN
					(SELECT --SUMA DE CANCELACION
						SUM(det3.nu_importemovimiento)
					FROM
						ccob_ta_detalle as det3
					WHERE
						det3.ch_tipmovimiento = '2' --CANCELACION
						AND (CASE WHEN
								det3.ch_identidad = '2'
							THEN
								det3.ch_identidad = '2'
							ELSE
								det3.ch_identidad::INTEGER BETWEEN 2 AND det.ch_identidad::integer
							END) --NO TIENE SENTIDO
						AND det3.ch_tipdocumento	= det.ch_tipdocumento
						AND det3.ch_seriedocumento	= det.ch_seriedocumento
						AND det3.ch_numdocumento	= det.ch_numdocumento
						AND det3.cli_codigo 		= det.cli_codigo
						AND det3.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
					)
				ELSE
					0.00
				END)
			ELSE --SINO HAY PAGOS
				CASE WHEN --SI SOLO ESTA LA INCLUSION
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldo_soles,


			(CASE WHEN --SI ES CANCELACION Y HAY MAS DE UN MOVIMIENTO EN DETALLE, ES DECIR SI HAY PAGOS
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) > 1
			THEN
				(SELECT --SUMA DE INCLUSION
					SUM(det2.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det2
				WHERE
					det2.ch_tipmovimiento 		= '1' --INCLUSION
					AND det2.ch_tipdocumento	= det.ch_tipdocumento
					AND det2.ch_seriedocumento	= det.ch_seriedocumento
					AND det2.ch_numdocumento	= det.ch_numdocumento
					AND det2.cli_codigo 		= det.cli_codigo
					AND det2.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				) -
				(CASE WHEN --SI ES CANCELACION DE NUEVO???
					det.ch_tipmovimiento = '2'
				THEN
					(SELECT --SUMA DE CANCELACION
						SUM(det3.nu_importemovimiento)
					FROM
						ccob_ta_detalle as det3
					WHERE
						det3.ch_tipmovimiento = '2' --CANCELACION
						AND (CASE WHEN
								det3.ch_identidad = '2'
							THEN
								det3.ch_identidad = '2'
							ELSE
								det3.ch_identidad::INTEGER BETWEEN 2 AND det.ch_identidad::integer
							END) --NO TIENE SENTIDO
						AND det3.ch_tipdocumento	= det.ch_tipdocumento
						AND det3.ch_seriedocumento	= det.ch_seriedocumento
						AND det3.ch_numdocumento	= det.ch_numdocumento
						AND det3.cli_codigo 		= det.cli_codigo
						AND det3.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
					)
				ELSE
					0.00
				END)
			ELSE --SINO HAY PAGOS
				CASE WHEN --SI SOLO ESTA LA INCLUSION
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldo_dolares,

		cab.nu_tipocambio AS TIPOCAMBIO --Pueden haber muchos tipos de cambio en ccob_ta_detalle, solo mostramos el de ccbo_ta_cabecera

	FROM
		ccob_ta_cabecera cab
		JOIN ccob_ta_detalle        AS det  ON(cab.cli_codigo = det.cli_codigo AND cab.ch_tipdocumento = det.ch_tipdocumento AND cab.ch_seriedocumento = det.ch_seriedocumento AND cab.ch_numdocumento = det.ch_numdocumento)
		JOIN int_clientes           AS cli  ON(cab.cli_codigo = cli.cli_codigo)
		LEFT JOIN int_tabla_general AS mone ON(det.ch_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla='04' AND mone.tab_elemento != '000000')
		LEFT JOIN int_tabla_general AS gen  ON(cab.ch_tipdocumento = substring(trim(gen.tab_elemento) for 2 from length(trim(gen.tab_elemento))-1) AND gen.tab_tabla ='08' AND gen.tab_elemento != '000000')
		LEFT JOIN (SELECT
						ch_tipdocumento,
						ch_seriedocumento,
						ch_numdocumento,
						cli_codigo,
						nu_importemovimiento AS Nu_Importe_Movimiento_Detalle_Inclusion
					FROM
						ccob_ta_detalle	
					WHERE
						ch_tipdocumento IN ('10','11','20','21','22')
						AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
						AND ch_tipmovimiento = '1'
					) AS CDINCLUSION ON (cab.ch_tipdocumento = CDINCLUSION.ch_tipdocumento and cab.ch_seriedocumento = CDINCLUSION.ch_seriedocumento and cab.ch_numdocumento = CDINCLUSION.ch_numdocumento AND cab.cli_codigo = CDINCLUSION.cli_codigo)
		LEFT JOIN (SELECT
						ch_tipdocumento,
						ch_seriedocumento,
						ch_numdocumento,
						cli_codigo,
						SUM(nu_importemovimiento) AS Nu_Importe_Movimiento_Detalle_Cancelacion
					FROM
						ccob_ta_detalle	
					WHERE
						1 = 1
						--AND ch_tipdocumento IN ('10','11','20','21','22')
						AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
						AND ch_tipmovimiento >= '2'
					GROUP BY
						1,2,3,4
					) AS CDCANCELACION ON (cab.ch_tipdocumento = CDCANCELACION.ch_tipdocumento and cab.ch_seriedocumento = CDCANCELACION.ch_seriedocumento and cab.ch_numdocumento = CDCANCELACION.ch_numdocumento AND cab.cli_codigo = CDCANCELACION.cli_codigo)
	WHERE
		cab.ch_tipdocumento IN ('10','11','20','21','22')
		AND det.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
		AND COALESCE(CDINCLUSION.Nu_Importe_Movimiento_Detalle_Inclusion, 0) > COALESCE(CDCANCELACION.Nu_Importe_Movimiento_Detalle_Cancelacion, 0)
		
		$where_socios_cuentas
	ORDER BY
		1,
		6 desc,
		3,
		4,
		5,
		det.ch_tipmovimiento,
		det.dt_fechamovimiento
) CUENTA_CORRIENTE

GROUP BY
	CLIENTE,
	RAZONSOCIAL,
	TIPODOCUMENTO,
	SERIEDOCUMENTO,
	NUMDOCUMENTO
ORDER BY	
	CLIENTE,
	MONEDA DESC,
	TIPODOCUMENTO,
	SERIEDOCUMENTO,
	NUMDOCUMENTO;
			";
			error_log($sql_cuentas_por_cobrar);
			$contenido['1_cuentas_por_cobrar'] = SQLImplodeArray($sql_cuentas_por_cobrar);

			$sql_vales ="
SELECT DISTINCT
	cli.cli_codigo as CLIENTE,
	cli.cli_razsocial AS RAZONSOCIAL,
	cli.cli_codigo||' - '||TRIM(cli.cli_razsocial)||' TIPO: '||COALESCE(cli.cli_tipo,'IN')||' LIMITE: '||COALESCE(cli.cli_creditosol,0) as grupo,	
	'Vale: '||valc.ch_documento as documentoval,
	valc.nu_importe as importeval,
	valc.dt_fecha as fecha
FROM
	val_ta_cabecera as valc
	INNER JOIN
		int_clientes as cli ON(valc.ch_cliente = cli.cli_codigo)
WHERE
	valc.dt_fecha <= TO_DATE('$fecha','DD/MM/YYYY')
	AND valc.ch_liquidacion IS NULL
	$where_socios_vales
ORDER BY
	CLIENTE;
			";
			$contenido['2_vales'] = SQLImplodeArray($sql_vales);

			$data = json_encode($contenido);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		case 'TOTALS_SOBRANTES_FALTANTES':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];
			$productos = $_REQUEST['productos'];
			$arrayProductos = explode('|', $productos);
			$unidadmedida = $_REQUEST['unidadmedida'];

			/*Datos para queries Sobrantes y Faltantes*/
			$fecha_desde_explode = explode("/", $desde);
			$fecha_hasta_explode = explode("/", $hasta);

			$almacen = $warehouse_id;
			$fechad  = $fecha_desde_explode['0'] . "-" . $fecha_desde_explode['1'] . "-" . $fecha_desde_explode['2'];
			$fechaa  = $fecha_hasta_explode['0'] . "-" . $fecha_hasta_explode['1'] . "-" . $fecha_hasta_explode['2'];
			/*Cerrar*/

			global $sqlca;

			// RECORREMOS PRODUCTOS
			foreach ($arrayProductos as $key => $producto) {	
				// OBTENEMOS CODIGO DE COMBUSTIBLE
				$cod_combustible = $producto;

				// OBTENEMOS CODIGO Y NOMBRE DE COMBUSTIBLE
				$sqlca->query("SELECT 
									c.ch_nombrecombustible,
									c.ch_codigocombustible
								FROM
									comb_ta_combustibles c
								WHERE
									c.ch_codigocombustible = trim('$cod_combustible')");

				if ($sqlca->numrows() > 0) { //Si selecciona un combustible especifico
					$C               = $sqlca->fetchRow();
					$nom_combustible = $C[1] . " -- " . $C[0];
				}
				
				// CONVERSION DE GLP
				if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Litros_a_Galones'){
					$factor=3.785411784;
					$operacion='/';
				}else if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Galones_a_Litros'){
					$factor=3.785411784;
					$operacion='*';
				}else{
					$factor=1; //Por defecto todo esta en litros, mencionar que segun lo indicado en OPENSOFT-93, por defecto la unidad de medida de GLP vendra en Galones, ya no en Litros
					$operacion='/';
				}

				//FECHA
				$qf = "SELECT 
							to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
							SUM(nu_medicion) AS saldo,
							to_char(dt_fechamedicion- interval '1 day','DD-MM-YYYY') AS fecha2
						FROM 
							comb_ta_mediciondiaria med
							INNER JOIN comb_ta_tanques tan ON (med.ch_tanque = tan.ch_tanque)
						WHERE 
							med.ch_sucursal=trim('$almacen')
							AND tan.ch_sucursal=trim('$almacen')
							AND tan.ch_codigocombustible = '$cod_combustible'
							AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
							AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
						GROUP BY 
							dt_fechamedicion,
							fecha2 
						ORDER BY 
							dt_fechamedicion";

				$sqlca->query($qf);

				if($sqlca->numrows()>0){
					//FECHA
					$A = array();
					$rep = array();
					$fec = array();
					$fec_saldo = array();
					
					//SALDO
					$A = array();
					$Fe = array();
					$Saldo = array();

					//COMPRA
					$A = array();
					$F2 = array();
					$COMPRA = array();

					//MEDICION O AFERICION
					$A = array();
					$F3 = array();
					$AFE = array();

					//VENTA
					$A = array();
					$F4 = array();
					$VENTA = array();
					$PRECIO_VENTA = array();

					//INGRESO
					$A = array();
					$F5 = array();
					$ING = array();

					//SALIDA
					$A = array();
					$F6 = array();
					$SAL = array();

					//PARTE
					$rep = array();

					// VARILLA
					$A = array();
					$FE8 = array();
					$VARI = array();

					//DIARIA
					$rep = array();

					//ACUMULADA
					$rep = array();

					for($i=0;$i<$sqlca->numrows();$i++){
						$A = $sqlca->fetchRow();
					
						$rep[$i][0] = $A[0];
						$fec[$i] = $A[0];
						$fec_saldo[$i] = $A[2];
					}

					//SALDO
					$qe =  "SELECT 
								to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
								ROUND(SUM(nu_medicion) $operacion '$factor',3) AS saldo
							FROM	
								comb_ta_mediciondiaria med
								INNER JOIN comb_ta_tanques tan ON (med.ch_tanque = tan.ch_tanque)
							WHERE 
								med.ch_sucursal=trim('$almacen')
								AND tan.ch_sucursal=trim('$almacen')
								AND dt_fechamedicion >= to_date('$fec_saldo[0]','dd-mm-yyyy')
								AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
								AND tan.ch_codigocombustible = '$cod_combustible'
							GROUP BY 
								dt_fechamedicion 
							ORDER BY 
								dt_fechamedicion";

					$sqlca->query($qe);

					for($i=0;$i<$sqlca->numrows();$i++){
						$A = $sqlca->fetchRow();
						$Fe[$i] = $A[0];
						$Saldo[$i] = $A[1];
					}
				
					for($i=0;$i<count($fec_saldo);$i++){
						$rep[$i][1] = "0.000";
						for($a=0;$a<count($Fe);$a++){
							if($Fe[$a]==$fec_saldo[$i]){  
								$rep[$i][1] = $Saldo[$a];
							}
						}
					}

					//COMPRA
					$limit = count($fec)-1;
					$sqlca->query("SELECT 
										to_char(mov_fecha::DATE,'DD-MM-YYYY') AS fecha,
										ROUND(SUM(mov_cantidad) $operacion '$factor',3) AS compra
									FROM
										inv_movialma mov 
									WHERE 
										tran_codigo	= '21'
										AND mov_almacen	= trim('$almacen')
										AND art_codigo	= '$cod_combustible'
										AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','dd-mm-yyyy')
										AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','dd-mm-yyyy')
									GROUP BY 
										mov_fecha::DATE");

					for($a=0;$a<$sqlca->numrows();$a++){
						$A = $sqlca->fetchRow();
						$F2[$a] = $A[0];
						$COMPRA[$a] = $A[1];
					}
			
					for($i=0;$i<count($fec);$i++){
						$rep[$i][2] = "0.000";
						for($b=0;$b<count($F2);$b++)
						if($F2[$b]==$fec[$i]){  $rep[$i][2] = $COMPRA[$b];  }
					}

					//MEDICION O AFERICION
					$sqlca->query("
								SELECT
									TO_CHAR(a.dia, 'DD-MM-YYYY') AS fecha,
									ROUND(SUM(a.cantidad) $operacion '$factor',3) AS medicion
								FROM
									pos_ta_afericiones a
									LEFT JOIN comb_ta_tanques t ON(t.ch_codigocombustible = a.codigo AND t.ch_sucursal = a.es)
								WHERE
									a.es = trim('$almacen')
									AND t.ch_sucursal = trim('$almacen')
									AND a.dia BETWEEN to_date('$fechad','dd-mm-yyyy') AND to_date('$fechaa','dd-mm-yyyy')
									AND t.ch_codigocombustible = '$cod_combustible'
								GROUP BY
									a.dia
								ORDER BY
									a.dia
							");

					for($a=0;$a<$sqlca->numrows();$a++){
						$A = $sqlca->fetchRow();
						$F3[$a] = $A[0];
						$AFE[$a] = $A[1];
					}

					for($i=0;$i<count($fec);$i++){
						$rep[$i][3] = "0.000";
						for($b=0;$b<count($F3);$b++)
						if($F3[$b]==$fec[$i]){  $rep[$i][3] = $AFE[$b];  }
					}

					//VENTA
					$sqlca->query("SELECT 
									to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha,
									ROUND(SUM(cont.nu_ventagalon) $operacion '$factor',3) AS venta,
									CASE 
									WHEN SUM(cont.nu_ventagalon) = 0 THEN 0.00
									ELSE
									ROUND((COALESCE(SUM(cont.nu_ventavalor),0) / COALESCE(SUM(cont.nu_ventagalon),1)) , 2) 
									END AS nu_precio_venta
								FROM 
									comb_ta_contometros cont
								WHERE 
									cont.ch_sucursal=trim('$almacen')
									AND dt_fechaparte >= to_date('$fechad','dd-mm-yyyy')
									AND dt_fechaparte <= to_date('$fechaa','dd-mm-yyyy')
									AND cont.ch_codigocombustible = '$cod_combustible'
								GROUP BY 
									dt_fechaparte");

					for($a=0;$a<$sqlca->numrows();$a++){
						$A = $sqlca->fetchRow();
						$F4[$a]             = $A[0];
						$VENTA[$a]          = $A[1];
						$PRECIO_VENTA[$a]   = $A[2];
					}

					for($i = 0; $i < count($fec); $i++){

						$rep[$i][4] = "0.000";

						for($b = 0; $b < count($F4); $b++){
							if($F4[$b]==$fec[$i]){
								$rep[$i][4] = $VENTA[$b];
								$rep[$i][12] = $PRECIO_VENTA[$b];
							}
						}

					}

					//INGRESO
					$sqlca->query("SELECT 
										to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
										ROUND(SUM(mov_cantidad) $operacion '$factor',3) AS compra
									FROM 
										inv_movialma
									WHERE 
										mov_almacen=trim('$almacen')
										AND art_codigo='$cod_combustible'
										AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
										AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
										AND tran_codigo='27' 
									GROUP BY 
										mov_fecha::date");

					for($a=0;$a<$sqlca->numrows();$a++){
						$A = $sqlca->fetchRow();
						$F5[$a] = $A[0];
						$ING[$a] = $A[1];
					}

					for($i=0;$i<count($fec);$i++){
						$rep[$i][5] = "0.000";
						for($b=0;$b<count($F5);$b++)
						if($F5[$b]==$fec[$i]){  $rep[$i][5] = $ING[$b];  }
					}

					//SALIDA
					$sqlca->query("SELECT 
										to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
										ROUND(SUM(mov_cantidad) $operacion '$factor',3) AS compra
									FROM 
										inv_movialma
									WHERE 
										mov_almacen=trim('$almacen')
										AND art_codigo='$cod_combustible'
										AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
										AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
										AND tran_codigo='28' 
									GROUP BY 
										mov_fecha::date");

					for($a=0;$a<$sqlca->numrows();$a++){
						$A = $sqlca->fetchRow();
						$F6[$a] = $A[0];
						$SAL[$a] = $A[1];
					}

					for($i=0;$i<count($fec);$i++){
						$rep[$i][6] = "0.000";
						for($b=0;$b<count($F6);$b++)
						if($F6[$b]==$fec[$i]){  $rep[$i][6] = $SAL[$b];  }
					}

					//PARTE
					for($i=0;$i<count($fec);$i++){
						$rep[$i][7] = $rep[$i][1]+$rep[$i][2]+$rep[$i][3]-$rep[$i][4]+$rep[$i][5]-$rep[$i][6];
					}

					//VARILLA
					$sqlca->query("SELECT 
										to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
										ROUND(SUM(nu_medicion) $operacion '$factor',3) AS saldo
									FROM 
										comb_ta_mediciondiaria med
										INNER JOIN comb_ta_tanques tan ON (med.ch_tanque = tan.ch_tanque)
									WHERE 
										med.ch_sucursal=trim('$almacen')
										AND tan.ch_sucursal=trim('$almacen')
										AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
										AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
										AND tan.ch_codigocombustible = '$cod_combustible'
									GROUP BY 
										dt_fechamedicion 
									ORDER BY dt_fechamedicion");
					
					for($i=0;$i<$sqlca->numrows();$i++){
						$A = $sqlca->fetchRow();
						$FE8[$i] = $A[0];
						$VARI[$i] = $A[1];
					}
				
					for($i=0;$i<count($fec);$i++){
						$rep[$i][8] = "0.000";
						for($a=0;$a<count($FE8);$a++){
							if($FE8[$a]==$fec[$i]){  $rep[$i][8] = $VARI[$a];  }
						}
					}

					//DIARIA
					for($i=0;$i<count($fec);$i++){
						$rep[$i][9] = $rep[$i][8]-$rep[$i][7];
					}
				
					//ACUMULADA
					for($i=0;$i<count($fec);$i++){
					
						$rep[$i][10] = $rep[$i][9]+$rep[$i-1][10];
					
					}

					$contenido[$nom_combustible] = $rep;
				}
			}
		
			$data = json_encode($contenido);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		/**
		 * Para ejecutar en modo prueba:
		 * http://172.18.8.12/sistemaweb/centralizer_.php?mod=VALES_CLIENTES_CREDITO&from=20200801&to=20200831&warehouse_id=003&desde=01/08/2020&hasta=31/08/2020&clientes=123456787|123456788|123456789
		 */
		case 'VALES_CLIENTES_CREDITO':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];

			//Datos para obtener tabla pos_trans
			$fecha_explode = explode("/", $hasta);
			$dYear       = $fecha_explode['2'];
			$dMonth      = $fecha_explode['1'];
			$dDay        = $fecha_explode['0'];
			$pos_transYM = "pos_trans" . $dYear . $dMonth;

			//Desde
			$porciones_desde = explode("/", $desde);
			$desde_ = $porciones_desde = $porciones_desde[2] . "-" . $porciones_desde[1] . "-" . $porciones_desde[0];

			//Hasta
			$porciones_hasta = explode("/", $hasta);
			$hasta_ = $porciones_desde = $porciones_hasta[2] . "-" . $porciones_hasta[1] . "-" . $porciones_hasta[0];

			//RUCs
			$clientes = $_REQUEST['clientes'];
			$clientes = explode("|", $clientes);
			
			$clientes_ = '';			
			foreach ($clientes as $key => $cliente){
				$clientes_ .= "'" . $clientes[$key] . "',";
			}
			$clientes_ = substr($clientes_, 0, -1);

			$sql_vales_clientes_credito = "
			SELECT
				TO_CHAR(cab.dt_fecha, 'DD/MM/YYYY') AS fecha,  
				(  SELECT 
						TO_CHAR(PT.fecha, 'HH24:MI:SS') 
					FROM 
						".$pos_transYM." PT 
					WHERE 
						PT.caja||'-'||PT.trans = cab.ch_documento 
						OR PT.trans::VARCHAR = cab.ch_documento 
					LIMIT 1 ) AS hora, 
				TO_CHAR(cab.fecha_replicacion, 'HH24:MI:SS') AS hora_replicacion,
				'ESTACION' AS estacion,
				cab.ch_documento AS numero,
				cab.ch_placa AS placa,
				cab.nu_odometro AS odometro,
				pf.nomusu AS chofer,
				art.art_codigo AS codigo,
				art.art_descbreve AS producto,
				det.nu_cantidad AS cantidad,
				det.nu_importe AS importe,
				CASE
					when cli.cli_anticipo='S' then fac.cod_hermandad 
					else
					fac.ch_fac_seriedocumento||'-'||fac.ch_fac_numerodocumento
				end AS documento, --AQUI OBTIENE EL CAMPO #FACTURA
				cab.ch_cliente codcliente,
 				cli.cli_razsocial nomcliente
			FROM
				val_ta_cabecera AS cab
				LEFT JOIN val_ta_detalle AS det
				 ON(cab.ch_sucursal = det.ch_sucursal AND cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha)
				LEFT JOIN val_ta_complemento AS com --Numero de vale
				 ON(cab.ch_sucursal = com.ch_sucursal AND cab.ch_documento = com.ch_documento AND cab.dt_fecha = com.dt_fecha)
				LEFT JOIN val_ta_complemento_documento AS fac
				 ON(fac.art_codigo = det.ch_articulo AND fac.ch_numeval = cab.ch_documento AND fac.ch_cliente = cab.ch_cliente AND cab.ch_sucursal = cab.ch_sucursal AND fac.dt_fecha = cab.dt_fecha)
				LEFT JOIN fac_ta_factura_cabecera AS fac2
				 ON(fac2.ch_liquidacion = cab.ch_liquidacion)
				LEFT JOIN pos_fptshe1 AS pf
				 ON(pf.numtar = cab.ch_tarjeta)
				LEFT JOIN inv_ta_almacenes AS alma
				 ON(cab.ch_sucursal = alma.ch_almacen)
				LEFT JOIN int_clientes AS cli
				 ON(cli.cli_codigo = cab.ch_cliente)
				LEFT JOIN int_articulos AS art
				 ON(art.art_codigo = det.ch_articulo)
			WHERE
				cab.dt_fecha BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
				AND TRIM(cab.ch_cliente) IN ($clientes_)
			ORDER BY
				cab.ch_cliente,
				cab.ch_sucursal,
				cab.dt_fecha DESC,
				hora DESC;
			";
			error_log('VALES_CLIENTES_CREDITO');
			error_log($sql_vales_clientes_credito);
			$contenido['vales_clientes_credito'] = SQLImplodeArray($sql_vales_clientes_credito);

			$data = json_encode($contenido);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		/**
		 * Para ejecutar en modo prueba:
		 * http://172.18.8.12/sistemaweb/centralizer_.php?mod=COMPROBANTES_COBRANZA&from=20200801&to=20200831&warehouse_id=003&desde=01/08/2020&hasta=31/08/2020&ruc=-&state=0
		 */
		case 'COMPROBANTES_COBRANZA':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];

			//Datos para obtener tabla pos_trans
			$fecha_explode = explode("/", $hasta);
			$dYear       = $fecha_explode['2'];
			$dMonth      = $fecha_explode['1'];
			$dDay        = $fecha_explode['0'];
			$pos_transYM = "pos_trans" . $dYear . $dMonth;

			//Desde
			$porciones_desde = explode("/", $desde);
			$desde_ = $porciones_desde = $porciones_desde[2] . "-" . $porciones_desde[1] . "-" . $porciones_desde[0];

			//Hasta
			$porciones_hasta = explode("/", $hasta);
			$hasta_ = $porciones_desde = $porciones_hasta[2] . "-" . $porciones_hasta[1] . "-" . $porciones_hasta[0];

			//RUC
			$cli_codigo = $_REQUEST['ruc'];

			//State
			$state = $_REQUEST['state'];

			/**
			 * Webflotas:
			 * Pendiente - 0
			 * Cancelado - 1
			 * Todos - 2
			 * 
			 * Reglas state:
			 * Pendientes (Saldo <>0)
			 * Cancelados (Saldo=0)
			 * Todos (ambos)
			 */
			$where_state = "";
			if($state == 0){
				$where_state = "AND nu_importesaldo <> 0";
			}else if($state == 1){
				$where_state = "AND nu_importesaldo = 0";
			}else if($state == 2){
				$where_state = "AND (nu_importesaldo <> 0 OR nu_importesaldo = 0)";
			}

			$sql_comprobantes_cobranza = "
			SELECT  
				--ch_tipdocumento,
				-- CASE 
					-- WHEN ch_tipdocumento = '10' THEN '10'
					-- WHEN ch_tipdocumento = '20' THEN '20'
					-- WHEN ch_tipdocumento = '21' THEN '21'
					-- WHEN ch_tipdocumento = '22' THEN '22'			
				-- END as ch_tipdocumento,
				gen.tab_descripcion AS ch_tipdocumento,
				cab.ch_seriedocumento,
				cab.ch_numdocumento,
				cab.cli_codigo,
				cab.dt_fechaemision,
				cab.nu_dias_vencimiento,
				cab.dt_fechavencimiento,
				--ch_moneda,
				(CASE WHEN cab.ch_moneda = '01' THEN 'S/' ELSE '$' END) as ch_moneda,
				cab.nu_importetotal,
				(cab.nu_importetotal - cab.nu_importesaldo) AS nu_importepagos,
				cab.nu_importesaldo,
				cab.dt_fechasaldo
			FROM 
				ccob_ta_cabecera AS cab
				LEFT JOIN int_tabla_general AS gen ON(cab.ch_tipdocumento = substring(trim(gen.tab_elemento) for 2 from length(trim(gen.tab_elemento))-1) AND gen.tab_tabla ='08' AND gen.tab_elemento != '000000')
			WHERE
				cab.dt_fechaemision BETWEEN '" . pg_escape_string($desde_) . "' AND '" . pg_escape_string($hasta_) . "'
				AND TRIM(cab.cli_codigo) = '" . TRIM($cli_codigo) . "'
				$where_state
			ORDER BY
				cab.cli_codigo,
				cab.ch_sucursal,
				cab.dt_fechaemision DESC;
			";
			error_log('COMPROBANTES_COBRANZA');
			error_log($sql_comprobantes_cobranza);
			$contenido['comprobantes_cobranza'] = SQLImplodeArray($sql_comprobantes_cobranza);

			$data = json_encode($contenido);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		case 'TOTALS_STATISTICS_SALE':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$sql = "SELECT
			_result._type,
			_result.codigo,
			_result.descripcion,
			round(_result.neto_cantidad, 2) AS neto_cantidad,
			round(_result.neto_soles, 2) AS neto_venta,
			round(_result.importe_ci, 2) AS importe_ci,
			round(_result.cantidad_ci, 2) AS cantidad_ci FROM (
			SELECT
							'anterior' as _type,
							C .codigo AS codigo,--0
							COMB.descripcion AS descripcion,--1
							(
								CASE
								WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
									(COMB.total_cantidad - COMB.af_cantidad)
								WHEN AFC.af_cantidad > 0 THEN
									(COMB.total_cantidad - AFC.af_cantidad)
								WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
									(COMB.total_cantidad)
								END
							) AS neto_cantidad,--9
							(
								CASE
								WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
									((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
								WHEN AFC.af_total > 0 THEN
									((COMB.total_venta + COMB.descuentos) - AFC.af_total)
								WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
									(COMB.total_venta + COMB.descuentos)
								END
							) AS neto_soles--10
							, gasto_interno.nu_importe as importe_ci
							, gasto_interno.nu_cantidad as cantidad_ci
						FROM
							(
								SELECT
									ch_codigocombustible AS codigo
								FROM
									comb_ta_tanques
								WHERE ch_sucursal = '$warehouse_id'
							) C
						INNER JOIN (
							SELECT
								comb.ch_codigocombustible AS codigo,
								cmb.ch_nombrecombustible AS descripcion,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventavalor
									ELSE
										0
									END
								) AS total_venta,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventagalon
									ELSE
										0
									END
								) AS total_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										(comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_soles,
								ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
								nu_factor_igv AS igv
							FROM
								comb_ta_contometros comb
							LEFT JOIN comb_ta_combustibles cmb ON (
								comb.ch_codigocombustible = cmb.ch_codigocombustible
							)
							WHERE
								comb.dt_fechaparte BETWEEN '$_BeginDate' AND '$_EndDate'
							AND comb.ch_sucursal = TRIM ('$warehouse_id')
							GROUP BY
								comb.ch_codigocombustible,
								cmb.ch_nombrecombustible,
								comb.nu_factor_igv
						) COMB ON COMB.codigo = C .codigo
						LEFT JOIN (
							SELECT
								af.codigo AS codigo,
								SUM (af.importe) AS af_total,
								ROUND(SUM(af.cantidad), 3) AS af_cantidad
							FROM
								pos_ta_afericiones af
							WHERE
								af.dia BETWEEN '$_BeginDate' AND '$_EndDate'
							AND af.es = TRIM ('$warehouse_id')
							GROUP BY
								af.codigo
						) AFC ON AFC.codigo = C .codigo
						LEFT JOIN (
							SELECT
								detalle.ch_articulo, SUM(detalle.nu_importe) AS nu_importe, SUM(detalle.nu_cantidad) AS nu_cantidad
							FROM
								val_ta_cabecera cabecera
							JOIN int_clientes clientes ON (
								cabecera.ch_cliente = clientes.cli_ruc
							)
							JOIN val_ta_detalle detalle ON (
								cabecera.ch_documento = detalle.ch_documento AND cabecera.ch_sucursal = detalle.ch_sucursal AND cabecera.dt_fecha = detalle.dt_fecha
							)
							JOIN int_ta_sucursales sucursales ON (
								clientes.cli_ruc = sucursales.ruc
							)
							WHERE
							sucursales.ch_sucursal = TRIM('$warehouse_id') AND
							detalle.dt_fecha BETWEEN '$_BeginDate' AND '$_EndDate'
							--AND detalle.ch_articulo = TRIM('11620304')
							GROUP BY detalle.ch_articulo
						) gasto_interno ON (C.codigo = gasto_interno.ch_articulo)
						
						UNION ALL
						SELECT
						'anterior' as _type,
						'11620308' AS codigo,
						'GNV' AS descripcion,
						SUM(tot_cantidad) AS neto_cantidad,
						SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
							tot_surtidor_soles
						ELSE
							tot_surtidor_soles - tot_afericion
						END)
						AS neto_soles
						, SUM(0) AS importe_ci
						, SUM(0) AS cantidad_ci
						FROM comb_liquidaciongnv
						WHERE ch_almacen = '$warehouse_id'
						AND dt_fecha BETWEEN '$_BeginDate' AND '$_EndDate'

						UNION ALL

						SELECT
							'anterior' as _type,
							'MARKET' AS codigo,
							'MKT' AS descripcion,
							SUM(MOVIALMA.mov_cantidad) AS neto_cantidad,
							SUM(PT.importe) AS neto_soles
							, SUM(0) AS importe_ci
							, SUM(0) AS cantidad_ci
						FROM
							inv_movialma AS MOVIALMA
							JOIN int_articulos AS ART ON (MOVIALMA.art_codigo = ART.art_codigo)
							LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
							JOIN (
								SELECT
									es,
									dia,
									PT.codigo,
									SUM(importe) AS importe
								FROM
									pos_trans$_BeginYear$_BeginMonth PT
									JOIN int_articulos AS ART ON (PT.codigo = ART.art_codigo)
									LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
								WHERE
									--PT.es       = '$warehouse_id' AND
									ART.art_plutipo   = '1'
									AND ART.art_unidad NOT IN('000GLN', '0000GL')
									AND PT.dia::DATE BETWEEN '$_BeginDate' AND '$_EndDate'
								GROUP BY
									1,
									2,
									3
							) AS PT ON (PT.es = MOVIALMA.mov_almacen AND MOVIALMA.mov_fecha::DATE = PT.dia AND PT.codigo = MOVIALMA.art_codigo)
						WHERE
							--MOVIALMA.mov_almacen             = '$warehouse_id' AND
							MOVIALMA.tran_codigo         = '45'
							AND ART.art_plutipo              = '1'
							AND ART.art_unidad NOT IN('000GLN', '0000GL')
							AND MOVIALMA.mov_fecha BETWEEN '$_BeginDate' AND '$_EndDate'


			UNION
			SELECT
							'actual' as _type,
							C .codigo AS codigo,--0
							COMB.descripcion AS descripcion,--1
							(
								CASE
								WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
									(COMB.total_cantidad - COMB.af_cantidad)
								WHEN AFC.af_cantidad > 0 THEN
									(COMB.total_cantidad - AFC.af_cantidad)
								WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
									(COMB.total_cantidad)
								END
							) AS neto_cantidad,--9
							(
								CASE
								WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
									((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
								WHEN AFC.af_total > 0 THEN
									((COMB.total_venta + COMB.descuentos) - AFC.af_total)
								WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
									(COMB.total_venta + COMB.descuentos)
								END
							) AS neto_soles--10
							, gasto_interno.nu_importe as importe_ci
							, gasto_interno.nu_cantidad as cantidad_ci
						FROM
							(
								SELECT
									ch_codigocombustible AS codigo
								FROM
									comb_ta_tanques
								WHERE ch_sucursal = '$warehouse_id'
							) C
						INNER JOIN (
							SELECT
								comb.ch_codigocombustible AS codigo,
								cmb.ch_nombrecombustible AS descripcion,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventavalor
									ELSE
										0
									END
								) AS total_venta,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventagalon
									ELSE
										0
									END
								) AS total_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										(comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_soles,
								ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
								nu_factor_igv AS igv
							FROM
								comb_ta_contometros comb
							LEFT JOIN comb_ta_combustibles cmb ON (
								comb.ch_codigocombustible = cmb.ch_codigocombustible
							)
							WHERE
								comb.dt_fechaparte BETWEEN '$BeginDate' AND '$EndDate'
							AND comb.ch_sucursal = TRIM ('$warehouse_id')
							GROUP BY
								comb.ch_codigocombustible,
								cmb.ch_nombrecombustible,
								comb.nu_factor_igv
						) COMB ON COMB.codigo = C .codigo
						LEFT JOIN (
							SELECT
								af.codigo AS codigo,
								SUM (af.importe) AS af_total,
								ROUND(SUM(af.cantidad), 3) AS af_cantidad
							FROM
								pos_ta_afericiones af
							WHERE
								af.dia BETWEEN '$BeginDate' AND '$EndDate'
							AND af.es = TRIM ('$warehouse_id')
							GROUP BY
								af.codigo
						) AFC ON AFC.codigo = C .codigo
						LEFT JOIN (
							SELECT
								detalle.ch_articulo, SUM(detalle.nu_importe) AS nu_importe, SUM(detalle.nu_cantidad) AS nu_cantidad
							FROM
								val_ta_cabecera cabecera
							JOIN int_clientes clientes ON (
								cabecera.ch_cliente = clientes.cli_ruc
							)
							JOIN val_ta_detalle detalle ON (
								cabecera.ch_documento = detalle.ch_documento AND cabecera.ch_sucursal = detalle.ch_sucursal AND cabecera.dt_fecha = detalle.dt_fecha
							)
							JOIN int_ta_sucursales sucursales ON (
								clientes.cli_ruc = sucursales.ruc
							)
							WHERE
							sucursales.ch_sucursal = TRIM('$warehouse_id') AND
							detalle.dt_fecha BETWEEN '$BeginDate' AND '$EndDate'
							--AND detalle.ch_articulo = TRIM('11620304')
							GROUP BY detalle.ch_articulo
						) gasto_interno ON (C.codigo = gasto_interno.ch_articulo)

						UNION ALL
						SELECT
						'actual' as _type,
						'11620308' AS codigo,
						'GNV' AS descripcion,
						SUM(tot_cantidad) AS neto_cantidad,
						SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
							tot_surtidor_soles
						ELSE
							tot_surtidor_soles - tot_afericion
						END)
						AS neto_soles
						, SUM(0) AS importe_ci
						, SUM(0) AS cantidad_ci
						FROM comb_liquidaciongnv
						WHERE ch_almacen = '$warehouse_id'
						AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate'

						UNION ALL

						SELECT
							'actual' as _type,
							'MARKET' AS codigo,
							'MKT' AS descripcion,
							SUM(MOVIALMA.mov_cantidad) AS neto_cantidad,
							SUM(PT.importe) AS neto_soles
							, SUM(0) AS importe_ci
							, SUM(0) AS cantidad_ci
						FROM
							inv_movialma AS MOVIALMA
							JOIN int_articulos AS ART ON (MOVIALMA.art_codigo = ART.art_codigo)
							LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
							JOIN (
								SELECT
									es,
									dia,
									PT.codigo,
									SUM(importe) AS importe
								FROM
									pos_trans$BeginYear$BeginMonth PT
									JOIN int_articulos AS ART ON (PT.codigo = ART.art_codigo)
									LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
								WHERE
									--PT.es       = '$warehouse_id' AND
									ART.art_plutipo   = '1'
									AND ART.art_unidad NOT IN('000GLN', '0000GL')
									AND PT.dia::DATE BETWEEN '$BeginDate' AND '$EndDate'
								GROUP BY
									1,
									2,
									3
							) AS PT ON (PT.es = MOVIALMA.mov_almacen AND MOVIALMA.mov_fecha::DATE = PT.dia AND PT.codigo = MOVIALMA.art_codigo)
						WHERE
							--MOVIALMA.mov_almacen             = '$warehouse_id' AND
							MOVIALMA.tran_codigo         = '45'
							AND ART.art_plutipo              = '1'
							AND ART.art_unidad NOT IN('000GLN', '0000GL')
							AND MOVIALMA.mov_fecha BETWEEN '$BeginDate' AND '$EndDate'

			)  as _result ORDER BY _result.codigo ASC, _result._type ASC
			;";

			//SQLImplode($sql);
			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'TOTALS_STATISTICS_SALE_':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			/*
			$PosTransTable
			$_PosTransTable
			*/
			$sql = "SELECT * FROM 
				( SELECT 
						t.es, 
						'ANTERIOR'::text, 
						trim(t.codigo), 
						sum(t.cantidad) 
					FROM 
						$_PosTransTable t 
					WHERE 
						t.tm='V' 
						AND t.tipo='C'  and grupo!='D'
						AND t.dia BETWEEN '$_BeginDate' AND $_EndDate'
					GROUP BY 
						1,3 
				) AS A 
				UNION
				( SELECT 
						t.es, 
						'ACTUAL'::text, 
						trim(t.codigo), 
						sum(t.cantidad) 
					FROM 
						$PosTransTable t 
					WHERE 
						t.tm='V' 
						AND t.tipo='C'  and grupo!='D' 
						AND t.dia BETWEEN '$BeginDate' AND '$EndDate'
					GROUP BY 
						1,3 
				) 
				UNION
				( SELECT 
						t.es, 
						'ANTERIOR'::text, 
						lpad(art.art_tipo,6,'0'),  
						sum(t.cantidad) 
					FROM 
						$_PosTransTable t
						LEFT JOIN int_articulos art ON (art.art_codigo=t.codigo) 
					WHERE 
						tm='V' 
						AND tipo='M'
						AND dia BETWEEN '$_BeginDate' AND $_EndDate'
					GROUP BY 
						1,3 
				) 
				UNION
				( SELECT 
						t.es, 
						'ACTUAL'::text, 
						lpad(art.art_tipo,6,'0'), 
						sum(cantidad) 
					FROM 
						$PosTransTable t
						LEFT JOIN int_articulos art ON (art.art_codigo=t.codigo) 
					WHERE 
						tm='V' 
						AND tipo='M'
						AND dia BETWEEN '$BeginDate' AND '$EndDate'
					GROUP BY 
						1,3 
				)
			ORDER BY 1,2,3;";

			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

	case 'DETAIL_PRODUCTS_LINE':
		//usado actualmete en market
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$line_id = $_REQUEST['line_id'];

			$sql = "SELECT
				LINEA.tab_elemento AS Co_Linea,
				LINEA.tab_descripcion AS No_Linea,
				ART.art_descripcion AS No_Producto,
				SUM(MOVIALMA.mov_cantidad) AS Nu_Cantidad,
				FIRST(SALDO.Nu_Costo_Promedio) AS Nu_Costo_Promedio,
				SUM(MOVIALMA.mov_costototal) AS Nu_Costo_Total,
				SUM(PT.importe) AS Nu_Venta_Soles,
				--ROUND(SUM(PT.importe / (1 + (util_fn_igv()/100))) - SUM(MOVIALMA.mov_costototal), 2) AS Nu_Margen
				ROUND(SUM(PT.importe / (1 + (util_fn_igv()/100))) - (
					CASE WHEN SUM(MOVIALMA.mov_costototal)::VARCHAR != '' OR SUM(MOVIALMA.mov_costototal) IS NOT NULL THEN
						SUM(MOVIALMA.mov_costototal)
					ELSE
					0
					END
				), 2) AS Nu_Margen
			FROM
				inv_movialma AS MOVIALMA
				JOIN int_articulos AS ART ON (MOVIALMA.art_codigo = ART.art_codigo)
				LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
				JOIN (
					SELECT
						es,
						dia,
						PT.codigo,
						SUM(importe) AS importe
					FROM
						pos_trans$BeginYear$BeginMonth PT
						JOIN int_articulos AS ART ON (PT.codigo = ART.art_codigo)
						LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
					WHERE
						PT.es = '$warehouse_id'
						AND ART.art_plutipo = '1'
						AND ART.art_unidad NOT IN('000GLN', '0000GL')
						AND PT.dia::DATE BETWEEN '$BeginDate' AND '$EndDate'
					GROUP BY
						1,
						2,
						3
				) AS PT ON (PT.es = MOVIALMA.mov_almacen AND MOVIALMA.mov_fecha::DATE = PT.dia AND PT.codigo = MOVIALMA.art_codigo)
				JOIN (
					SELECT
						stk_almacen,
						stk_periodo,
						SALDO.art_codigo,
						SUM(stk_costo$BeginMonth) AS Nu_Costo_Promedio
					FROM
						inv_saldoalma SALDO
						JOIN int_articulos AS ART ON (SALDO.art_codigo = ART.art_codigo)
						LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
					WHERE
						stk_almacen = '$warehouse_id'
						AND stk_periodo = '$BeginYear'
						AND ART.art_plutipo = '1'
						AND ART.art_unidad NOT IN('000GLN', '0000GL')
					GROUP BY
						1,
						2,
						3
				) AS SALDO ON (SALDO.stk_almacen = MOVIALMA.mov_almacen AND SALDO.stk_periodo = SUBSTRING(MOVIALMA.mov_fecha::TEXT, 1, 4) AND SALDO.art_codigo = MOVIALMA.art_codigo)
			WHERE
				MOVIALMA.mov_almacen = '$warehouse_id'
				AND MOVIALMA.tran_codigo = '45'
				AND ART.art_plutipo = '1'
				AND ART.art_unidad NOT IN('000GLN', '0000GL')
				AND MOVIALMA.mov_fecha BETWEEN '$BeginDate' AND '$EndDate'
				AND LINEA.tab_elemento = '$line_id'
			GROUP BY
				Co_Linea,
				No_Linea,
				art.art_descripcion
			ORDER BY
				nu_venta_soles DESC-- LIMIT 3
			;";

			$data = SQLImplodeJSON($sql);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		case 'UTILITY_LINES':
			/**
			 * ventas/market_productos_linea
			 * Busqueda por estacion(Resumen)
			 */
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];

			$sql = "SELECT
codigo_linea as co_linea,
FIRST(nombre_linea) as no_linea,
SUM(qt_cantidad) AS nu_cantidad,
0.0 AS nu_costo_promedio,
SUM(ss_kardex_promedio_total) AS nu_costo_total,
SUM(ss_tickets_sigv_total) AS nu_venta_soles,
SUM(ss_tickets_sigv_total) - SUM(ss_kardex_promedio_total) AS nu_margen
FROM
(SELECT
 linea.tab_elemento AS codigo_linea,
 linea.tab_descripcion AS nombre_linea,
 art.art_codigo as codigo,
 SUM(m.mov_cantidad) AS qt_cantidad,
 (ROUND(MAX(pre.pre_precio_act1 / (1 + (util_fn_igv() / 100))), 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_tickets_sigv_total,
 (ROUND(
  (CASE
  WHEN sal.stk_costo$BeginMonth = 0.0000 THEN
   COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '$BeginYear-$BeginMonth-01 00:00:00' AND art_codigo = art.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
  ELSE
   sal.stk_costo$BeginMonth
  END)
 , 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_kardex_promedio_total
FROM
 inv_movialma AS m
JOIN int_articulos AS art ON (art.art_codigo = m.art_codigo)
LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
LEFT JOIN fac_lista_precios AS pre ON (pre.art_codigo = m.art_codigo)                
LEFT JOIN (SELECT art_codigo, MAX(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC) AS pro ON (pro.art_codigo = art.art_codigo)
LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = m.mov_almacen AND sal.art_codigo = m.art_codigo AND sal.stk_periodo = '$BeginYear')
WHERE
 m.mov_almacen = '$warehouse_id'
 AND m.mov_fecha::DATE BETWEEN '$BeginDate' AND '$EndDate'
 AND m.tran_codigo = '45'
 AND art.art_plutipo = '1'
 AND art.art_unidad NOT IN('000GLN', '0000GL')
 AND pre.pre_lista_precio = (SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio')
GROUP BY
 linea.tab_elemento,
 linea.tab_descripcion,
 art.art_codigo,
 sal.stk_costo$BeginMonth
) AS A
GROUP BY
 codigo_linea
ORDER BY
 nu_margen DESC;";
 			error_log('Resumen de lineas:');
 			error_log($sql);

			$data = SQLImplodeJSON($sql);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		case 'UTILITY_LINES_DETAIL':
			/**
			 * ventas/market_productos_linea
			 * Busqueda por linea(Detalles)
			 */
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$line_id = $_REQUEST['line_id'];

			$sql = "SELECT
codigo_linea as co_linea,
nombre_linea as no_linea,
qt_cantidad AS nu_cantidad,
codigo AS co_producto,
articulo AS no_producto,
0.0 AS nu_costo_promedio,
ss_kardex_promedio_total AS nu_costo_total,
ss_tickets_sigv_total AS nu_venta_soles,
ss_tickets_sigv_total - ss_kardex_promedio_total AS nu_margen
FROM
(SELECT
 linea.tab_elemento AS codigo_linea,
 linea.tab_descripcion AS nombre_linea,
 art.art_codigo as codigo,
 art.art_descripcion as articulo,
 SUM(m.mov_cantidad) AS qt_cantidad,
 (ROUND(MAX(pre.pre_precio_act1 / (1 + (util_fn_igv() / 100))), 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_tickets_sigv_total,
 (ROUND(
  (CASE
  WHEN sal.stk_costo$BeginMonth = 0.0000 THEN
   COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '$BeginYear-$BeginMonth-01 00:00:00' AND art_codigo = art.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
  ELSE
   sal.stk_costo$BeginMonth
  END)
 , 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_kardex_promedio_total
FROM
inv_movialma AS m
JOIN int_articulos AS art ON (art.art_codigo = m.art_codigo)
LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
LEFT JOIN fac_lista_precios AS pre ON (pre.art_codigo = m.art_codigo)                
LEFT JOIN (SELECT art_codigo, MAX(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC) AS pro ON (pro.art_codigo = art.art_codigo)
LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = m.mov_almacen AND sal.art_codigo = m.art_codigo AND sal.stk_periodo = '$BeginYear')
WHERE
 m.mov_almacen = '$warehouse_id'
 AND m.mov_fecha::DATE BETWEEN '$BeginDate' AND '$EndDate'
 AND m.tran_codigo = '45'
 AND art.art_plutipo = '1'
 AND art.art_unidad NOT IN('000GLN', '0000GL')
 AND linea.tab_elemento = '$line_id'
 AND pre.pre_lista_precio = (SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio')
GROUP BY
 linea.tab_elemento,
 linea.tab_descripcion,
 art.art_codigo,
 sal.stk_costo$BeginMonth
) AS A
ORDER BY
 nu_margen DESC;";
 			//830
 			error_log('Detalle de lineas:');
 			error_log($sql);

			$data = SQLImplodeJSON($sql);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		/**
		 *
		 */

		default:
			die("ERR_INVALID_MOD");
}
?>