<?php

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

function correlativo_formulario($tran_codigo,$tipo){
	$rs = pg_exec("select UTIL_FN_CORRE_FORM('$tran_codigo','$tipo')");
	$A = pg_fetch_array($rs,0);
	$corre = $A[0];

return $corre;
}

function numeroAleatorio($ini,$fin){
	$r = rand($ini, $fin);
return $r;
}

function momentoActual(){
$rs = pg_exec("select now()");
$A = pg_fetch_array($rs,0);
$r = $A[0];

return $r;
}

function costoUnitario($art_codigo)
{
	$rs = pg_exec("SELECT rec_precio FROM com_rec_pre_proveedor WHERE art_codigo='".trim($art_codigo)."' ORDER BY rec_fecha_ultima_compra DESC LIMIT 1;");
	if(pg_numrows($rs)>0){
		$A = pg_fetch_array($rs,0);
	}
	$costo_uni = $A[0];
	return $costo_uni;
}

function costoPromedio($periodo , $mes , $articulo , $almacen){
	$rs = pg_exec(" SELECT UTIL_FN_COSTO_PROMEDIO('$periodo' , '$mes' , '$articulo' , '$almacen') ");
	$A = pg_fetch_array($rs,0);
	$costo_prom = $A[0];
	
return $costo_prom;
}

function stockArticulo($periodo , $mes , $articulo , $almacen){
	global $sqlca;

	$year	= date("Y");
	$month	= date("m");

	$query = "
		SELECT
			stk_stock".$month."
		FROM
			inv_saldoalma
		WHERE
			TRIM(stk_almacen) 	= '".trim($almacen)."'
			AND stk_periodo		= '".$year."'
			AND TRIM(art_codigo)	= '".trim($articulo)."';
	";

	$sqlca->query($query);
	$row		= $sqlca->fetchRow();
	$art_stock	= $row[0];//stock

	return $art_stock;


/*
ANTES 
	$query = "SELECT UTIL_FN_STOCK('".$periodo."', '".$mes."' , '".$articulo."' , '".$almacen."');";
	$rs		= pg_exec($query);
	$A		= pg_fetch_array($rs);
	$art_stock 	= $A[0];	
	return $art_stock;

*/

}


