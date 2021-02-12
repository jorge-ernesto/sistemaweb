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


class CuadroVentaLiquidacionModel extends Model {

	function getAlmacenes() {
		global $sqlca;

		$sql = "
SELECT
 ch_almacen,
 ch_almacen||' - '||ch_nombre_almacen
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
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

    function getUltimaFechaCierrePlaya() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
    }
    
    function getVentaCombustibleyOtrasVentas($arrPOST) {
		global $sqlca;

$sql="
SELECT
	PRIN.id_item,
	PRIN.no_item,
	SUM(COMB.qt_total) as qt_total,

	CASE 
		WHEN SUM(COMB.ss_total) = 0 THEN 0
		ELSE ROUND(SUM(COMB.ss_total) / SUM(COMB.qt_total), 2)
	END AS ss_precio,
	
	SUM(COMB.ss_total) as ss_total,
	SUM(COMB.ss_descuentos) as ss_descuentos,
	SUM(AFE.ss_total) AS ss_total_afericion
FROM
 (SELECT DISTINCT
 CTC.ch_sucursal AS id_almacen,
  CTC.dt_fechaparte AS fe_emision,
  CTC.ch_codigocombustible AS id_item,
  ITEM.ch_nombrecombustible AS no_item
 FROM
  comb_ta_contometros AS CTC
  JOIN comb_ta_combustibles AS ITEM
   USING(ch_codigocombustible)
 WHERE
  CTC.ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
  AND CTC.dt_fechaparte BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 ) PRIN
 JOIN
 (SELECT
  ch_sucursal AS id_almacen,
  dt_fechaparte AS fe_emision,
  ch_codigocombustible AS id_item,
  ROUND(SUM(nu_ventagalon), 2) AS qt_total,
  ROUND(SUM(nu_ventavalor), 2) AS ss_total,
  ROUND(SUM(nu_descuentos), 2) AS ss_descuentos
 FROM
  comb_ta_contometros
 WHERE 	
  ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
  AND dt_fechaparte BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 GROUP BY
  ch_sucursal,
  dt_fechaparte,
  ch_codigocombustible
 ) AS COMB ON(PRIN.id_almacen=COMB.id_almacen AND PRIN.fe_emision=COMB.fe_emision AND PRIN.id_item=COMB.id_item)
 LEFT JOIN
 (SELECT 
  es AS id_almacen,
  dia AS fe_emision,
  codigo AS id_item,
  ROUND(SUM(importe), 2) AS ss_total
 FROM 
  pos_ta_afericiones
 WHERE
  es='" . $arrPOST['sIdAlmacen'] . "'
  AND dia BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 GROUP BY
  es,
  dia,
  codigo
 ) AS AFE ON(PRIN.id_almacen=AFE.id_almacen AND PRIN.fe_emision=AFE.fe_emision AND PRIN.id_item=AFE.id_item)
 GROUP BY PRIN.id_item, PRIN.no_item;
";
		$iStatusSQL = $sqlca->query($sql);
		
		if ( $iStatusSQL>0 ) {
			$arrData = $sqlca->fetchAll();
			$sql2 = "
SELECT
 SUM(FD.nu_fac_valortotal) AS ss_total,
 SUM(FD.nu_fac_cantidad) AS qt_cantidad
FROM
 fac_ta_factura_cabecera AS FC 
 JOIN int_clientes AS CLI
  USING(cli_codigo)
 JOIN fac_ta_factura_detalle as FC
  USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_seriedocumento, cli_codigo)
WHERE
 FC.ch_almacen='" . $arrPOST['sIdAlmacen'] . "'
 AND FC.ch_fac_tipodocumento='45'
 AND FC.dt_fac_fecha BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 AND CLI.cli_ndespacho_efectivo!=1
			";
			$iStatusSQL = $sqlca->query($sql2);
			if ( $iStatusSQL>0 ) {
				$arrData = array_merge($arrData, $sqlca->fetchAll());
			}

			return array(
				'sStatus'=>'success',
				'arrData'=>$arrData,
				'SQL'=>$sql,
				'SQL2'=>$sql2
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros',
				'SQL'=>$sql,
				'SQL2'=>$sql2,
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$sql,
			'SQL2'=>$sql2,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
    }
    
    function getDetalleLiquidacionNDCredito($arrPOST) {
		global $sqlca;
		$iStatusSQL = $sqlca->query($sql);
		
		$sql="SELECT
 SUM(VC.nu_importe) AS ss_total
FROM
 val_ta_cabecera AS VC
 JOIN int_clientes AS CLI
  ON(VC.ch_cliente=CLI.cli_codigo)
WHERE
 VC.ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
 AND VC.dt_fecha BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 AND CLI.cli_ndespacho_efectivo='0'
 AND CLI.cli_anticipo='N'
GROUP BY
 VC.ch_sucursal"; //VC.dt_fecha; //Se retiro de la query 2019-01-06
		
		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL>0 ) {
			return array(
				'sStatus'=>'success',
				'arrData'=>$sqlca->fetchAll(),
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros',
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$query,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
	}

    function getDetalleLiquidacionTCRED($arrPOST) {
		global $sqlca;
		
		$sql="
SELECT
 FIRST(TCRED.tab_desc_breve) AS no_tarjeta_credito,
 SUM(importe) AS ss_total
FROM
 " . $arrPOST['sTablePosTransYM'] . " AS PT
 JOIN int_tabla_general AS TCRED
  ON(TRIM(PT.at)=SUBSTRING(TCRED.tab_elemento,6,6) AND TCRED.tab_tabla='95' AND TCRED.tab_elemento!='000000')
WHERE
 PT.es='" . $arrPOST['sIdAlmacen'] . "'
 AND PT.dia BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 AND PT.fpago!='1'
GROUP BY
 at;
		";		
		
		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL>0 ) {
			return array(
				'sStatus'=>'success',
				'arrData'=>$sqlca->fetchAll(),
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros',
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$query,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
	}
    
    function getDetalleLiquidacionEGRESOS($arrPOST) {
		global $sqlca;
		
		$sql="
SELECT
 SUM(CCTP.amount) AS ss_total
FROM
 c_cash_transaction AS CCT
 JOIN c_cash_operation AS CCO
  USING(c_cash_operation_id)
 JOIN c_cash_transaction_payment AS CCTP
  USING(c_cash_transaction_id)
WHERE
 CCT.ware_house='" . $arrPOST['sIdAlmacen'] . "'
 AND CCT.d_system BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 AND CCT.type='1'
GROUP BY
 CCT.ware_house"; //CCT.d_system; //Se retiro de la query 2019-01-06
		
		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL>0 ) {
			return array(
				'sStatus'=>'success',
				'arrData'=>$sqlca->fetchAll(),
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros',
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$query,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
	}
    
    function getDetalleLiquidacionINGRESOS($arrPOST) {
		global $sqlca;
		
		$sql="
SELECT
 SUM(CCTP.amount) AS ss_total
FROM
 c_cash_transaction AS CCT
 JOIN c_cash_operation AS CCO
  USING(c_cash_operation_id)
 JOIN c_cash_transaction_payment AS CCTP
  USING(c_cash_transaction_id)
WHERE
 CCT.ware_house='" . $arrPOST['sIdAlmacen'] . "'
 AND CCT.d_system BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 AND CCT.type='0'
GROUP BY
 CCT.ware_house"; //CCT.d_system; //Se retiro de la query 2019-01-06

		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL>0 ) {
			return array(
				'sStatus'=>'success',
				'arrData'=>$sqlca->fetchAll(),
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros',
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$query,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
	}
    
    function getDetalleNotasDespachoCredito($arrPOST) {
		global $sqlca;

		$sql="
SELECT
 CLI.cli_codigo AS id_cliente,
 FIRST(CLI.cli_razsocial) AS no_cliente,
 VD.ch_articulo AS id_item,
 (CASE WHEN SUM(VD.nu_cantidad) > 0 THEN ROUND(SUM(VD.nu_importe) / SUM(VD.nu_cantidad), 2) ELSE 0 END) AS ss_precio,
 ROUND(SUM(VD.nu_cantidad), 2) AS qt_cantidad,
 ROUND(SUM(VD.nu_importe), 2) AS ss_total
FROM
 val_ta_cabecera AS VC
 JOIN val_ta_detalle AS VD
  USING(ch_sucursal, dt_fecha, ch_documento)
 JOIN int_clientes AS CLI
  ON(VC.ch_cliente=CLI.cli_codigo)
WHERE
 VC.ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
 AND VC.dt_fecha BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda2'] . "'
 AND CLI.cli_ndespacho_efectivo='0'
 AND CLI.cli_anticipo='N'
 AND (VD.ch_articulo = '11620301' OR VD.ch_articulo = '11620302' OR VD.ch_articulo = '11620303' OR VD.ch_articulo = '11620304' OR VD.ch_articulo = '11620305' OR VD.ch_articulo = '11620307')
GROUP BY
 VC.ch_sucursal,
 CLI.cli_codigo,
 VD.ch_articulo
ORDER BY
 2,3"; //VC.dt_fecha //Se retiro de la query 2020-01-06

		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL>0 ) {
			return array(
				'sStatus'=>'success',
				'arrData'=>$sqlca->fetchAll(),
				'iTotalRegistros'=>$iStatusSQL,
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros',
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$query,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
	}
    
    function getControlInventario($arrPOST) {
		global $sqlca;

		$sql="
SELECT
 VARILLA_ACTUAL.id_item,
 VARILLA_ACTUAL.no_item,
 VARILLA_AYER.qt_total AS qt_total_varilla_ayer,
 ENTRADA.qt_total AS qt_total_entrada,
 SALIDA.qt_total AS qt_total_salida,
 VARILLA_ACTUAL.qt_total AS qt_total_varilla_hoy,
 VARILLA_MES.qt_total_mes AS qt_total_varilla_mes
FROM
 (SELECT
  VARILLA.ch_sucursal AS id_almacen,
  VARILLA.dt_fechamedicion AS fe_emision,
  TANK.ch_codigocombustible AS id_item,
  FIRST(ITEM.ch_nombrecombustible) AS no_item,
  ROUND(SUM(VARILLA.nu_medicion), 3) AS qt_total
 FROM
  comb_ta_mediciondiaria AS VARILLA
  JOIN comb_ta_tanques AS TANK
   USING(ch_tanque)
  JOIN comb_ta_combustibles AS ITEM
   USING(ch_codigocombustible)
 WHERE
  VARILLA.ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
  AND VARILLA.dt_fechamedicion BETWEEN '" . $arrPOST['dBusqueda'] . "' AND '" . $arrPOST['dBusqueda'] . "'
 GROUP BY
  VARILLA.ch_sucursal,
  VARILLA.dt_fechamedicion,
  TANK.ch_codigocombustible
 ) AS VARILLA_ACTUAL
 LEFT JOIN
 (SELECT
  VARILLA.ch_sucursal AS id_almacen,
  VARILLA.dt_fechamedicion AS fe_emision,
  TANK.ch_codigocombustible AS id_item,
  ROUND(SUM(VARILLA.nu_medicion), 3) AS qt_total
 FROM
  comb_ta_mediciondiaria AS VARILLA
  JOIN comb_ta_tanques AS TANK
   USING(ch_tanque)
 WHERE
  VARILLA.ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
  AND VARILLA.dt_fechamedicion BETWEEN '".$arrPOST['dBusquedaAyer']."' AND '" . $arrPOST['dBusquedaAyer'] . "'
 GROUP BY
  VARILLA.ch_sucursal,
  VARILLA.dt_fechamedicion,
  TANK.ch_codigocombustible
 ) AS VARILLA_AYER USING(id_almacen,id_item)
 LEFT JOIN (
 SELECT
  mov_almacen AS id_almacen,
  mov_fecha::DATE AS fe_emision,
  art_codigo AS id_item,
  ROUND(SUM(mov_cantidad), 3) AS qt_total
 FROM
  inv_movialma
 WHERE
  mov_almacen='" . $arrPOST['sIdAlmacen'] . "'
  AND mov_fecha BETWEEN '" . $arrPOST['dBusqueda'] . " 00:00:00' AND '" . $arrPOST['dBusqueda'] . " 23:59:59'
  AND tran_codigo IN('21', '27')
 GROUP BY
  mov_almacen,
  mov_fecha::DATE,
  art_codigo
 ) AS ENTRADA USING(id_almacen,id_item)
 LEFT JOIN (
 SELECT
  mov_almacen AS id_almacen,
  mov_fecha::DATE AS fe_emision,
  art_codigo AS id_item,
  ROUND(SUM(mov_cantidad), 3) AS qt_total
 FROM
  inv_movialma
 WHERE
  mov_almacen='" . $arrPOST['sIdAlmacen'] . "'
  AND mov_fecha BETWEEN '" . $arrPOST['dBusqueda'] . " 00:00:00' AND '" . $arrPOST['dBusqueda'] . " 23:59:59'
  AND tran_codigo='25'
 GROUP BY
  mov_almacen,
  mov_fecha::DATE,
  art_codigo
 ) AS SALIDA USING(id_almacen,id_item)
 LEFT JOIN (
 SELECT
  T.ch_sucursal AS id_almacen,
  T.ch_codigocombustible AS id_item,
  SUM(MD2.nu_medicion-(MD1.nu_medicion+(CASE WHEN MA.compras>0 THEN MA.compras ELSE 0.00 END)-C.ventas)) AS qt_total_mes
 FROM  
  comb_ta_mediciondiaria AS MD1
  INNER JOIN(
  SELECT
   ch_sucursal,
   ch_tanque,
   ch_codigocombustible
  FROM
   comb_ta_tanques
  ) AS T ON(T.ch_tanque=MD1.ch_tanque AND T.ch_sucursal=MD1.ch_sucursal)
  LEFT JOIN(
  SELECT
   mov_fecha::DATE AS fecha,
   art_codigo,
   SUM(mov_cantidad) AS compras 
  FROM
   inv_movialma 
  WHERE
   tran_codigo='21'
   AND mov_almacen='" . $arrPOST['sIdAlmacen'] . "'
  GROUP BY
   art_codigo,
   mov_fecha::DATE
  ) AS MA ON(MA.art_codigo=T.ch_codigocombustible and MA.fecha=MD1.dt_fechamedicion+1)
  INNER JOIN(
  SELECT
   ch_sucursal,
   dt_fechaparte,
   ch_codigocombustible,
   SUM(nu_afericionveces_x_5) AS afericion,
   SUM(nu_ventagalon) AS venta,
   SUM(nu_ventagalon-(nu_afericionveces_x_5*5)) AS ventas
  FROM
   comb_ta_contometros
  GROUP BY
   ch_sucursal,
   dt_fechaparte,
   ch_codigocombustible
  ) AS C ON(C.ch_sucursal=MD1.ch_sucursal AND C.dt_fechaparte=MD1.dt_fechamedicion+1 AND C.ch_codigocombustible=T.ch_codigocombustible)
  INNER JOIN comb_ta_combustibles AS Co
   ON(Co.ch_codigocombustible = T.ch_codigocombustible)
  INNER JOIN(
  SELECT
   dt_fechamedicion,
   ch_sucursal,
   ch_tanque,
   nu_medicion
  FROM
   comb_ta_mediciondiaria
  ) AS MD2 ON(MD2.dt_fechamedicion=MD1.dt_fechamedicion+1 AND MD2.ch_sucursal=MD1.ch_sucursal AND MD2.ch_tanque=T.ch_tanque)
 WHERE
  MD1.dt_fechamedicion BETWEEN TO_DATE('" . substr($arrPOST['dBusqueda'], 0, 8) . "01','YYYY-MM-DD') - 1 AND TO_DATE('" . $arrPOST['dBusqueda'] . "','YYYY-MM-DD') - 1
  AND MD1.ch_sucursal='" . $arrPOST['sIdAlmacen'] . "'
 GROUP BY
  T.ch_sucursal,
  T.ch_codigocombustible
 ) AS VARILLA_MES USING(id_almacen,id_item)
;
	";
		
		// echo "getControlInventario:";
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";
		
		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL>0 ) {
			return array(
				'sStatus'=>'success',
				'arrData'=>$sqlca->fetchAll(),
				'iTotalRegistros'=>$iStatusSQL,
			);
		} else if ( $iStatusSQL==0 ) {
			return array(
				'sStatus'=>'warning',
				'sMessage'=>'No hay registros (Verificar ingreso de varilla del día ' . $arrPOST['dBusqueda'] . ')',
				'sCssStyle'=>'
				color: #856404;
				background-color: #fff3cd;
				border-color: #ffeeba;
				',
			);
		}
		return array(
			'sStatus'=>'danger',
			'sMessage'=>'Problemas al obtener reporte',
			'sMessageSQL'=>$sqlca->get_error(),
			'SQL'=>$sql,
			'sCssStyle'=>'
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
			',
		);
	}

function getControlInventarioNuevo($almacen,$cod_combustible,$fechad,$fechaa,$unidadmedida,$detallecompras) {		
	global $sqlca;

	$fechad = trim($fechad);
	$fechad = strip_tags($fechad);
	$fechad = explode("-", $fechad);
	$fechad = $fechad[2] . "-" . $fechad[1] . "-" . $fechad[0];
	
	$fechaa = trim($fechaa);
	$fechaa = strip_tags($fechaa);
	$fechaa = explode("-", $fechaa);
	$fechaa = $fechaa[2] . "-" . $fechaa[1] . "-" . $fechaa[0];		

	//Aqui se saca el codigo del combustible de una vez para no estar metiendo mas cosas
	$cod_combustible = $cod_combustible;

	// Lo movi aca JCP 04/09/2010
	if(trim($cod_combustible)=='11620307' && $unidadmedida=='Galones'){
		$factor=3.785411784;
	}else{
		$factor=1; //Por defecto todo esta en litros
	}	

	// $factor = 1; //Por defecto todo esta en litros

	// echo "Query 1:";
	// echo "<pre>";
	// echo $comb;
	// echo "</pre>";	
	// return array("data" => $cod_combustible);
	
	//FECHA
	$qf = "SELECT 
				to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
				SUM(nu_medicion) AS saldo,
				to_char(dt_fechamedicion- interval '1 day','DD-MM-YYYY') AS fecha2
			FROM 
				comb_ta_mediciondiaria
			WHERE 
				ch_sucursal=trim('$almacen')
				AND ch_tanque IN (SELECT ch_tanque FROM comb_ta_tanques WHERE ch_codigocombustible = '$cod_combustible')
				AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
				AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
			GROUP BY 
				dt_fechamedicion,
				fecha2 
			ORDER BY 
				dt_fechamedicion";
		//echo $qf;
		$sqlca->query($qf);        		

        // echo "Query 2:";
        // echo "<pre>";
        // echo $qf;
		// echo "</pre>";
		// return array("data" => $sqlca->fetchAll());		
	
	//IF POR LO QUE ENCONTO FRED
	if($sqlca->numrows()>0){
		for($i=0;$i<$sqlca->numrows();$i++){
		
			$A = $sqlca->fetchRow(); //Importante		
		
			$rep[$i][0] = $A[0];
			$fec[$i] = $A[0];
			$fec_saldo[$i] = $A[2];
		}		
		//return array("data" => $A);
	
		//SALDO
		$qe =  "SELECT 
				to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
				ROUND(SUM(nu_medicion)/'$factor',3) AS saldo
			FROM	
				comb_ta_mediciondiaria
			WHERE 
				ch_sucursal=trim('$almacen')
				AND dt_fechamedicion >= to_date('$fec_saldo[0]','dd-mm-yyyy')
				AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
				AND ch_tanque IN (SELECT ch_tanque FROM comb_ta_tanques WHERE ch_codigocombustible = '$cod_combustible')
			GROUP BY 
				dt_fechamedicion 
			ORDER BY 
				dt_fechamedicion";
	
		$sqlca->query($qe);        				
		// echo "Query 3:";
		// echo "<pre>";
		// echo $qe;
		// echo "</pre>";
	
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
		//return array("A" => $Saldo);

		//COMPRA
		$limit = count($fec)-1;
		$qc = "SELECT 
					to_char(mov_fecha::DATE,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(mov_cantidad)/'$factor',3) AS compra
				FROM
					inv_movialma mov 
				WHERE 
					tran_codigo	= '21'
					AND mov_almacen	= trim('$almacen')
					AND art_codigo	= '$cod_combustible'
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','dd-mm-yyyy')
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','dd-mm-yyyy')
				GROUP BY 
					mov_fecha::DATE";
		$sqlca->query($qc);        						

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
		$qma = "SELECT
					TO_CHAR(a.dia, 'DD-MM-YYYY') AS fecha,
					ROUND(SUM(a.cantidad)/'$factor',3) AS medicion
				FROM
					pos_ta_afericiones a
					LEFT JOIN comb_ta_tanques t ON(t.ch_codigocombustible = a.codigo AND t.ch_sucursal = a.es)
				WHERE
					a.es = trim('$almacen')
					AND a.dia BETWEEN to_date('$fechad','dd-mm-yyyy') AND to_date('$fechaa','dd-mm-yyyy')
					AND t.ch_tanque IN (SELECT ch_tanque FROM comb_ta_tanques WHERE ch_codigocombustible = '$cod_combustible')
				GROUP BY
					a.dia
				ORDER BY
					a.dia";
		$sqlca->query($qma);        						

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
		$qv = "SELECT 
					to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(cont.nu_ventagalon)/'$factor',3) AS venta,
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
					AND cont.ch_tanque IN (SELECT ch_tanque FROM comb_ta_tanques WHERE ch_codigocombustible = '$cod_combustible')
				GROUP BY 
					dt_fechaparte";	
		$sqlca->query($qv); 	

		for($a=0;$a<$sqlca->numrows();$a++){
			$A = $sqlca->fetchRow();
			$F4[$a]             = $A[0];
			$VENTA[$a]          = $A[1];
            $PRECIO_VENTA[$a]   = $A[2];
		}

		for($i = 0; $i < count($fec); $i++){

			$rep[$i][4] = "0.000";

			for($b = 0; $b < count($F4); $b++)

			if($F4[$b]==$fec[$i]){
                $rep[$i][4] = $VENTA[$b];
                $rep[$i][12] = $PRECIO_VENTA[$b];
            }

		}

		//INGRESO
		$qi = "SELECT 
					to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(mov_cantidad)/'$factor',3) AS compra
				FROM 
					inv_movialma
				WHERE 
					mov_almacen=trim('$almacen')
					AND art_codigo='$cod_combustible'
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
					AND tran_codigo='27' 
				GROUP BY 
					mov_fecha::date";
		$sqlca->query($qi); 	

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
		$qs = "SELECT 
					to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(mov_cantidad)/'$factor',3) AS compra
				FROM 
					inv_movialma
				WHERE 
					mov_almacen=trim('$almacen')
					AND art_codigo='$cod_combustible'
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
					AND tran_codigo='28' 
				GROUP BY 
					mov_fecha::date";
		$sqlca->query($qs); 

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
		$qvar = "SELECT 
					to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(nu_medicion)/'$factor',3) AS saldo
				FROM 
					comb_ta_mediciondiaria
				WHERE 
					ch_sucursal=trim('$almacen')
					AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
					AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
					AND ch_tanque IN (SELECT ch_tanque FROM comb_ta_tanques WHERE ch_codigocombustible = '$cod_combustible')

				GROUP BY 
					dt_fechamedicion 
				ORDER BY dt_fechamedicion";
		$sqlca->query($qvar); 
	
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
	
	} //FIN DEL IF DE LO QUE ENCONTRO FRED
		
	/***Agregado 2020-01-13***/
	$rep2 = array();
	$fecha   = "";
	$no_item = "";
	$qt_total_varilla_ayer = 0;
	$qt_total_entrada      = 0;
	$qt_total_salida       = 0;
	$qt_total_varilla_hoy  = 0;
	$qt_total_varilla_mes  = 0;

	if($rep == null){
		$rep['sStatus']   = "warning";
		$rep['sMessage']  = "No hay registros (Verificar ingreso de varilla del día 2017-12-01)";
		$rep['sCssStyle'] = "color: #856404; background-color: #fff3cd; border-color: #ffeeba;";
		return $rep;
	}

	$rep2['sStatus'] = "success";
	foreach ($rep as $key=>$value) {		
		$fecha                 = $rep[$key]['0']; //Inv Inicial
		$qt_total_varilla_ayer = $rep[$key]['1']; //Inv Inicial
		$qt_total_compra       = $rep[$key]['2']; //Compra
		$qt_total_entrada      = $rep[$key]['5']; //Ingreso
		$qt_total_salida       = $rep[$key]['4']; //Ventas
		$qt_total_varilla_hoy  = $rep[$key]['8']; //Varillaje
		$qt_total_varilla_mes  = (string) $rep[$key]['10']; //Dif. Acumulada		

		if($cod_combustible == 11620301){
			$no_item = "GASOHOL 84";
		}		
		if($cod_combustible == 11620302){
			$no_item = "GASOHOL 90";
		}		
		if($cod_combustible == 11620303){
			$no_item = "GASOHOL 97";
		}		
		if($cod_combustible == 11620304){
			$no_item = "DIESEL B5 UV";
		}		
		if($cod_combustible == 11620305){
			$no_item = "GASOHOL 95";
		}		
		if($cod_combustible == 11620307){
			$no_item = "GLP";
		}		
			
		$rep2['arrData'][] = array(
			'fecha' => $fecha,
			'id_item' => $cod_combustible,
			'no_item' => $no_item,
			'qt_total_varilla_ayer' => $qt_total_varilla_ayer,
			'qt_total_compra'       => $qt_total_compra,
			'qt_total_entrada'      => $qt_total_entrada,
			'qt_total_salida'       => $qt_total_salida,
			'qt_total_varilla_hoy'  => $qt_total_varilla_hoy,
			'qt_total_varilla_mes'  => $qt_total_varilla_mes,
		);
	}	
	/***/

	return $rep2;		
}

	function getDepositosValidados($almacen, $dia1, $turno1, $dia2, $turno2, $busqueda, $find, $tipomoneda) {
		global $sqlca;
				
		if (trim($find) != "" and trim($busqueda) != "") {
			if ($find == "T") // trabajador
				$cond2 = " AND ptt.ch_codigo_trabajador||ptt.ch_apellido_paterno||ptt.ch_apellido_materno||ptt.ch_nombre1||ptt.ch_nombre2 LIKE '%".$busqueda."%' ";
			if ($find == "C") // correlativo
				$cond2 = " AND pdd.ch_numero_correl LIKE '%".$busqueda."%' ";
			if ($find == "D") // numero de documento
				$cond2 = " AND pdd.ch_numero_documento LIKE '%".$busqueda."%' ";
			if ($find == "S") // serie
				$cond2 = " AND pdd.ch_serie1||pdd.ch_serie2||pdd.ch_serie3 LIKE '%".$busqueda."%' ";												
		}		
				
		if (trim($turno1) == "" and trim($turno2) == "") {
			$cond = "
				pdd.dt_dia BETWEEN to_date('$dia1', 'dd/mm/yyyy') and to_date('$dia2', 'dd/mm/yyyy') ";
		} else {
			$cond = "
				pdd.dt_dia||to_char(pdd.ch_posturno,'99') BETWEEN to_date('$dia1', 'dd/mm/yyyy')||to_char($turno1,'99') and to_date('$dia2', 'dd/mm/yyyy')||to_char($turno2,'99') ";
		}

		if($tipomoneda == '00'){	// Todos - sin filtro
			$deno = "";
		}elseif($tipomoneda == '01'){	// Monedas - Solo soles(aqui no circulan monedas de dolares)
			$deno = " AND (pdd.ch_moneda = '01' AND pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 = 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0)";
//			$deno = " AND ( pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 = 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0 )";
		}elseif($tipomoneda == '02'){	// Billetes
			$deno = " 
AND (
 (pdd.ch_moneda = '01' AND pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 = 0) OR
 (pdd.ch_moneda = '02' AND pdd.nu_importe > 0 )
)";
//			$deno ="AND
//				(
//					pdd.ch_moneda != '01' AND pdd.nu_importe > 0
//					OR pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 = 0
//				) ";
		}elseif($tipomoneda == '03'){	// Monedas y Billetas
			$deno = "AND (pdd.ch_moneda = '01' AND  pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0)";
//			$deno ="AND ( pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0 )";
		}


		if($almacen != 'TODOS'){
			$cond3 = " AND pdd.ch_almacen='$almacen' ";
		}

//		pdd.ch_almacen='$almacen'

		$sql = "SELECT 	
				pdd.ch_almacen as almacen,
				pdd.ch_tipo_deposito as tipo,
				pdd.ch_valida as valida,
				pdd.dt_dia as dia,
				pdd.ch_posturno as turno,
				pdd.ch_codigo_trabajador as codtrab,
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
				pdd.ch_serie1 as observacion1,
				pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 AS bilbil,
				pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 as monmon	
			FROM
				pos_depositos_diarios pdd
				LEFT JOIN pla_ta_trabajadores ptt ON (pdd.ch_codigo_trabajador = ptt.ch_codigo_trabajador)
			WHERE
				pdd.dt_dia BETWEEN '$dia1' AND '$dia2'
				AND pdd.ch_valida = 'S'				
			ORDER BY  
				fecha, turno, seq, codtrab";
			
			/* Originalmente la consulta sql tenia estas condiciones, sin embargo no es necesario ya que piden todo
			WHERE
				$cond
				$cond3
				$cond2
				$deno
			*/

		//echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$resultado = Array();
		$res 	   = Array();

		$can    = 0;
		$sem    = 0;
		$totsol = 0;
		$totdol = 0;
		$semsol = 0;								
		$semdol = 0;
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['almacen'] 	= $a[0];
		    	$resultado[$i]['tipo'] 		= $a[1];
		    	$resultado[$i]['valida'] 	= $a[2];
		    	$resultado[$i]['dia'] 		= $a[3];
		    	$resultado[$i]['turno'] 	= $a[4];
		    	$resultado[$i]['codtrab'] 	= $a[5];
		    	$resultado[$i]['trabajador'] 	= $a[6];
		    	$resultado[$i]['fecha'] 	= $a[7];
		    	$resultado[$i]['seq'] 		= $a[8];
		    	$resultado[$i]['num'] 		= $a[9];
		    	$resultado[$i]['moneda'] 	= $a[10];
		    	$resultado[$i]['cambio'] 	= $a[11];
		    	$resultado[$i]['soles'] 	= $a[12];
		    	$resultado[$i]['dolares']	= $a[13];
		    	$resultado[$i]['usuario'] 	= $a[14];
		    	$resultado[$i]['ip'] 		= $a[15];
		    	$resultado[$i]['observacion1'] 	= $a[16];
		    	
		    	if(trim($a[10])!="01" and $a[13]>0) {
		    		$resultado[$i]['denominacion'] 	= "Billetes";
				$billetes = $billetes + $a[12];
		    	} else {
			    	if($a[17]>0 and $a[18]==0){
			    		$resultado[$i]['denominacion'] 	= "Billetes";
					$billetes = $billetes + $a[12];
			    	} else {
			    		if($a[17]==0 and $a[18]>0){
			    			$resultado[$i]['denominacion'] 	= "Monedas";
						$monedas = $monedas + $a[12];
			    		} else {
			    			if($a[17]>0 and $a[18]>0){
			    				$resultado[$i]['denominacion'] 	= "B y M";
			    			} else {
			    				$resultado[$i]['denominacion'] 	= "Ninguna";
			    			}
			    		}
			    	}
			}		    		
		    	
		    	if(trim($a[2])=="S" or trim($a[2])=="s") {		    	

				$sem++;	
				$semsol = $semsol + $a[12];								
				$semdol = $semdol + $a[13];

				if(trim($a[10])!="01" and $a[13]>0) {
					$sbilletes = $sbilletes + $a[12];
			    	} else {
				    	if($a[17]>0 and $a[18]==0){
						$sbilletes = $sbilletes + $a[12];
					} else {
			    			if($a[17]==0 and $a[18]>0){
			    				$resultado[$i]['denominacion'] 	= "Monedas";
							$smonedas = $smonedas + $a[12];
			    			}
					}
				}
			}		

		    	$can++;
			$totsol = $totsol + $a[12];
			$totdol = $totdol + $a[13];			
		}

		$res['detalles'] 	  	= $resultado;
		$res['totales']['sem'] 	  	= $sem;
		$res['totales']['semsol']	= $semsol;
		$res['totales']['semdol']	= $semdol;
		$res['totales']['can'] 	  	= $can;
		$res['totales']['totsol'] 	= $totsol;
		$res['totales']['totdol'] 	= $totdol;	
		$res['totales']['billetes'] 	= $billetes;
		$res['totales']['sbilletes'] 	= $sbilletes;
		$res['totales']['monedas']	= $monedas;
		$res['totales']['smonedas'] 	= $smonedas;
		//$res['sql']	= $sql; //Agregado 2020-01-06

		return $res;
	}
	
