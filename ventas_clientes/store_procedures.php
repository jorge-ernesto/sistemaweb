<?php


function ayuda($consulta , $condi , $like){
	$q = "select UTIL_FN_AYUDAS('ret','$consulta','$condi','$like') ";

	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	return $rs;
}


function por_igv(){
    
    $q = "select round((util_fn_igv()/100),4)";
    $rs = pg_exec($q);
    
    return pg_result($rs,0,0);
}


function cod_igv(){

    $q = " select substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1)";
    $rs = pg_exec($q);

    return pg_result($rs,0,0);
}



function reporte_detalle_ventas($cod_almacen,$fecha,$num_documento){
	$q = "select POST_FN_DETALLE_VENTAS('ret','$cod_almacen',to_date('$fecha','dd/mm/yyyy'),'$num_documento' )";
	pg_exec("begin");
	pg_exec($q);
	//echo $q;
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	
	return $rs;
}

function reporte_detalle_ventas_fac($tipodocumento,$seriedocumento,$numerodocumento,$cli_codigo,$fecha){
	$q = "select POST_FN_DETALLE_VENTAS_FAC('ret','$tipodocumento','$seriedocumento','$numerodocumento','$cli_codigo',to_date('$fecha','dd/mm/yyyy'))";
	pg_exec("begin");
	pg_exec($q);
	//echo $q;
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	
	return $rs;
}

