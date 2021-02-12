<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

//require("../clases/funciones.php");
$funcion = new class_funciones;

/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$v_diad=$nuevofechad[0];
$v_mesd=$nuevofechad[1];
$v_anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$v_diaa=$nuevofechaa[0];
$v_mesa=$nuevofechaa[1];
$v_anoa=$nuevofechaa[2];
// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)==""){
	$almacen="001";
}

if($boton=="Consultar" or (strlen($v_diad)>0 and strlen($v_mesd)>0 and strlen($v_anod)>0 and strlen($v_diaa)>0 and strlen($v_mesa)>0 and strlen($v_anoa)>0)) {
	$v_sql="select CH_REQ_NUMREQUERIMIENTO
				from COM_TA_REQUERIMIENTOS
				where true ".$sqladd." ".$bddsql." ";
	$v_xsql=pg_query($conector_id,$v_sql);
	$numeroRegistros=pg_num_rows($v_xsql);
}

if($boton=="Ins" or $boton=="Agregar") {
	echo('<script languaje="JavaScript">' );
	echo("	location.href='cmpr_requerimiento_1.php' ");
	echo('</script>');
	}
if($boton=="Mod" or $boton=="Modificar") {
	if(strlen($v_clave)>0)
		{
		echo('<script languaje="JavaScript">');
		echo("	location.href='cmpr_requerimiento_2.php?v_clave=".$v_clave."' " );
		echo('</script>');
		}
	else
		{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Debe seleccionar Un Requerimiento!!! ") ');
		echo('</script>');
		}
	}
if($boton=="Imprimir" or $boton=="Print"){
	if(strlen($v_clave)>0)
		{
		//echo 'clave es: '.$v_clave;
		echo('<script languaje="JavaScript">');
		echo("	location.href='cmpr_requerimiento_3.php?v_clave=".$v_clave."' " );
		echo('</script>');
		}
	else
		{
		echo('<script languaje="JavaScript"> ');
		echo('	alert(" Debe seleccionar Un Requerimiento!!! ") ');
		echo('</script>');
		}
	}


if(strlen($v_diad)==0 or strlen($v_mesd)==0 or strlen($v_anod)==0 or
   strlen($v_anoa)==0 or strlen($v_mesa)==0 or strlen($v_diaa)==0)
	{
	$dia_actual=1;
	$v_mes=date("m");
	$v_ano=date("Y");
	$v_fecini=date("Y-m")."-01";
	$v_ultimo_dia = ultimoDia($v_mes,$v_ano);
	$v_fecfin=date("Y-m")."-".$v_ultimo_dia;
	$v_diad="01";
	$v_mesd=$v_mes;
	$v_anod=$v_ano;
	$v_diaa=$v_ultimo_dia;
	$v_mesa=$v_mes;
	$v_anoa=$v_ano;
	}
else
	{
	$v_fecini=$v_anod."/".$v_mesd."/".$v_diad;
	$v_fecfin=$v_anoa."/".$v_mesa."/".$v_diaa;
	}
if($boton=="Consultar" or ( strlen($v_diad)>0 and strlen($v_mesd)>0 and strlen($v_anod)>0 and strlen($v_diaa)>0 and strlen($v_mesa)>0 and strlen($v_anoa)>0) )
	{
	$sqladd=" and DT_REQ_FECHA_REQUERIDA between '$v_fecini' and '$v_fecfin' ";
	$v_sql="select CH_REQ_NUMREQUERIMIENTO
				from COM_TA_REQUERIMIENTOS
				where true ".$sqladd." ".$bddsql." ";
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_num_rows($v_xsql);
	if($v_ilimit>0) {
		$numeroRegistros=$v_ilimit;
	}
}
?>
<html><link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script>
function activa(){
	document.f_name.v_diad.select()
	document.f_name.v_diad.focus()
}
</script>
</head>
<body>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<form name="f_name" action="" method="post">

