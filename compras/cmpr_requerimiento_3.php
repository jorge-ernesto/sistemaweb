<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

//require("../clases/funciones.php");

$v_funcion = new class_funciones;
$v_clase_error = new OpensoftError;
$v_conector_id=$v_funcion->conectar("","","","","");

$v_sql="select  NUM_TIPDOCUMENTO,
				NUM_SERIEDOCUMENTO,
				CH_REQ_NUMREQUERIMIENTO,
				ART_CODIGO,
				CH_REQ_ALMACEN,
				DT_REQ_FECHA_REQUERIDA,
				DT_REQ_FECHA_ATENCION,
				NU_REQ_CANTIDAD_REQUERIDA,
				NU_REQ_CANTIDAD_ATENDIDA,
				NU_REQ_VENTA_FECHA,
				NU_REQ_VENTA_MES_ACTUAL,
				NU_REQ_VENTA_MES_ANTERIOR,
				CH_REQ_ESTADO 
				from COM_TA_REQUERIMIENTOS 
				where NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||CH_REQ_NUMREQUERIMIENTO='".$v_clave."' " ;

$v_xsql=pg_query($v_conector_id,$v_sql);
$v_ilimit=pg_numrows($v_xsql);

$c_num_tipdocumento=pg_result($v_xsql,0,0);
$c_num_seriedocumento=pg_result($v_xsql,0,1);
$c_ch_req_numrequerimiento=pg_result($v_xsql,0,2);
$c_art_codigo=pg_result($v_xsql,0,3);
$c_ch_req_almacen=pg_result($v_xsql,0,4);
$c_dt_req_fecha_requerida=pg_result($v_xsql,0,5);
$c_dt_req_fecha_atencion=pg_result($v_xsql,0,6);
$c_nu_req_cantidad_requerida=pg_result($v_xsql,0,7);
$c_nu_req_cantidad_atendida=pg_result($v_xsql,0,8);
$c_nu_req_venta_fecha=pg_result($v_xsql,0,9);
$c_nu_req_venta_mes_actual=pg_result($v_xsql,0,10);
$c_nu_req_venta_mes_anterior=pg_result($v_xsql,0,11);
$c_ch_req_estado=pg_result($v_xsql,0,12);

$c_tab_descripcion=" ";
$c_tab_car_01=" ";
// $v_sql1="select  TAB_DESCRIPCION,TAB_CAR_01 from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO='$c_ch_req_almacen' ";
$v_sql1="select a.ch_nombre_almacen, s.ch_direccion from inv_ta_almacenes a, int_ta_sucursales s  where a.ch_sucursal=s.ch_sucursal and a.ch_almacen like '%".c_ch_req_almacen."%' and  a.ch_clase_almacen='1' ";

$v_xsql1=pg_exec($v_conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);
if($v_ilimit1>0) {$c_tab_descripcion=pg_result($v_xsql1,0,0); $c_tab_car_01=pg_result($v_xsql1,0,1); }

echo 'entro';
$v_funcion->v_ruta='/var/www/html/sistemaweb/compras/reporte.txt';
$v_funcion->impr_init();
$v_funcion->impr_line(' SISTEMA INTEGRADO            ');
$v_funcion->impr_line(' REQUERIMIENTO :'.$c_ch_req_numrequerimiento );
$v_funcion->impr_line(' FECHA REQUERIMIENTO :'.$c_dt_req_fecha_requerida );
$v_funcion->impr_line(' ALMACEN :'.$c_ch_req_almacen.' '.$c_tab_descripcion );
$v_funcion->impr_line(' DIRECCION DESTINO :'.$c_tab_car_01 );

$v_funcion->impr_line(' DETALLE DE REQUERIMIENTO ' );



$v_ilimit=pg_numrows($v_xsql);
while($irow<$v_ilimit) 
	{
	$c_art_codigo=pg_result($v_xsql,$irow,'art_codigo');
	$v_sql2="select ART_DESCRIPCION
					from INT_ARTICULOS 
					where ART_CODIGO='".$c_art_codigo."' " ;
	$v_xsql2=pg_exec($v_conector_id,$v_sql2);
	$v_ilimit2=pg_numrows($v_xsql2);
	if($v_ilimit2>0) {$c_descripcion=pg_result($v_xsql2,0,0); }
	
	$c_dt_req_fecha_requerida=pg_result($v_xsql,$irow,'dt_req_fecha_requerida');
	$c_dt_req_fecha_atencion=pg_result($v_xsql,$irow,'dt_req_fecha_atencion');
	$c_nu_req_cantidad_requerida=pg_result($v_xsql,$irow,'nu_req_cantidad_requerida');
	$c_nu_req_cantidad_atendida=pg_result($v_xsql,$irow,'nu_req_cantidad_atendida');
	$c_nu_req_venta_fecha=pg_result($v_xsql,$irow,'nu_req_venta_fecha');
	$c_nu_req_venta_mes_actual=pg_result($v_xsql,$irow,'nu_req_venta_mes_actual');
	$c_nu_req_venta_mes_anterior=pg_result($v_xsql,$irow,'nu_req_venta_mes_anterior');
	$c_ch_req_estado=pg_result($v_xsql,$irow,'ch_req_estado');

	$v_funcion->impr_line($c_art_codigo.$c_descripcion.$c_dt_req_fecha_requerida.$c_dt_req_fecha_atencion.
	$c_nu_req_cantidad_requerida.$c_nu_req_cantidad_atendida.$c_nu_req_venta_fecha.
	$c_nu_req_venta_mes_actual.$c_nu_req_venta_mes_anterior.$c_ch_req_estado );

	$irow++;
	}

$v_comando_imprime='smbclient //sistemasnew/epson -c "print /var/www/html/sistemaweb/compras/reporte.txt" -P -N -I 128.1.2.66';
exec( $v_comando_imprime );
	

echo('<script languaje="JavaScript">');
echo("	location.href='cmpr_requerimiento.php'; ");
echo('</script>');

