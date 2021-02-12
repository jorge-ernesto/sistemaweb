<?php
//include("../valida_sess.php");
//include("inc_top_compras.php");
include "../menu_princ.php";
include("../functions.php");


//require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}

// carga de nuevo la clase con el control de errores
// $clase_error->error();

// Este reporte se basa en el reporte de Stocks Consolidados que existe en Inventarios (10/03/2004)

// Considerar Combustibles S/N 			Siempre es N
// Considerar Oficina Principal S/N 	Siempre es N
// Almacen 999 							Siempre es todos
// Solo Saldos Iniciales S/N			Siempre es N
// Linea 99								Input -> Linea del Producto
// Fecha Stock							Input -> Fecha Actualizacion (es referencia al dia)
// Unidades/Valores						Siempre es Unidades
// Dias Venta							Input -> Dias atras Calcula la fecha Inicial
// Bajo Stock Minimo					Siempre es N
// Sobre Stock Maximo					Siempre es N
// Stocks Negativos						Siempre es N
// Stock Cero Actual S/N				Siempre es N
// Stock c/Costo<=0						Siempre es N
// Detallado/Resumido					Siempre es D

if (is_null($v_almacen) )
	{ $v_almacen=''; }
if (is_null($v_linea) )
	{ $v_linea=''; }
if (is_null($v_fecha_proceso) )
	{ $v_fecha_proceso=date('d/m/Y'); }
if (is_null($v_fecha_stock) )
	{ $v_fecha_stock=date('d/m/Y'); }
if (is_null($v_fecha_inicial) )
	{ $v_fecha_inicial=date('d/m/Y'); }
if (is_null($v_fecha_final) )
	{ $v_fecha_final=date('d/m/Y'); }

$v_dias_venta = 0;


// carga los almacenes en un dropdown 
// $v_xsqlalma=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='ALMA' and tab_elemento!='000000' and tab_car_02='1' order by cod");
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

if($boton=="Regresar") 
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_stock_consol_list.php' ");
	echo('</script>');
	}


	// captura stock a fecha solicitada
	// todas las fechas en dd/mm/yyyy solo se cambian al grabar en la bd
	// para evitar error del formato fecha

	// buscar en la tabla com_ta_areq_cabecera (aprobacion requerimiento haber si estan generados con la misma fecha de proceso)
	if (strlen($v_linea)==0 and strlen($v_almacen)==0)
		{
		$v_sqlareq_cab="select * from COM_TA_AREQ_CABECERA
						where DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						order by CH_AREQ_LINEA, CH_AREQ_ALMACEN ";
		
		$v_sqlareq_det="select DET.*,ART.ART_DESCBREVE,ART.ART_DESCRIPCION from COM_TA_AREQ_DETALLE DET, INT_ARTICULOS ART
						where DET.CH_AREQ_ARTICULO=ART.ART_CODIGO 
						and DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						order by CH_AREQ_LINEA, CH_AREQ_ARTICULO, CH_AREQ_ALMACEN ";
		}
	if (strlen($v_linea)>0 and strlen($v_almacen)==0)
		{
		$v_sqlareq_cab="select * from COM_TA_AREQ_CABECERA 
						where DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						and CH_AREQ_LINEA='$v_linea' 
						order by CH_AREQ_ALMACEN ";

		$v_sqlareq_det="select DET.*,ART.ART_DESCBREVE,ART.ART_DESCRIPCION from COM_TA_AREQ_DETALLE DET, INT_ARTICULOS ART
						where DET.CH_AREQ_ARTICULO=ART.ART_CODIGO 
						and DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						and CH_AREQ_LINEA='$v_linea' 
						order by CH_AREQ_ARTICULO, CH_AREQ_ALMACEN ";
		}
	if (strlen($v_linea)==0 and strlen($v_almacen)>0)
		{
		$v_sqlareq_cab="select * from COM_TA_AREQ_CABECERA 
						where DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						and CH_AREQ_ALMACEN='$v_almacen' 
						order by CH_AREQ_LINEA
						";

		$v_sqlareq_det="select DET.*,ART.ART_DESCBREVE,ART.ART_DESCRIPCION from COM_TA_AREQ_DETALLE DET, INT_ARTICULOS ART
						where DET.CH_AREQ_ARTICULO=ART.ART_CODIGO
						and DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						and CH_AREQ_ALMACEN='$v_almacen' 
						order by CH_AREQ_LINEA, CH_AREQ_ARTICULO 
						";
		}
	if (strlen($v_linea)>0 and strlen($v_almacen)>0)
		{
		$v_sqlareq_cab="select * from COM_TA_AREQ_CABECERA 
						where DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						and CH_AREQ_ALMACEN='$v_almacen' 
						and CH_AREQ_LINEA='$v_linea' 
						";

		$v_sqlareq_det="select DET.*,ART.ART_DESCBREVE,ART.ART_DESCRIPCION from COM_TA_AREQ_DETALLE DET, INT_ARTICULOS ART
						where DET.CH_AREQ_ARTICULO=ART.ART_CODIGO
						and DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
						and CH_AREQ_ALMACEN='$v_almacen' 
						and CH_AREQ_LINEA='$v_linea' 
						order by CH_AREQ_ARTICULO 
						";
		}
	
	//verifica que no exista en los archivos ya generados
	$v_xsqlareq_cab=pg_exec($conector_id,$v_sqlareq_cab);
	
	if (pg_numrows($v_xsqlareq_cab)>0)
		{
		$v_fecha_inicial= $funcion->date_format( pg_result($v_xsqlareq_cab,0,'DT_AREQ_FECHA_VENTA_INICIAL'), 'DD/MM/YYYY');
		$v_fecha_final  = $funcion->date_format( pg_result($v_xsqlareq_cab,0,'DT_AREQ_FECHA_VENTA_FINAL'), 'DD/MM/YYYY');
		$v_fecha_stock  = $funcion->date_format( pg_result($v_xsqlareq_cab,0,'DT_AREQ_FECHA_STOCK'), 'DD/MM/YYYY');

		// si existe en cab entonces recien carga det
		$v_xsqlareq_det=pg_exec($conector_id,$v_sqlareq_det);

		if(strlen($v_almacen)>0) 
			{
			// $v_sql_alma="select tab_elemento, tab_desc_breve from int_tabla_general where tab_tabla='ALMA' and tab_elemento='$v_almacen' and tab_car_02='1' order by tab_elemento ";
			$v_sql_alma="select trim(ch_almacen) as cod, ch_nombre_almacen from inv_ta_almacenes where trim(ch_almacen)=trim('$v_almacen') and ch_clase_almacen='1' order by cod ";
			}
		else
			{
			// $v_sql_alma="select tab_elemento, tab_desc_breve from int_tabla_general where tab_tabla='ALMA' and tab_car_02='1' order by tab_elemento ";
			$v_sql_alma="select trim(ch_almacen) as cod, ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' order by cod ";			
			}
		$v_xsql_alma=pg_exec($conector_id, $v_sql_alma);
		}

	// Proceso para cargar en una tabla venta y stock por la linea que se ha pedido y de acuerdo 
	// a los parametros descritos
	// Procesa el Reporte al Final y Luego Reprocesa las nuevas modificaciones
	// Boton para Procesar
?>	
<html>
<link rel="stylesheet" href="<?php echo $v_path_url; ?>jch.css" type="text/css"> 
<head>
<title>sistemaweb</title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
</head>

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_name">
<?php 
// echo $v_sqlareq_cab;
// echo $v_sqlareq_det;

include("../compras/cmpr_stock_consol_diseno3.php");?>
</form>
</body>
</html>

<?php

// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();

