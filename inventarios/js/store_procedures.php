<?php
function ultimo_Dia($fecha){
	$rs = pg_exec("select UTIL_FN_LAST_FECHA('1/3/2004')");
	$A = pg_fetch_array($rs,0);
	$ultimo_dia = $A[0];
	return $ultimo_dia;
	}

function combo($dato){
	$q = "select UTIL_FN_COMBOS('$dato','ret')";
	//echo "combo-->".$q;
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

function ayudaOrdcompra($proveedor , $almacen , $orden_compra,$many_alma){
	if($many_alma=="many_alma"){$almacen = "todos";}
	$q = "select UTIL_FN_AYUDA_ORDCOMPRAS('ret','$proveedor','$almacen','$orden_compra') ";
	echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}

function  tipoCambio($fecha_doc,$cod_moneda,$descrip_moneda){
	$q = "select UTIL_FN_TIPOCAMBIO('ret','$fecha_doc','$cod_moneda')";	
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	if(pg_numrows($rs)>0){
	$A	= pg_fetch_array($rs,0);
	}else{
		$A["tca_venta_banco"] = 1;
	}
	//echo $q;
	$RTC["cod_moneda"] 	=  	$cod_moneda;
	$RTC["venta_banco"] 	=	$A["tca_venta_banco"];
	$RTC["des_moneda"] 	= 	$descrip_moneda;
	//echo ":) -->".$RTC["venta_banco"] ;
	return $RTC;
}