function sacarExcelDetVentas($user,$titulo,$almacen,$cabecera,$rs){
		$user_temp = $user."_rep3";
		
		//exec("echo -e $almacen >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		exec("echo -e '$cabecera' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		for($b=0;$b<pg_numrows($rs);$b++){
		$D = pg_fetch_array($rs,$b);
		exec("echo -e $D[0]$D[1] ,$D[2] ,$D[3] ,- ,- ,$D[5] ,$D[6] ,$D[7] >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
		echo "DEBUG !";
		}
		exec("echo -e '' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		
}

function reporte_detalle_ventasxtipo($cod_almacen,$fechad,$fechaa){
	$q = "select POST_FN_DETALLE_VENTAS_RESUMENXTIPO('ret','$cod_almacen',to_date('$fechad','dd/mm/yyyy'),to_date('$fechaa','dd/mm/yyyy') )";
	pg_exec("begin");
	pg_exec($q);
	//echo "CARAJO".$q;
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	
	return $rs;
}

function sacarExcelDetVentasXTipo($user,$titulo,$almacen,$cabecera,$rs,$T){
	$user_temp = $user."_rep3";
	exec("echo -e '$titulo' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
	exec("echo -e '$cabecera' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
	for($i=0;$i<pg_numrows($rs);$i++){
	$S = pg_fetch_array($rs,$i);
	$total_vn = $total_vn + $S[1];
	$total_imp = $total_imp + $S[2];
	$total =  $total + $S[3];
	}
	
	for($i=0;$i<pg_numrows($rs);$i++){
		$D = pg_fetch_array($rs,$i);
		$porc = 100*($D[1]/$total_vn);
		exec("echo -e $D[0] , $D[1] ,$D[2] ,$D[3] , $porc % >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
	}
	exec("echo -e 'TOTALES DEL RESUMEN , $T[0] ,$T[1] ,$T[2] , $T[3] ' >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
}

function reporte_ventas_mensuales($periodo,$cod_almacen,$cod_linea,$modo){
	$q = "select POST_FN_VENTAS_MENSUALES('ret','$periodo','$cod_almacen','$cod_linea','$modo')";
	pg_exec("begin");
	pg_exec($q);
//	echo $q;
	$rs = pg_exec("fetch all in ret"); 
	pg_exec("close ret");
	pg_exec("end");
	return $rs;
}

function sacarExcelRepVentasMensuales($user,$titulo,$almacen,$cabecera,$rs){
		$user_temp = $user."_rep4";
		exec("echo -e '$titulo \n \n' > /var/www/html/sistemaweb/tmp/$user_temp.txt");
		//exec("echo -e $almacen >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		exec("echo -e '$cabecera' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		for($b=0;$b<pg_numrows($rs);$b++){
		$D = pg_fetch_array($rs,$b);
		exec("echo -e $D[2],$D[3],$D[4],$D[5],$D[6],$D[7],$D[8],$D[9],$D[10],$D[11],$D[12],$D[13],$D[14],$D[15],$D[16],$D[17],$D[18],$D[19],$D[20],$D[21],$D[22],$D[23] >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
		//echo "DEBUG !";
		}
		exec("echo -e '' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		
}

function reporte_revdia_transaciones($fechad, $fechaa, $cod_almacen, $periodo, $mes, $modo,$tipo){
	//--(ret,fechadd,fechaa,almacen,periodo,modo)
	$q = "select VEN_FN_REVISION_TICKETS('ret', '$fechad', '$fechaa', '$cod_almacen', '$periodo' , '$mes', '$modo','$tipo')";
	//echo $q;
	if(existeError($q,$fechad,$fechaa)){
	pg_exec("begin");
	pg_exec($q);
	
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	return $rs;
	}else{//echo "Ah ocurrido u error";
	}
}

function existeError($q,$fechad,$fechaa){

if(!($db = pg_connect("user=postgres dbname=integrado")))
   die("pg_connect");
	
  if(!pg_send_query($db, $q))
   die("pg_send_query");

  if(!($result = pg_get_result($db)))
   die("pg_get_result");
	$mensaje = pg_result_error($result);
//  echo(pg_result_error($result) . "<br />\n");
if(substr_count($mensaje,"timestamp incorrecta")>0){
$msj = "La fecha ingresadas ".$fechad." y ".$fechaa." esta teniendo problemas quizas ingreso un dia 30 en un mes que solo tiene 29 dias o quizas ha puesto el mes 13 que no existe o algo asi";
 
?>
<script language="JavaScript">alert("<?php echo $msj;?>");</script>
<?php
}
//pg_close($db);
	
	if(strlen($mensaje)==0) {
	return true;
	}else {
	return false;
	}
}

function sacarExcelRevDiaTransacciones($user,$titulo,$almacen,$cabecera,$rsM,$rsC){
		$user_temp = $user."_rep6";
		exec("echo -e '$titulo \n \n' > /var/www/html/sistemaweb/tmp/$user_temp.txt");
		//exec("echo -e $almacen >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		exec("echo -e '$cabecera' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		for($k=1;$k<=2;$k++){
		if($k==1){$rs = $rsM;}
	  	if($k==2){$rs = $rsC;}
		for($b=0;$b<pg_numrows($rs);$b++){
		$D = pg_fetch_array($rs,$b);
		exec("echo -e $D[0],$D[1],$D[2],$D[3] - $D[4],$D[5],$D[6] >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
		//echo "DEBUG !";
		}
		}
		exec("echo -e '' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		
}

//UTILITARIO PARA COMBOS

function combo($dato){
	$q = "SELECT util_fn_combos('$dato','ret')";
	//echo $q;
	pg_exec("begin");
	pg_exec($q);
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;
}


function REPORTE_VENTAS_DIARIAS($est, $desde,$hasta,$opt_reporte){

	$q = "select VENTAS_FN_REPORTE_VENTAS_DIARIAS
	('$est',to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	,'$opt_reporte','ret')";
	//print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}


function REPORTE_VALES_X_CENTRO_COSTO($est, $desde,$hasta){

	$q = "select VENTAS_FN_REPORTE_VALES_X_CENTRO_COSTO
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$est','ret')";
	//print $q;
	// echo "<pre>";
	// print_r($q);
	// echo "</pre>";

	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

	return $rs;	
}

function REPORTE_RESUMEN_DIARIO_VALES_X_CENTRO_COSTO($est, $desde,$hasta,$cliente){

	$q = "select VENTAS_FN_REPORTE_RESUMEN_DIARIO_VALES_X_CENTRO_COSTO
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$est','$cliente','ret')";
	//print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}

function REPORTE_DETALLE_CONSUMO_VALES($est,$desde,$hasta,$cliente,$num_liqui){

	$q = "select VENTAS_FN_REPORTE_DETALLE_CONSUMO_VALES
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$est','$cliente','$num_liqui','ret')";
//print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}


function VENTAS_LIQUIDACION_VALES($desde,$hasta,$documento,$serie,$fec_liquidacion){

	//$serie = "";
	/*switch($documento){
		case "000010":
			//Factura
			$serie="001";
		break;
		
		case "000035":
			//Boleta
			$serie="501";
		break;
		
	}*/
	
	echo "serie es " . $serie;
	/*$q = "select VENTAS_LIQUIDACION_VALES 
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,SUBSTRING('$documento' for 2 from length('$documento')-1)
	 ,'$serie',to_date('$fec_liquidacion','dd/mm/yyyy'))";*/
	$q = "select VENTAS_LIQUIDACION_VALES 
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,'$documento'
	 ,'$serie',to_date('$fec_liquidacion','dd/mm/yyyy'))";
	
//	print $q;
	//pg_exec("begin");
	$rs = pg_exec($q); 
	echo "resultados = " . pg_numrows($rs);
	//pg_exec("end");
	//$rs = pg_exec("fetch all in ret");
	//pg_exec("close ret");
	//pg_exec("end");

//return $rs;	
}


function REPORTE_CONSUMO_PERSONAL($desde,$hasta,$est,$trabajador,$opcion){

	$q = "select VENTAS_FN_REPORTE_CONSUMO_PERSONAL
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$est','$trabajador','$opcion','ret')";
	print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}


function REPORTE_CONSUMO_PLACA($desde,$hasta,$est,$cliente){
	//VENTAS_FN_REPORTE_CONSUMO_PLACA (DESDE,HASTA,ALMACEN,CLIENTE,RET)
	$q = "select VENTAS_FN_REPORTE_CONSUMO_PLACA
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$est','$cliente','ret')";
	//print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}

function REPORTE_RESUMEN_CONSUMOS_XCLIENTE($desde,$hasta,$tablas,$opcion1,$xgrupo){
	//VENTAS_FN_REPORTE_CONSUMO_PLACA (DESDE,HASTA,ALMACEN,CLIENTE,RET)
	
	$q = "select VENTAS_FN_REPORTE_RESUMEN_CONSUMOS_XCLIENTE
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$tablas','$opcion1','$xgrupo','ret')";
//	print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}


function REPORTE_ESTADISTICA_VENTAS($desde_0,$hasta_0,$desde_1,$hasta_1){
	// SELECT VENTAS_FN_REPORTE_ESTADISTICA_VENTAS ('2005-03-01','2005-03-31','2005-04-01','2005-04-30','ret');
	
	$q = "select VENTAS_FN_REPORTE_ESTADISTICA_VENTAS
	 (to_date('$desde_0','dd/mm/yyyy'),to_date('$hasta_0','dd/mm/yyyy')
	 ,to_date('$desde_1','dd/mm/yyyy'),to_date('$hasta_1','dd/mm/yyyy'),'ret')";
	echo $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}


#SELECT  VENTAS_FN_REPORTE_REGISTRO_VENTAS_CONSOLIDADO_1(DESDE,HASTA,DETALLADO_RESUMIDO,CREDITO_CONTADO_AMBOS,DESC_VALES,RET);
function REPORTE_REGISTRO_VENTAS_CONSOLIDADO_1($desde,$hasta,$detallado_resumido,$credito_contado_ambos,$desc_vales){
	// SELECT VENTAS_FN_REPORTE_ESTADISTICA_VENTAS ('2005-03-01','2005-03-31','2005-04-01','2005-04-30','ret');
	
	$q = "select VENTAS_FN_REPORTE_REGISTRO_VENTAS_CONSOLIDADO_1
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,'$detallado_resumido','$credito_contado_ambos','$desc_vales','ret')";
	print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}

function REPORTE_REGISTRO_VENTAS_CONSOLIDADO_2($desde,$hasta,$detallado_resumido,$credito_contado_ambos,$desc_vales){
	// SELECT VENTAS_FN_REPORTE_ESTADISTICA_VENTAS ('2005-03-01','2005-03-31','2005-04-01','2005-04-30','ret');
	
	$q = "select VENTAS_FN_REPORTE_REGISTRO_VENTAS_CONSOLIDADO_2
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,'$detallado_resumido','$credito_contado_ambos','$desc_vales','ret')";
	print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}


function REPORTE_CUADRE_FACTURACION_1($desde,$hasta,$opcion){
	//SELECT VENTAS_FN_REPORTE_CUADRE_FACTURACION_1(DESDE,HASTA,OPCION,RET)
	
	$sql = "SELECT VENTAS_FN_REPORTE_CUADRE_FACTURACION_1
	 						(to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy'),'$opcion','ret')";
	
	//echo $sql;
	pg_exec("begin");
	pg_exec($sql); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
return $rs;
}


function REPORTE_CUADRE_FACTURACION_2($desde,$hasta,$opcion){
	//SELECT VENTAS_FN_REPORTE_CUADRE_FACTURACION_2(DESDE,HASTA,OPCION,RET)
	
	$q = "SELECT VENTAS_FN_REPORTE_CUADRE_FACTURACION_2
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,'$opcion','ret')";
//	print $q;
	pg_exec("begin");
	//pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}

function ANALISIS_MOVIMIENTOS_CLIENTES($desde,$hasta){
	//select VENTAS_FN_ANALISIS_MOVIMIENTOS_CLIENTES(DESDE,HASTA,RET);
	
	$q = "SELECT VENTAS_FN_ANALISIS_MOVIMIENTOS_CLIENTES
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,'ret')";
	print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}



function REPORTE_REGISTRO_VENTAS_CLIENTES($desde,$hasta,$credito_contado,$estacion_oficina){
	//SELECT VENTAS_FN_REPORTE_REGISTRO_VENTAS_CLIENTES (DESDE,HASTA,'CREDITO_CONTADO_AMBOS','ret');
	
	$q = "SELECT VENTAS_FN_REPORTE_REGISTRO_VENTAS_CLIENTES
	 (to_date('$desde','dd/mm/yyyy'),to_date('$hasta','dd/mm/yyyy')
	 ,'$credito_contado'::character,'$estacion_oficina'::character,'ret'::refcursor)";
	//print $q;
	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");

return $rs;	
}
