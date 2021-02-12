<?php

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


function reporte_diftrans($cod_almacen,$trans_cod){
	pg_exec("begin");
	pg_exec("select reporte_diftrans('$trans_cod','$cod_almacen','ret')");
	//echo "select reporte_diftrans('$trans_cod','$cod_almacen','ret')";
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	
	return $rs;
}

function reporte_movdia($fecha_inicial, $fecha_final, $cod_almacen){
        pg_exec("begin");
        pg_exec("select UTIL_FN_GENERA_MOVDIA('$fecha_inicial','$fecha_final','$cod_almacen','ret')");
        $rs = pg_exec("fetch all in ret");
        pg_exec("end");
        return $rs;
}

function sacarExcelDifTrans($user,$titulo,$almacen,$cabecera,$D){
		$user_temp = $user."_rep2";
		exec("echo -e $almacen >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		exec("echo -e $cabecera >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		for($i=0;$i<count($D);$i++){
		exec("echo -e $D[0] ,$D[1] ,$D[2] ,$D[3] ,$D[4] ,$D[5] ,$D[6] ,$D[7] ,$D[8] ,$D[9] ,$D[10] ,$D[11]  >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
		}
		exec("echo -e '' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		
}


function reporte_actualizacion_maestros($tipo,$desde,$hasta,$opt_codigo,$opt_descripcion){
	if(trim($opt_codigo)==""){			$opt_codigo="null";}else{$opt_codigo=strtoupper($opt_codigo);}
	if(trim($opt_descripcion)==""){		$opt_descripcion="null";}else{$opt_descripcion=strtoupper($opt_descripcion);}
	
	$q = "select UTIL_FN_REPORTE_ACTUALIZACIONES('ret','$tipo',to_date('$desde','dd/mm/yyyy')
	,to_date('$hasta','dd/mm/yyyy'),trim('$opt_codigo'),trim('$opt_descripcion'))";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");

return $rs;
}


function fecha_aprosys(){
	pg_exec("begin");
	$rs = pg_exec(" select to_char(util_fn_fechaactual_aprosys(),'dd/mm/yyyy') as fecha ");
	$A = pg_fetch_array($rs,0);
	$ret = $A["fecha"];
	pg_exec("end");

return $ret;
}

function correlativo_documento($cod_documento,$serie,$modo){
	
	$q = "select UTIL_FN_CORRE_DOCS('$cod_documento',lpad('$serie',3,'0'),'$modo')";
	//echo $q;
	$rs = pg_exec($q);
	$A = pg_fetch_array($rs,0);	
	$ret = $A[0];

	return $ret;
}


function costo_unitario_xvalorizacion($valorizado,$cod_item,$almacen){
	
	$q = "select UTIL_FN_COSTO_UNITARIO_XVALORIZACION('$valorizado','$cod_item','$almacen')";
	$rs = pg_exec($q);
	$A = pg_fetch_array($rs,0);
	$ret = $A[0];

	return $ret;
	
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

?>
