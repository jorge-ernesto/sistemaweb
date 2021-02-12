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
	
	if($fecha==""){$fecha="now()";}
	pg_exec("begin");
	pg_exec("select UTIL_FN_TIPOCAMBIO('ret','$fecha','$cod_moneda') ;");
        //echo "\n<!- select UTIL_FN_TIPOCAMBIO('ret','$fecha','$cod_moneda') ;--> ";

	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	if(pg_numrows($rs)>0){
	$A = pg_fetch_array($rs,0);
	}
	return $A[7];
}

function ANALISIS_MOVIMIENTOS_CLIENTES($desde,$hasta,$cliente,$tipmovi){
	if(trim($cliente)==""){
		$q = "SELECT ventas_fn_analisis_movimientos_clientes(to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'ret','$tipmovi')";
	}else{
		$q = "SELECT ventas_fn_analisis_movimientos_clientes_new(to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$cliente','ret','$tipmovi')";
	}
	echo "<!-- $q -->\n";
	pg_exec("begin");
	pg_exec($q); 
	$cursor = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	
	return $cursor;
}

function reporte_cuentas($hasta, $codigo)
{
	if(!isset($codigo)) {
//		$q = "SELECT reporte_cuentas_por_fechas(to_date('$hasta','dd/mm/yyyy'), 'ret')";
		$q = "SELECT reporte_estadocuenta_porfecha(to_date('$hasta','dd/mm/yyyy'), 'ret')";
	} else {
//		$q = "SELECT reporte_cuentas_por_fechas_codigo(to_date('$hasta','dd/mm/yyyy'), '$codigo', 'ret')";
		$q = "SELECT reporte_estadocuenta_porfecha_porcodigo(to_date('$hasta','dd/mm/yyyy'), '$codigo', 'ret')";
	}
	pg_exec("begin");
	pg_exec($q); 
	$cursor = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	return $cursor;
}

function ESTADO_CUENTA_GENERAL($desde,$hasta,$opciones,$tasa_cambio,$categoria){

	$q = "SELECT ccob_fn_estado_cuenta_general_distinto(to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy') ,'$opciones',$tasa_cambio,'$categoria','ret')";
	print $q;
	//echo "\n<!-- FUNCION :$q -->\n";
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	return $rs;	
}
?>
