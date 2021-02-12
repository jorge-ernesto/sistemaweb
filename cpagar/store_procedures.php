<?php
function ultimo_Dia($fecha){
	//La fecha debe ser en formarto dd/mm/yyyy
	$rs = pg_exec("select UTIL_FN_LAST_FECHA('$fecha')");
	$A = pg_fetch_array($rs,0);
	$ult_dia = $A[0];
	
	return $ult_dia;
	}

function combo($dato){
	$q = "select UTIL_FN_COMBOS('$dato','ret')";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;
}

function ayuda($consulta , $condi , $like){
	$q = "select UTIL_FN_AYUDAS('ret','$consulta','$condi','$like') ";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}

function ayudaOrdcompra($proveedor , $almacen , $orden_compra,$many_alma,$like){
	if($many_alma=="many_alma"){$almacen = "todos";}
	if($like==""){$line = "null";}
	$q = "select UTIL_FN_AYUDA_ORDCOMPRAS('ret','$proveedor','$almacen','$orden_compra','$like') ";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	//echo "ayudaOrdcompra";
	return $rs;
}


function ayudaOrdDev($proveedor , $almacen , $orden_compra,$many_alma,$like){
	if($many_alma=="many_alma"){$almacen = "todos";}
	if($like==""){$line = "null";}
	$q = "select UTIL_FN_AYUDA_DEV('ret','$proveedor','$almacen','$orden_compra','$like') ";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}

function ayudaOrdcompraFecha($proveedor , $almacen , $orden_compra,$many_alma,$like,$fec_desde,$fec_hasta){
	//echo "ayudaOrdcompraFecha<br>";
	$q = "select to_char(to_date('$fec_desde','dd/mm/yyyy') , 'yyyy-mm-dd') as desde ,
	 to_char(to_date('$fec_hasta','dd/mm/yyyy') , 'yyyy-mm-dd') as hasta";
	//echo $q;
	$rs = pg_exec($q);
	$A = pg_fetch_array($rs,0);
	$fec_desde = $A["desde"]." 00:00:00";
	$fec_hasta = $A["hasta"]." 23:59:59";
	
	if($many_alma=="many_alma"){$almacen = "todos";}
	if($like==""){$line = "null";}
	$q = "select UTIL_FN_AYUDA_ORDCOMPRAS_FECHA('ret','$proveedor','$almacen','$orden_compra','$like','$fec_desde', '$fec_hasta' ) ";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}


function ayudaOrdDevFecha($proveedor , $almacen , $orden_compra,$many_alma,$like,$fec_desde,$fec_hasta){
	
	$q = "select to_char(to_date('$fec_desde','dd/mm/yyyy') , 'yyyy-mm-dd') as desde ,
	 to_char(to_date('$fec_hasta','dd/mm/yyyy') , 'yyyy-mm-dd') as hasta";
	//echo $q;
	$rs = pg_exec($q);
	$A = pg_fetch_array($rs,0);
	$fec_desde = $A["desde"]." 00:00:00";
	$fec_hasta = $A["hasta"]." 23:59:59";
	
	if($many_alma=="many_alma"){$almacen = "todos";}
	if($like==""){$line = "null";}
	$q = "select UTIL_FN_AYUDA_DEV_FECHA('ret','$proveedor','$almacen','$orden_compra','$like'
	,'$fec_desde', '$fec_hasta' ) ";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}


function tipoCambio($cod_moneda,$fecha){
	
	/* HORA PERUANA */

	date_default_timezone_set("America/Lima" );
	$t       = microtime(true);
	$micro   = sprintf("%06d",($t - floor($t)) * 1000000);
	$hora    = date('H:i:s.'.$micro,$t);
	$created = date(Y."-".m."-".d)." ".$hora;

	if($fecha == ""){
		//$fecha = "now()";
		$fecha = $created;
	}

	pg_exec("BEGIN");
	pg_exec("SELECT UTIL_FN_TIPOCAMBIO('ret','$fecha','$cod_moneda') ;");
	$rs = pg_exec("FETCH ALL IN RET");
	pg_exec("END");

	if(pg_numrows($rs)>0){
		$A = pg_fetch_array($rs,0);
	}

	return $A[5];
}

