<?php
if($boton=="Importar")
{
	header('Location: importador/explorer.php');
}

extract($_REQUEST);

//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

//require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$v_diad=$nuevofechad[0];
$v_mesd=$nuevofechad[1];
$v_anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$v_diaa=$nuevofechaa[0];
$v_mesa=$nuevofechaa[1];
$v_anoa=$nuevofechaa[2];

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}

$query = "SELECT trim(ch_almacen) AS cod,
                 ch_nombre_almacen 
          FROM inv_ta_almacenes  
          WHERE ch_clase_almacen='1' 
          ORDER BY cod";
$v_xsqlalma=pg_exec($query);

/*if($boton=="Consultar" or (strlen($diad)>0 and strlen($mesd)>0 and strlen($anod)>0
	and strlen($diaa)>0 and strlen($mesa)>0 and strlen($anoa)>0))
	{
        if($_REQUEST['m_almacen'] != "all")
	 {
	    $sqlalm = " AND com_cab_almacen = '".$_REQUEST['m_almacen']."'";
	 }		
	$sqladd=" AND com_cab_fechaorden BETWEEN '$fecini' AND '$fecfin' ".$sqlalm;
	
	
	$v_sql="SELECT com_cab_numorden
		FROM com_cabecera
		WHERE true ".$sqladd." ".$bddsql." ";
	echo "<!-- SQL3 : $v_sql-->\n";	
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_num_rows($v_xsql);
	if($v_ilimit>0) {$numeroRegistros=$v_ilimit;}
	}*/

if($boton=="Ins" or $boton=="Agregar") {
	echo('<script languaje="JavaScript">' );
	echo(" location.href='cmpr_ordencom_1.php' ");
	echo('</script>');
	}
if($boton=="Mod" or $boton=="Modificar") {
	if(strlen($m_clave)>0)
		{
		echo('<script languaje="JavaScript">');
		echo("	location.href='cmpr_ordencom_2.php?m_clave=".$m_clave."' " );
		echo('</script>');
		}
	else
		{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Debe seleccionar una Orden de Compra !!! ") ');
		echo('</script>');
		}
	}

if(strlen($diad)==0 or strlen($mesd)==0 or strlen($anod)==0 or strlen($anoa)==0 or strlen($mesa)==0 or strlen($diaa)==0) 
{
    $dia_actual = 1;
    $mes=date("m");
    $ano=date("Y");
    $fecini=date("Y-m")."-01";
    $ultimo_dia = ultimoDia($mes,$ano);
    $fecfin=date("Y-m")."-".$ultimo_dia;
    $diad="01";
    $dias = date("d");
    $mesd=$mes;
    $anod=$ano;
    $diaa=$ultimo_dia;
    $mesa=$mes;
    $anoa=$ano;
}else{
    $fecini=$anod."/".$mesd."/".$diad;
    $fecfin=$anoa."/".$mesa."/".$diaa;
}

if($boton=="Consultar" or (strlen($diad)>0 and strlen($mesd)>0 and strlen($anod)>0 and strlen($diaa)>0 and strlen($mesa)>0 and strlen($anoa)>0)) {
      if($_REQUEST['m_almacen'] != "all")
      {
	 $sqlalm = " AND com_cab_almacen = '".$_REQUEST['m_almacen']."'";
      }	
	$sqladd=" AND com_cab_fechaorden BETWEEN '$fecini' AND '$fecfin' ".$sqlalm;	
	$v_sql="SELECT com_cab_numorden
		FROM com_cabecera
		WHERE true ".$sqladd." ".$bddsql." ";
	//echo "<!-- SQL : $v_sql-->\n";
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_num_rows($v_xsql);
	if($v_ilimit>0) {
		$numeroRegistros=$v_ilimit;
	}
}

?>

<html><link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<head> <title>ACOSA</title>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script>
function AbrirPDFImpresion(codigo)
{
    window.open("/sistemaweb/compras/cmpr_ordencom_3.php?m_clave="+codigo+" ", "ventana", "resizable=yes, scrollbars=yes, height=500, width=700")
}
 