REQUERIMIENTOS DESDE <?php echo $v_diad.'/'.$v_mesd.'/'.$v_anod ; ?> HASTA <?php echo $v_diaa.'/'.$v_mesa.'/'.$v_anoa; ?> <BR>
<?php
// $v_sql="select  TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$almacen."%' ";
$v_sql="select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$almacen."%' and  ch_clase_almacen='1' ";
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_descalma=pg_result($v_xsql,0,1);	}
?>
ALMACEN ORIGEN <?php echo $almacen;?> - <?php echo $v_descalma; ?>
<hr noshade>
<input type="hidden" name="v_tipdocumento" value='<?php echo $v_tipdocumento;?>'>
<input type="hidden" name="v_seriedocumento" value='<?php echo $v_seriedocumento;?>'>
<input type="hidden" name="v_numrequerimiento" value='<?php echo $v_numrequerimiento;?>'>
<table border="1">
	<tr>
		<th colspan="5">CONSULTAR POR RANGO DE FECHAS </th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th><input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('f_name.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></th>
		<th>HASTA:</th>
		<th><input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('f_name.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></th>
		<th> 
		<?php 
		$v_xsqlesta=pg_query("select TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ESTR' and TAB_ELEMENTO!='000000' order by TAB_ELEMENTO");
		$v_var='CHECKED';
		for($i=0; $i<pg_numrows($v_xsqlesta);$i++){
			$v_elemento=trim(pg_result($v_xsqlesta,$i,0));
			$v_descripcion=trim(pg_result($v_xsqlesta,$i,1));
			$var='v_estado'.$v_elemento;
		        if ($$var==$v_elemento){
				echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' $v_var > $v_descripcion ";
			}else{
				if (!$_REQUEST['v_diad']) echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' $v_var > $v_descripcion ";
				else echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' > $v_descripcion ";
			}
		}
		$lista=' ';
		$v_xsqlesta=pg_query("select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ESTR' and tab_elemento!='000000' ");
		for($i=0; $i<pg_numrows($v_xsqlesta);$i++){
			$v_elemento=trim(pg_result($v_xsqlesta,$i,0));
			$var='v_estado'.$v_elemento;
			if ($$var==$v_elemento) {$lista=$lista.'&'.$var.'='.$$var; }
		    else{$lista=$lista.'&'.$var.'= '; }
		}
		?>
		</th>
		<th><input type="submit" name="boton" value="Consultar"></th>
	</tr>
	</table>
<?php
$var_pers="v_diad=".$v_diad."&v_mesd=".$v_mesd."&v_anod=".$v_anod."&v_diaa=".$v_diaa."&v_mesa=".$v_mesa."&v_anoa=".$v_anoa.$lista;

//$numeroRegistros=100;
include("/sistemaweb/maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>
	<table border="1" cellpadding="0" cellspacing="0">
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="boton" value="Agregar"></td>
			<td><input type="submit" name="boton" value="Modificar"></td>
	        <td><input type="submit" name="boton" value="Imprimir"></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
		<th>&nbsp;</th>
		<th>REQUERIM</th>
		<th>ALMACEN DES</th>
		<th>FECHA REQ</th>
		<th>FECHA ATE</th>
		<th>ARTICULO</th>
		<th>CANTIDAD REQ</th>
		<th>CANTIDAD ATE</th>
		<th>VTA FECHA</th>
		<th>VTA MES ACT</th>
		<th>VTA MES ANT</th>
		<th>CANT STOCK</th>
		<th>ESTADO</th>
		</tr>
<?php
$v_cadena='';
$v_flag=true;
for($i=0; $i<pg_numrows($v_xsqlesta);$i++)
	{
	$v_elemento=trim(pg_result($v_xsqlesta,$i,0));
	$var='v_estado'.$v_elemento;
    if ($$var==$v_elemento)
		{
		if($v_flag) { $v_cadena=$v_elemento; $v_flag=false; } else {$v_cadena=$v_cadena.','.$v_elemento; }
		}
	}
if ($v_cadena!='')
	{$sqladd=" and REQ.DT_REQ_FECHA_REQUERIDA between '$v_fecini' and '$v_fecfin' and to_number(REQ.CH_REQ_ESTADO,'0') in ( $v_cadena ) ";}
else 
	{$sqladd=" and REQ.DT_REQ_FECHA_REQUERIDA between '$v_fecini' and '$v_fecfin' and false ";}

//echo $sqladd;

$v_sql="select  REQ.NUM_TIPDOCUMENTO||REQ.NUM_SERIEDOCUMENTO||REQ.CH_REQ_NUMREQUERIMIENTO as CLAVE,
				REQ.NUM_TIPDOCUMENTO,
				REQ.NUM_SERIEDOCUMENTO,
				REQ.CH_REQ_NUMREQUERIMIENTO,
				REQ.CH_REQ_ALMACEN,
				REQ.DT_REQ_FECHA_REQUERIDA,
				REQ.DT_REQ_FECHA_ATENCION,
				ART.ART_DESCRIPCION,
				REQ.NU_REQ_CANTIDAD_REQUERIDA,
				REQ.NU_REQ_CANTIDAD_ATENDIDA,
				REQ.NU_REQ_VENTA_FECHA,
				REQ.NU_REQ_VENTA_MES_ACTUAL,
				REQ.NU_REQ_VENTA_MES_ANTERIOR,
				REQ.NU_REQ_CANTIDAD_STOCK,
				REQ.CH_REQ_ESTADO
				from COM_TA_REQUERIMIENTOS REQ, INT_ARTICULOS ART
				where REQ.ART_CODIGO=ART.ART_CODIGO ".$sqladd."
				order by  REQ.CH_REQ_NUMREQUERIMIENTO
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
		$a5=pg_result($v_xsql,$v_irow,5);
		$a6=pg_result($v_xsql,$v_irow,6);
		$a7=pg_result($v_xsql,$v_irow,7);
		$a8=pg_result($v_xsql,$v_irow,8);
		$a9=pg_result($v_xsql,$v_irow,9);
		$a10=pg_result($v_xsql,$v_irow,10);
		$a11=pg_result($v_xsql,$v_irow,11);
		$a12=pg_result($v_xsql,$v_irow,12);
		$a13=pg_result($v_xsql,$v_irow,13);
		$a14=pg_result($v_xsql,$v_irow,14);
		if($v_clave==$a0) {
			?>
			<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];">
			<?php
			echo "<td>&nbsp;<input type='radio' name='v_clave' value='".$a0."' checked></td>";
			}
		else {
			?>
			<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];">
			<td>&nbsp;<input type='radio' name='v_clave' value='<?php echo $a0 ?>' ></td>
			<?php
			}

		echo "<td>&nbsp;".$a3."</td>";
		// $v_sql2="select  TAB_ELEMENTO, TAB_DESC_BREVE from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$a4."%' ";
		$v_sql2="select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$a4."%' and  ch_clase_almacen='1' ";
		$v_xsql2=pg_query($conector_id,$v_sql2);
		if(pg_numrows($v_xsql2)>0)	{	$v_descripcion=pg_result($v_xsql2,0,1);	}
		echo "<td>&nbsp;".$v_descripcion."</td>";
		echo "<td>&nbsp;".$funcion->date_format($a5,',')."</td>";
		echo "<td>&nbsp;".$funcion->date_format($a6,',')."</td>";
		echo "<td>&nbsp;".$a7."</td>";
		echo "<td><p align='right'>".number_format( $a8, 2). "</p></td>";
		echo "<td><p align='right'>".number_format( $a9, 2). "</p></td>";
		echo "<td><p align='right'>".number_format( $a10, 2)."</p></td>";
		echo "<td><p align='right'>".number_format( $a11, 2)."</p></td>";
		echo "<td><p align='right'>".number_format( $a12, 2)."</p></td>";
		echo "<td><p align='right'>".number_format( $a13, 2)."</p></td>";
		$v_sql3="select  TAB_ELEMENTO,
						TAB_DESC_BREVE
						from INT_TABLA_GENERAL
						where TAB_TABLA='ESTR' and TAB_ELEMENTO='$a14' ";
		$v_xsql3=pg_query($conector_id,$v_sql3);
		if(pg_numrows($v_xsql3)>0)	{	$v_descripcion=pg_result($v_xsql3,0,1);	}
		echo "<td>&nbsp;".$v_descripcion."</td>";
		$v_irow++;
	}
}
?>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Agregar"></td>
      <td><input type="submit" name="boton" value="Modificar"></td>
      <td><input type="submit" name="boton" value="Imprimir"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
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

