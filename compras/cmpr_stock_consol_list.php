<?php
//include("../valida_sess.php");
//include("inc_top_compras.php");
include "../menu_princ.php";
include("../functions.php");
//include("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)==""){
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
if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) ){
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}

if($boton=="Consultar" or (strlen($v_fecha_desde)>0 and strlen($v_fecha_hasta)>0) ) {
	$v_sql="select  DT_AREQ_FECHA_PROCESO,
					CH_AREQ_LINEA,
					DT_AREQ_FECHA_STOCK,
					DT_AREQ_FECHA_VENTA_INICIAL,
					DT_AREQ_FECHA_VENTA_FINAL
					from COM_TA_AREQ_CABECERA 
					where DT_AREQ_FECHA_PROCESO between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' 
					group by DT_AREQ_FECHA_PROCESO,
					CH_AREQ_LINEA,
					DT_AREQ_FECHA_STOCK,
					DT_AREQ_FECHA_VENTA_INICIAL,
					DT_AREQ_FECHA_VENTA_FINAL
					".$bddsql."  ";
	
	$v_xsql=pg_exec($conector_id,$v_sql);
	$v_ilimit=pg_numrows($v_xsql);
	if($v_ilimit>0) {
		$numeroRegistros=$v_ilimit;
	}
}

	
if($boton=="Ins" or $boton=="Agregar") {
	echo('<script languaje="JavaScript">' );
	echo(" location.href='cmpr_stock_consol_agrega.php' ");
	echo('</script>');
	}
		
if($boton=="Ver" ) {
	$v_fecha_proceso=$funcion->date_format( substr($v_clave,0,10) ,'DD/MM/YYYY');
	$v_linea=substr($v_clave,10,6);	
	$v_almacen='';	
	echo('<script languaje="JavaScript">' );
	echo(" location.href='cmpr_stock_consol_modif.php?v_fecha_proceso=".$v_fecha_proceso."&v_linea=".$v_linea." ' ");
	echo('</script>');
	}	

?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<title>sistemaweb</title>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_name" action="" method="post">
APROBACIONES DESDE <?php echo $v_fecha_desde; ?> HASTA <?php echo $v_fecha_hasta; ?> <BR>

<?php
// $v_sql="select  TAB_ELEMENTO,TAB_DESCRIPCION from INT_TABLA_GENERAL	where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$almacen."%' ";
$v_sql="select ch_almacen, ch_nombre_almacen from inv_ta_almacenes	where ch_almacen like '%".$almacen."%' ";
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_descalma=pg_result($v_xsql,0,1);	}
?>
ALMACEN ORIGEN <?php echo $almacen; ?> - <?php echo $v_descalma; ?> 
<hr noshade>

<table border="1">
	<tr> 
		<th colspan="7">CONSULTAR POR RANGO DE FECHAS </th>
	</tr>
	<tr> 
		<th>DESDE :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_name.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</th>
			
		<th>HASTA:</th>
		<th>
		<p>
		<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_name.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</th>


		
		<th><input type="submit" name="boton" value="Consultar">
		</th>
	</tr>
</table>

<?php

$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta;

include("../maestros/pagina.php");

$bddsql=" limit $tamPag offset $limitInf ";

?>

	<table border="1" cellpadding="0" cellspacing="0">
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="boton" value="Agregar"></td>
			<td><input type="submit" name="boton" value="Eliminar"></td>
			<td><input type="submit" name="boton" value="Ver"></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
		<th>&nbsp;</th>
		<th>FECHA PROC</th>
		<th>LINEA</th>
		<th>FECHA STK</th>
		<th>FECHA VTA INI</th>
		<th>FECHA VTA FIN</th>
		</tr>
<?php

$v_sql="select  DT_AREQ_FECHA_PROCESO,
				CH_AREQ_LINEA,
				DT_AREQ_FECHA_STOCK,
				DT_AREQ_FECHA_VENTA_INICIAL,
				DT_AREQ_FECHA_VENTA_FINAL
				from COM_TA_AREQ_CABECERA 
 				where DT_AREQ_FECHA_PROCESO between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' 
				order by  DT_AREQ_FECHA_PROCESO
				".$bddsql."  ";
$v_sql="select  DT_AREQ_FECHA_PROCESO,
				CH_AREQ_LINEA,
				DT_AREQ_FECHA_STOCK,
				DT_AREQ_FECHA_VENTA_INICIAL,
				DT_AREQ_FECHA_VENTA_FINAL
				from COM_TA_AREQ_CABECERA 
				where DT_AREQ_FECHA_PROCESO between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' 
				group by DT_AREQ_FECHA_PROCESO,
				CH_AREQ_LINEA,
				DT_AREQ_FECHA_STOCK,
				DT_AREQ_FECHA_VENTA_INICIAL,
				DT_AREQ_FECHA_VENTA_FINAL
				".$bddsql."  ";


$v_xsql=pg_exec($conector_id,$v_sql);
$v_ilimit=pg_numrows($v_xsql);
if($v_ilimit>0) {
	while($v_irow<$v_ilimit) {
		$a0=pg_result($v_xsql,$v_irow,0);
		$a1=pg_result($v_xsql,$v_irow,1);
		$a2=pg_result($v_xsql,$v_irow,2);
		$a3=pg_result($v_xsql,$v_irow,3);
		$a4=pg_result($v_xsql,$v_irow,4);
		$m_clave=$a0.$a1;

		if($v_clave==$a0) {
			?>
			<tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];">
			<?php
			echo "<td>&nbsp;<input type='radio' name='v_clave' value='".$m_clave."' checked></td>";
			}
		else {
			?>
			<tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];">
			<td>&nbsp;<input type='radio' name='v_clave' value='<?php echo $m_clave; ?>' ></td>
			<?php
			}
		echo "<td>&nbsp;".$funcion->date_format($a0,'DD/MM/YYYY')."</td>";
		$v_sql2="select  TAB_ELEMENTO,
						TAB_DESC_BREVE
						from INT_TABLA_GENERAL
						where TAB_TABLA='20' and TAB_ELEMENTO like '%".$a1."%' ";
		$v_xsql2=pg_query($conector_id,$v_sql2);
		if(pg_numrows($v_xsql2)>0)	{	$v_descripcion=pg_result($v_xsql2,0,1);	}
		echo "<td>&nbsp;".$a1."-".$v_descripcion."</td>";
		echo "<td>&nbsp;".$funcion->date_format($a2,'DD/MM/YYYY')."</td>";
		echo "<td>&nbsp;".$funcion->date_format($a3,'DD/MM/YYYY')."</td>";
		echo "<td>&nbsp;".$funcion->date_format($a4,'DD/MM/YYYY')."</td>";
		$v_irow++;
	}
}
?>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="boton" value="Agregar"></td>
			<td><input type="submit" name="boton" value="Eliminar"></td>
			<td><input type="submit" name="boton" value="Ver"></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
</form>
</body>
</html>
<?php

// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();

