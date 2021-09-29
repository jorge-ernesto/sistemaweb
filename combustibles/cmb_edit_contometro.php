<?php
http_response_code(404);
die("Not Found");
//session_start();
include("../config.php");
//include("../combustibles/inc_top.php");
include("../functions.php");
include("../valida_sess.php");
$fecha = getdate();
$dia = $fecha['mday'];
$mes = $fecha['mon'];
$year = $fecha['year'];
$hoy = $dia.'-'.$mes.'-'.$year;
switch($action){
	/*para eliminar el ultimo numero de parte de los contometros se debe 
	primero regresar los surtidores al estadop anterior de haberse ingresado este numero de parte
	*/
	case "eliminar_ultimo":
	$rs1 = pg_exec("select max(ch_numeroparte) as parte,to_char(dt_fechaparte,'dd-mm-yyyy') as fecha
	,trim(ch_sucursal), ch_codigocombustible 
	from comb_ta_contometros where ch_sucursal=trim('$cod_almacen')
	group by ch_numeroparte,dt_fechaparte,ch_sucursal,ch_codigocombustible order by parte desc");
	
	if(pg_numrows($rs1)>0){//ERROR COMETIDO POR FRED 
	$A = pg_fetch_array($rs1,0);
	$last_num_parte = $A[0];
	$fecha_last_parte = $A[1];
	$F = cortarCadena2($fecha_last_parte,"-");
	$periodo_last_parte = $F[2];
	$mes_last_parte = $F[1];
	$sucursal_last_parte = $A[2];
	$combustible_last_parte = $A[3];
	$rs2 = pg_exec("select cont.nu_contometroinicialgalon as galon, cont.nu_contometroinicialvalor as valor
	, cont.ch_surtidor as surtidor, cont.ch_tanque as tanque
	FROM comb_ta_contometros cont
	WHERE cont.ch_numeroparte = '$last_num_parte' and ch_sucursal=trim('$cod_almacen') ");
	
	
	for($i=0;$i<pg_numrows($rs2);$i++){
		$Q = pg_fetch_row($rs2,$i);
		pg_exec(" update comb_ta_surtidores set nu_contometrogalon='$Q[0]' , nu_contomtrovalor='$Q[1]'  
		where ch_surtidor='$Q[2]' and ch_sucursal=trim('$cod_almacen') ");
	}
	
	$rs3 = pg_exec("select art_codigo,mov_cantidad,to_char(mov_fecha,'dd-mm-yyyy') as fecha  
	from inv_movialma 
	where to_char(mov_fecha,'dd-mm-yyyy')='$fecha_last_parte' and tran_codigo='25' 
	and mov_numero='$last_num_parte' and mov_almacen='$sucursal_last_parte' ");
	
	$stk_stock = "stk_stock".$mes_last_parte;
	
	for($a=0;$a<pg_numrows($rs3);$a++){
		$R = pg_fetch_row($rs3,$a);
		pg_exec(" update inv_saldoalma set $stk_stock=$stk_stock+$R[1] 
		where stk_almacen='$sucursal_last_parte'  
		and stk_periodo='$periodo_last_parte' and art_codigo='$combustible_last_parte' ");
	}
	//para borrar el movimiento de inventario
	pg_exec("delete from inv_movialma where to_char(mov_fecha,'dd-mm-yyyy')='$fecha_last_parte' 
	and tran_codigo='25' and mov_numero='$last_num_parte' and  mov_almacen='$cod_almacen' ");
	//para borrar el movimiento de inventario en la afericion
	pg_exec("delete from inv_movialma where to_char(mov_fecha,'dd-mm-yyyy')='$fecha_last_parte' 
	and tran_codigo='23' and mov_numero='$last_num_parte' and  mov_almacen='$cod_almacen' ");
	//para borrar el movimiento de inventario en el consumo 
	pg_exec("delete from inv_movialma where to_char(mov_fecha,'dd-mm-yyyy')='$fecha_last_parte' 
	and tran_codigo='24' or tran_codigo='26' and mov_numero='$last_num_parte' 
	and  mov_almacen='$cod_almacen' ");
	
	//se borra finalmente del contometros
	pg_exec(" delete from comb_ta_contometros where ch_numeroparte='$last_num_parte' 
	and ch_sucursal=trim('$cod_almacen')");
		
	pg_close();
	
	
	}//FIN DEL ERROR COMETIDO POR FRED
	header("location: cmb_contometro.php?cod_almacen=$cod_almacen");
	break;
}