function activa(){
	// carga de frente el formulario con el foco en diad
	document.f_name.diad.select()
	document.f_name.diad.focus()
}
</script> 
</head>
<?php
if($boton=="Imprimir" or $boton=="Print")
{
if(strlen($m_clave)>0)
	{
	echo('<script languaje="JavaScript">');
	echo("AbrirPDFImpresion('".$m_clave."')");
	echo('</script>');
	}
else
	{
	echo('<script languaje="JavaScript"> ');
	echo('alert(" Debe seleccionar una Orden de Compra !!! ") ');
	echo('</script>');
	}
}


?>
<body> 
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<form name="f_name" action="" method="post">

ORDENES DE COMPRA DESDE <?php echo $diad.'/'.$mesd.'/'.$anod ; ?> HASTA <?php echo $diaa.'/'.$mesa.'/'.$anoa; ?> <BR>
<?php
// $v_sql="select  TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$almacen."%' ";
$v_sql="select ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$almacen."%' and ch_clase_almacen='1' ";
				
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$m_descalma=pg_result($v_xsql,0,0);	}
?>
ALMACEN ORIGEN <?php echo $almacen;?> 	<?php echo $m_descalma; ?> 

<hr noshade>

<input type="hidden" name="m_proveedor" value='<?php echo $m_proveedor;?>'>
<input type="hidden" name="m_tipdoc" value='<?php echo $m_tipdoc;?>'>
<input type="hidden" name="m_serie" value='<?php echo $m_serie;?>'>
<input type="hidden" name="m_orden" value='<?php echo $m_orden;?>'>
<input type="hidden" name="m_almacen" value='<?php echo $m_almacen;?>'>
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
		$v_xsqlesta=pg_query( "select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ESTO' and tab_elemento!='000000' order by TAB_ELEMENTO" );
		$v_var='CHECKED';
//		$lista=' ';
		for($i=0; $i<pg_numrows($v_xsqlesta);$i++)
		{
			$v_elemento=trim(pg_result($v_xsqlesta,$i,0));
			$v_descripcion=trim(pg_result($v_xsqlesta,$i,1));
			$var='v_estado'.$v_elemento;
		    if ($$var==$v_elemento)
	            {
			echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' $v_var > $v_descripcion ";
//				$lista=$lista.'&'.$var.'='.$$var;
		    }
		    elseif(!$_POST)
		    {
		       echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' $v_var> $v_descripcion ";
		    }
		    else
		    {
		       echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento'> $v_descripcion ";
		    }
		}

		$lista=' ';
		$v_xsqlesta=pg_query("select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ESTO' and tab_elemento!='000000' ");
		for($i=0; $i<pg_numrows($v_xsqlesta);$i++)
			{
			$v_elemento=trim(pg_result($v_xsqlesta,$i,0));
			$var='v_estado'.$v_elemento;
			if ($$var==$v_elemento) {
				$lista=$lista.'&'.$var.'='.$$var;
				}
		    else
				{$lista=$lista.'&'.$var.'= ';
				}
			}

//		echo "<br>".$lista;

		?>
		</th>

		<th><input type="submit" name="boton" value="Consultar"></th>
	</tr>
	<tr>
	 <th>ALMACEN : </th>
	 <td colspan="5" align="left">
        <select name="m_almacen" tabindex="2">
         <option value="all">Todos los Almacenes</option>
	 <?php
	 for($i=0;$i<pg_numrows($v_xsqlalma);$i++){		
		  $k_alma1 = pg_result($v_xsqlalma,$i,0);	
		  $k_alma2 = pg_result($v_xsqlalma,$i,1);
		  if (trim($k_alma1)==trim($m_almacen)) { 
			   echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
			   } 
		  else {
			   echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
			   }
		  }
	 ?>
        </select>	  
	 </td>
	</tr>
	</table>


