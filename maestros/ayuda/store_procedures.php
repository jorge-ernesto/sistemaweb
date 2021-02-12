<?php
function ultimo_Dia($fecha){
	$rs = pg_exec("select UTIL_FN_LAST_FECHA('1/3/2004')");
	$A = pg_fetch_array($rs,0);
	$ultimo_dia = $A[0];
	
	return $ultimo_dia;
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
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}

function ayudaOrdcompra($proveedor , $almacen , $orden_compra,$many_alma){
	if($many_alma=="many_alma"){$almacen = "todos";}
	$q = "select UTIL_FN_AYUDA_ORDCOMPRAS('ret','$proveedor','$almacen','$orden_compra') ";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	return $rs;
}
/**tipo es linea, ubicacion O PROVEEDOR y 
	$condicion es u array con los codigos ya sea de loinea ubicacion o proveedor*/
function generarPedidoAutomatico($tipo , $condicion, $almacen){
	pg_exec(" delete from tmp_cmp_pedido_automatico ");
	for($i=0;$i<count($condicion);$i++){
		
		pg_exec("select CMP_FN_PEDIDO_AUTOMATICO('".$tipo."' ,'".$condicion[$i]."', '".$almacen."')");
		echo "select CMP_FN_PEDIDO_AUTOMATICO('".$tipo."' ,'".$condicion[$i]."', '".$almacen."')";
	}
}