	function getDetalleBBVA($almacen, $dia1, $dia2) {
		global $sqlca;					

		$sql = "SELECT
					c.d_system,
					SUM(CASE
							WHEN c.c_currency_id=1 THEN i.amount
							ELSE i.amount*c.rate
						END) AS bbva
				FROM
					c_cash_transaction as c
					JOIN c_cash_transaction_payment AS i
					ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					JOIN c_cash_transaction_detail AS CD
					ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
				WHERE
					c.ware_house = '$almacen'
					AND c.c_cash_id = 1--Solo se filtra caja principal
					AND DATE(c.d_system) BETWEEN '$dia1' AND '$dia2'
					AND i.c_bank_id = '2'
					AND c.type = '0'
					AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE DATE(c.d_system) BETWEEN '$dia1' AND '$dia2' AND bpartner ='99999999')
					AND CD.doc_type NOT IN ('10','35')
				GROUP BY
				c.d_system";			

		if ($sqlca->query($sql) < 0) {
			return false;
		}

		$resultado = Array();
		$res 	   = Array();				
		
		/*Agregado 2020-01-08*/
		$resTotal = Array();			
		$total = 0;
		/***/
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['d_system'] = $a[0];
				$resultado[$i]['bbva'] 	   = $a[1];					    			    		    		
				$total                     += $a[1];
		}

		$res['arrData'] = $resultado;	

		/*Agregado 2020-01-08*/
		$resTotal['arrData']['total'] = $total;	
		/***/

		return $resTotal; //$res;
	}
	
	function getDetalleBCP($almacen, $dia1, $dia2) {
		global $sqlca;					

		$sql = "SELECT
					c.d_system,
					SUM(CASE 
							WHEN c.c_currency_id=1 THEN i.amount
							ELSE i.amount*c.rate
						END) AS bcp
				FROM
					c_cash_transaction AS c
					JOIN c_cash_transaction_payment AS i
					ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					JOIN c_cash_transaction_detail AS CD
					ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
				WHERE
					c.ware_house = '$almacen'
					AND c.c_cash_id = 1--Solo se filtra caja principal
					AND DATE(c.d_system) BETWEEN '$dia1' AND '$dia2'
					AND i.c_bank_id = '1'
					AND c.type = '0'
					AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE DATE(c.d_system) BETWEEN '$dia1' AND '$dia2' AND bpartner ='99999999')
					AND CD.doc_type NOT IN ('10','35')
				GROUP BY
					c.d_system";			

		if ($sqlca->query($sql) < 0) {
			return false;
		}

		$resultado = Array();
		$res 	   = Array();				
		
		/*Agregado 2020-01-08*/
		$resTotal = Array();			
		$total = 0;
		/***/
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['d_system'] = $a[0];
				$resultado[$i]['bbva'] 	   = $a[1];					    			    		    		
				$total                     += $a[1];
		}

		$res['arrData'] = $resultado;	

		/*Agregado 2020-01-08*/
		$resTotal['arrData']['total'] = $total;	
		/***/

		return $resTotal; //$res;
	}
	
	function getDetalleScotiabank($almacen, $dia1, $dia2) {
		global $sqlca;					

		$sql = "SELECT
					c.d_system,
					SUM(
						CASE 
							WHEN c.c_currency_id=1 THEN i.amount
							ELSE i.amount*c.rate
						END) AS scotiabank
					FROM
						c_cash_transaction AS c
						JOIN c_cash_transaction_payment AS i
						ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
						JOIN c_cash_transaction_detail AS CD
						ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
					WHERE
						c.ware_house = '$almacen'
						AND c.c_cash_id = 1--Solo se filtra caja principal
						AND DATE(c.d_system) BETWEEN '$dia1' AND '$dia2'
						AND i.c_bank_id = '3'
						AND c.type = '0'
						AND CD.doc_type NOT IN ('10','35')
					GROUP BY
						c.d_system";			

		if ($sqlca->query($sql) < 0) {
			return false;
		}

		$resultado = Array();
		$res 	   = Array();				
		
		/*Agregado 2020-01-08*/
		$resTotal = Array();			
		$total = 0;
		/***/
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['d_system'] = $a[0];
				$resultado[$i]['bbva'] 	   = $a[1];					    			    		    		
				$total                     += $a[1];
		}

		$res['arrData'] = $resultado;	

		/*Agregado 2020-01-08*/
		$resTotal['arrData']['total'] = $total;	
		/***/

		return $resTotal; //$res;
	}
	
	function getDetalleInterbank($almacen, $dia1, $dia2) {
		global $sqlca;					

		$sql = "SELECT
				c.d_system,
				SUM(CASE 
						WHEN c.c_currency_id=1 THEN i.amount
						ELSE i.amount*c.rate
					END)  AS interbank
				FROM
					c_cash_transaction AS c
					JOIN c_cash_transaction_payment AS i
					ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
					JOIN c_cash_transaction_detail AS CD
					ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
				WHERE
					c.ware_house = '$almacen'
					AND c.c_cash_id = 1--Solo se filtra caja principal
					AND DATE(c.d_system) BETWEEN '$dia1' AND '$dia2'
					AND i.c_bank_id = '4'
					AND c.type = '0'
					AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE DATE(c.d_system) BETWEEN '$dia1' AND '$dia2' AND bpartner ='99999999')
					AND CD.doc_type NOT IN ('10','35')
				GROUP BY
					c.d_system";			

		if ($sqlca->query($sql) < 0) {
			return false;
		}

		$resultado = Array();
		$res 	   = Array();				
		
		/*Agregado 2020-01-08*/
		$resTotal = Array();			
		$total = 0;
		/***/
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['d_system'] = $a[0];
				$resultado[$i]['bbva'] 	   = $a[1];					    			    		    		
				$total                     += $a[1];
		}

		$res['arrData'] = $resultado;	

		/*Agregado 2020-01-08*/
		$resTotal['arrData']['total'] = $total;	
		/***/

		return $resTotal; //$res;
	}
	
	function obtieneMarket($desde, $hasta, $estaciones) {
		global $sqlca;

		$desde = trim($desde);
		$desde = strip_tags($desde);
		$desde = explode("-", $desde);
		$desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];
		
		$hasta = trim($hasta);
		$hasta = strip_tags($hasta);
		$hasta = explode("-", $hasta);
		$hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];				
	
	$propiedad = CuadroVentaLiquidacionModel::obtenerPropiedadAlmacenes();
	$almacenes = CuadroVentaLiquidacionModel::obtieneListaEstaciones();
	// return $propiedad;
	// return $almacenes;

	$sqlA = "SELECT SUM(F.nu_fac_valortotal) AS ventatienda
		FROM 	fac_ta_factura_cabecera F LEFT JOIN int_clientes c on F.cli_codigo=c.cli_codigo AND c.cli_ndespacho_efectivo != 1
		WHERE
			F.ch_fac_seriedocumento='" . pg_escape_string($estaciones) . "' AND 
			F.ch_fac_tipodocumento='45' AND
			F.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	$sqlA1 = "SELECT
			SUM(CASE WHEN t.fpago='2' THEN t.importe ELSE 0 END) AS tarjetascredito,
			SUM(CASE WHEN t.tm='V' THEN (CASE WHEN t.importe<0 THEN t.importe ELSE 0 END) ELSE 0 END) AS descuentos
		 FROM
			pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t LEFT JOIN int_clientes k on k.cli_ruc = t.ruc AND k.cli_ndespacho_efectivo != 1
		 WHERE
			t.es='" . pg_escape_string($estaciones) . "' AND
			date(t.dia) BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
			and t.codigo not in (select ch_codigocombustible from comb_ta_combustibles)";
	
	//CREDITOS CLIENTES
	$sqlB = "SELECT
			SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
		 FROM
			(SELECT
				ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno 
			 FROM
				val_ta_cabecera 
			 WHERE
				ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 
			INNER JOIN (SELECT
							ch_documento
				             FROM
							val_ta_detalle VTD 
					     WHERE
							ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles)
							AND ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						GROUP BY ch_documento) VD ON VD.ch_documento = VC.ch_documento
			INNER JOIN (SELECT
						cli_codigo 
				     FROM
						int_clientes 
				     WHERE
						cli_anticipo='N' AND cli_ndespacho_efectivo != 1) C ON C.cli_codigo=VC.ch_cliente";

	//CREDITOS ANTICIPOS
	$sqlC = "
		SELECT
			SUM(VaCa.nu_importe) as importe
		FROM
			val_ta_detalle VTD
			JOIN val_ta_cabecera VaCa ON(VTD.ch_sucursal = VaCa.ch_sucursal AND VTD.dt_fecha = VaCa.dt_fecha AND VTD.ch_documento = VaCa.ch_documento)
			LEFT JOIN int_clientes IC ON (VaCa.ch_cliente = IC.cli_codigo)
		WHERE
			VaCa.ch_sucursal = '".pg_escape_string($estaciones) . "'
			AND IC.cli_ndespacho_efectivo != 1
			AND IC.cli_anticipo = 'S'
			AND VTD.ch_articulo NOT IN (select ch_codigocombustible from comb_ta_combustibles)
			AND VaCa.dt_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY');
	";

	//OPTIMIZACION CODIGO DE DEPOSITOS 03/05/17 CSR
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
		AND PT1.dt_dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
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
/*
	echo "<pre>";
	echo "CSR: Creado";
	var_dump($textofinal);
	echo "</pre>";
*/

	if($textofinal){
	$sqlD = "SELECT
			sum(nu_importe) as importe
			FROM
			pos_depositos_diarios
			WHERE
			ch_almacen = '". pg_escape_string($estaciones) . "'
			AND ch_moneda='01' and ch_valida='S'
			AND
			(
			" . $textofinal . "
			)";

	$sqlE = "SELECT
			sum (nu_importe * tpc.tca_venta_oficial) as importe
			FROM
			pos_depositos_diarios
			JOIN int_tipo_cambio tpc ON (tpc.tca_fecha=dt_dia AND tpc.tca_moneda = '02')
			WHERE
			ch_almacen = '". pg_escape_string($estaciones) . "'
			AND ch_moneda!='01' and ch_valida='S'
			AND
			(
			" . $textofinal . "
			)";
	}
	/*
	//EFECTIVO SOLES
	$sqlD = "SELECT
			sum(D.importe) 
		 FROM
			(SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,sum(nu_importe) as importe  FROM pos_depositos_diarios 
		 WHERE 	     ch_almacen='" . pg_escape_string($estaciones) . "' 
			 and ch_moneda='01' and ch_valida='S' 
			 and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		GROUP BY ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
		INNER JOIN (SELECT PT1.ch_sucursal, PT1.dt_dia, PT1.ch_posturno, PT1.ch_codigo_trabajador
			FROM pos_historia_ladosxtrabajador PT1
			where PT1.ch_tipo='M' 
			and PT1.ch_codigo_trabajador not in 
			(SELECT PT2.ch_codigo_trabajador 
			FROM pos_historia_ladosxtrabajador PT2 
				where PT2.ch_tipo='C' and PT2.dt_dia=PT1.dt_dia and PT2.ch_posturno=PT1.ch_posturno
				group by PT2.ch_sucursal,PT2.dt_dia,PT2.ch_codigo_trabajador)
				group by PT1.ch_sucursal, PT1.dt_dia, PT1.ch_posturno, PT1.ch_codigo_trabajador) T
				on T.ch_sucursal = D.ch_almacen 
				and T.ch_posturno = D.ch_posturno
				and T.ch_codigo_trabajador = D.ch_codigo_trabajador
				and T.dt_dia = D.dt_dia";
	*/
	//EFECTIVO DOLARES
	/*$sqlE = "SELECT sum(D.importe) 
		FROM (SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,sum(nu_importe*nu_tipo_cambio) as importe  FROM pos_depositos_diarios 
		WHERE ch_almacen='" . pg_escape_string($estaciones) . "' 
			and ch_moneda!='01' and ch_valida='S' 
			and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		group by     ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
		INNER JOIN (SELECT PT1.ch_sucursal,PT1.dt_dia,PT1.ch_posturno,PT1.ch_codigo_trabajador
			FROM pos_historia_ladosxtrabajador PT1
			WHERE PT1.ch_tipo='M' 
			AND PT1.ch_codigo_trabajador not in 
			(SELECT PT2.ch_codigo_trabajador 
			FROM pos_historia_ladosxtrabajador PT2
			WHERE PT2.ch_tipo='C' and PT2.dt_dia=PT1.dt_dia and PT2.ch_posturno=PT1.ch_posturno
			GROUP BY PT2.ch_sucursal,PT2.dt_dia,PT2.ch_codigo_trabajador )
			group by PT1.ch_sucursal,PT1.dt_dia,PT1.ch_posturno,PT1.ch_codigo_trabajador) T
		on 	T.ch_sucursal = D.ch_almacen 
			and T.ch_posturno = D.ch_posturno
			and T.ch_codigo_trabajador = D.ch_codigo_trabajador
			and T.dt_dia = D.dt_dia";
*/
	//FALTANTES
	$sqlH="SELECT 	sum(-importe)
		FROM	comb_diferencia_trabajador CD
		WHERE 	importe<0 and es='".pg_escape_string($estaciones)."' and tipo='M' and  dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	//SOBRANTES
	$sqlI="SELECT 	sum(-importe)
		FROM	comb_diferencia_trabajador CD
		WHERE 	importe>0 and es='" . pg_escape_string($estaciones)."' and tipo='M' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	//DATOS MARKET
		//echo "\n".'- MARKET:'.$sqlA.' -';
		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2] = $a[0];
		}

		//echo "\n".'- MARKET:'.$sqlA1.' -';
		if ($sqlca->query($sqlA1) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][5] = $a1[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][6] = $a1[1];
		}

		//echo "\n".'- MARKET CREDITOS CLIENTES: '.$sqlB.' -';
		if ($sqlca->query($sqlB) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$b = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = $b[0];
		}

		//echo "\n".'- MARKET CREDITOS ANTICIPOS: '.$sqlC.' -';
		if ($sqlca->query($sqlC) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$c = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = $c[0];
		}

		//echo "\n".'- MARKET EFECTIVO SOLES: '.$sqlD.' -';
		if (!$textofinal) {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = 0.00;
		} else {
			if ($sqlca->query($sqlD) < 0) 
				return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			   	$d = $sqlca->fetchRow();
			    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = $d[0];
			}
		}

		//echo "\n".'- MARKET EFECTIVO DOLARES: '.$sqlE.' -';
		if (!$textofinal) {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = 0.00;
		} else {
			if ($sqlca->query($sqlE) < 0) 
				return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			    	$e = $sqlca->fetchRow();
			    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][8] = $e[0];
			}
		}

		//echo "\n".'- MARKET FALTANTES: '.$sqlH.' -';
		if ($sqlca->query($sqlH) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$h = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][9] = $h[0];
		}

		//echo "\n".'- MARKET SOBRANTES: '.$sqlI.' -';
		if ($sqlca->query($sqlI) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$ii = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][10] = $ii[0];
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

}