<?php
$var_pers="diad=".$diad."&mesd=".$mesd."&anod=".$anod."&diaa=".$diaa."&mesa=".$mesa."&anoa=".$anoa.$lista."&m_almacen=".$_REQUEST['m_almacen'];
//$numeroRegistros=100;
//echo "<!--ILIMITRES : ".$ilimitres."-->\n";
include("../maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>
	<table border="1" cellpadding="0" cellspacing="0">
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="boton" value="Importar"></td>
			<td><input type="submit" name="boton" value="Agregar"></td>
			<td><input type="submit" name="boton" value="Modificar"></td>
			<td><input type="submit" name="boton" value="Imprimir"></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<th>ORDEN COMPRA</th>
			<th>ALMACEN</th>
			<th>PROVEEDOR</th>
			<th>NOMBRE</th>
			<th>FECHA</th>
			<th>MONEDA</th>
			<th>T.CAMBIO</th>
			<th>IMPORTE</th>
			<th>ESTADO</th>
			<th>Nro FACTURA</th>
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
		if($v_flag) { $v_cadena="'".$v_elemento."'"; $v_flag=false; } else {$v_cadena=$v_cadena.",'".$v_elemento."'"; }
		}
	}
if($_REQUEST['m_almacen'] != "all")
{
   $sqlalm = " AND com_cab_almacen = '".$_REQUEST['m_almacen']."'";
}
if ($v_cadena!='')
	{$sqladd= $sqlalm. " and CAB.COM_CAB_FECHAORDEN between '$fecini' and '$fecfin' and CAB.COM_CAB_ESTADO in ( $v_cadena ) ";}
else
	{
	$sqladd=$sqlalm. " and CAB.COM_CAB_FECHAORDEN between '$fecini' and '$fecfin' and false ";
	}
//echo $sqladd;
/*** SELECT MODIFICADO POR FRED
PARA QUE APARESCA EL NRO DE FACTURA AL FINAL****/
/* SELECT ORIGINAL
	$sqlres="select CAB.PRO_CODIGO||CAB.NUM_TIPDOCUMENTO||CAB.NUM_SERIEDOCUMENTO||CAB.COM_CAB_NUMORDEN as CLAVE,
				CAB.NUM_TIPDOCUMENTO,
				CAB.NUM_SERIEDOCUMENTO,
				CAB.COM_CAB_ALMACEN,
				CAB.COM_CAB_NUMORDEN,
				CAB.PRO_CODIGO,
				PRO.PRO_RAZSOCIAL,
				CAB.COM_CAB_FECHAORDEN,
				CAB.COM_CAB_MONEDA,
				CAB.COM_CAB_TIPCAMBIO,
				CAB.COM_CAB_IMPORDEN,
				CAB.COM_CAB_ESTADO
				from COM_CABECERA CAB, INT_PROVEEDORES PRO
				where CAB.PRO_CODIGO=PRO.PRO_CODIGO ".$sqladd."
				order by  COM_CAB_NUMORDEN, COM_CAB_ALMACEN
				".$bddsql."  ";*/

	$sqlres="select CAB.PRO_CODIGO||CAB.NUM_TIPDOCUMENTO||CAB.NUM_SERIEDOCUMENTO||CAB.COM_CAB_NUMORDEN as CLAVE,
				CAB.NUM_TIPDOCUMENTO,
				CAB.NUM_SERIEDOCUMENTO,
				CAB.COM_CAB_ALMACEN,
				CAB.COM_CAB_NUMORDEN,
				CAB.PRO_CODIGO,
				PRO.PRO_RAZSOCIAL,
				CAB.COM_CAB_FECHAORDEN,
				CAB.COM_CAB_MONEDA,
				CAB.COM_CAB_TIPCAMBIO,
				CAB.COM_CAB_IMPORDEN,
				CAB.COM_CAB_ESTADO,
				COM_SER,
				COM_FACTU
				from COM_CABECERA CAB, INT_PROVEEDORES PRO
				where CAB.PRO_CODIGO=PRO.PRO_CODIGO ".$sqladd."
				order by  COM_CAB_NUMORDEN, COM_CAB_ALMACEN
				".$bddsql."  ";
	//echo "<!--SQLRES : $sqlres -->";
/*** SELECT MODIFICADO POR FRED
PARA QUE APARESCA EL NRO DE FACTURA AL FINAL****/
$xsqlres=pg_exec($conector_id,$sqlres);
$ilimitres=pg_numrows($xsqlres);
if($ilimitres>0) {
	while($irowres<$ilimitres) {
		$a0=pg_result($xsqlres,$irowres,0);
		$a1=pg_result($xsqlres,$irowres,1);
		$a2=pg_result($xsqlres,$irowres,2);
		$a3=pg_result($xsqlres,$irowres,3);
		$a4=pg_result($xsqlres,$irowres,4);
		$a5=pg_result($xsqlres,$irowres,5);
		$a6=pg_result($xsqlres,$irowres,6);
		$a7=pg_result($xsqlres,$irowres,7);
		$a8=pg_result($xsqlres,$irowres,8);
		$a9=pg_result($xsqlres,$irowres,9);
		$a10=pg_result($xsqlres,$irowres,10);
		$a11=pg_result($xsqlres,$irowres,11);
/*Fred*/
		$a12=pg_result($xsqlres,$irowres,12);
		$a13=pg_result($xsqlres,$irowres,13);
/*Fred*/
		if($m_clave==$a0) {
			?>
			<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];">
			<?php
			echo "<td>&nbsp;<input type='radio' name='m_clave' value='".$a0."' checked></td>";
			}
		else {
			?>
			<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];">
			<td>&nbsp;<input type='radio' name='m_clave' value='<?php echo $a0 ?>' ></td>
			<?php
			}

		echo "<td>&nbsp;".$a4."</td>";
		// $v_sql="select  TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$a3."%' ";
		$v_sql="select ch_nombre_almacen from inv_ta_almacenes where ch_almacen like '%".$a3."%' and ch_clase_almacen='1' ";

		$v_xsql=pg_query($conector_id,$v_sql);
		if(pg_numrows($v_xsql)>0)	{	$m_descalma=pg_result($v_xsql,0,0);	}
//		echo "<td>&nbsp;".$a3."</td>";
		echo "<td>&nbsp;".$m_descalma."</td>";
		echo "<td>&nbsp;".$a5."</td>";
		echo "<td>&nbsp;".$a6."</td>";
		echo "<td>&nbsp;".$a7."</td>";
		$v_sql="SELECT  tab_elemento,
				tab_descripcion
			FROM int_tabla_general
			WHERE tab_tabla='MONE' 
			AND tab_elemento like '%".round($a8)."%' ";
		$v_xsql=pg_query($conector_id,$v_sql);
		if(pg_numrows($v_xsql)>0)	{	$m_descalma2=pg_result($v_xsql,0,1);	}
		echo "<!-- A8 : ".$a8."-->";
		echo "<td>&nbsp;".$m_descalma2."</td>";
		echo "<td>&nbsp;".$a9."</td>";
		echo "<td>&nbsp;".$a10."</td>";
		$v_sql="SELECT  tab_elemento,
			        tab_desc_breve
			FROM int_tabla_general
			WHERE tab_tabla='ESTO' 
			AND tab_elemento='$a11' ";
		$v_xsql=pg_query($conector_id,$v_sql);
		if(pg_numrows($v_xsql)>0)	{	$m_descalma=pg_result($v_xsql,0,1);	}
//		echo "<td><p align='right'>".$a11."</p></td>";
		echo "<td>&nbsp;".$m_descalma."</td>";
		echo "<td>&nbsp;".$a12." - ".$a13."</td>";

		$irowres++;
	}
}
?>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Importar"></td>
      <td><input type="submit" name="boton" value="Agregar"></td>
      <td><input type="submit" name="boton" value="Modificar"></td>
      <td><input type="submit" name="boton" value="Imprimir"></td>
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
//$clase_error->_error();

?>

