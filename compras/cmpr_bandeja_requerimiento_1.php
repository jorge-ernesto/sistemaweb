<?php
include("../valida_sess.php");
//include("../menu_princ.php");
include("../config.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name2" method="post" >
<input type="hidden" name="v_fecha_desde" value="<?php echo $v_fecha_desde;?>">
<input type="hidden" name="v_fecha_hasta" value="<?php echo $v_fecha_hasta;?>">
<input type="hidden" name="v_almacen" value="<?php echo $v_almacen;?>">
<?php 
$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta."&v_almacen=".$v_almacen;
include("../maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
if ( trim($v_almacen)==""){
?>
<table border="1" cellpadding="0" cellspacing="0">
	<tr><td >&nbsp;</td>
		<td >&nbsp;</td>
		<td>&nbsp;</td>
        <td><input type="submit" name="boton" value="Print"></td></tr>  
	<tr><td >&nbsp;</td>
		<th>CODIGO</th>
		<th>NOMBRE</th>
		<th>CANTIDAD</th></tr>
<?php
	}
else{
?>
<table border="1" cellpadding="0" cellspacing="0">
	<tr><td >&nbsp;</td>
		<td>&nbsp;</td>
        <td><input type="submit" name="boton" value="Print"></td></tr>  
	<tr><th>CODIGO</th>
		<th>NOMBRE</th>
		<th>CANTIDAD</th></tr>
<?php
	}



// LA IDEA GENERAL SERIA ESTO
$sqlres="select REQ.NUM_TIPDOCUMENTO||REQ.NUM_SERIEDOCUMENTO||REQ.CH_REQ_NUMQUERIMIENTO as CLAVE,
				REQ.NUM_TIPDOCUMENTO,
				REQ.NUM_SERIEDOCUMENTO,
				REQ.CH_REQ_NUMREQUERIMIENTO,
				ART.ART_CODIGO,
				ART.ART_DESCRIPCION,
				REQ.DT_REQ_FECHA_REQUERIDA,
				REQ.CH_REQ_ALMACEN,
				REQ.NU_REQ_CANTIDAD_REQUERIDA,
				REQ.CH_REQ_ESTADO 
				from COM_TA_REQUERIMIENTOS REQ, INT_ARTICULOS ART 
				where REQ.ART_CODIGO = ART.ART_CODIGO ".$sqladd." 
				group by REQ.ART_CODIGO 
				order by REQ.DT_REQ_FECHA_REQUERIDA, ART.ART_CODIGO 
				".$bddsql."  ";

if ( trim($v_almacen)=="")
	{
	$v_sql1="select REQ.ART_CODIGO, 
				ART.ART_DESCRIPCION, 
				SUM(REQ.NU_REQ_CANTIDAD_REQUERIDA) AS CANTIDAD 
				from COM_TA_REQUERIMIENTOS REQ, INT_ARTICULOS ART 
				where REQ.ART_CODIGO=ART.ART_CODIGO and REQ.DT_REQ_FECHA_REQUERIDA between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' 
				group by REQ.ART_CODIGO, ART.ART_DESCRIPCION 
				order by REQ.ART_CODIGO
				".$bddsql."  ";
	
	}
else
	{
	$v_sql1="select REQ.ART_CODIGO, 
				ART.ART_DESCRIPCION, 
				SUM(REQ.NU_REQ_CANTIDAD_REQUERIDA) AS CANTIDAD 
				from COM_TA_REQUERIMIENTOS REQ, INT_ARTICULOS ART 
				where REQ.ART_CODIGO=ART.ART_CODIGO and REQ.DT_REQ_FECHA_REQUERIDA between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' 
						and REQ.CH_REQ_ALMACEN='$v_almacen' 
				group by REQ.ART_CODIGO, ART.ART_DESCRIPCION 
				order by REQ.ART_CODIGO
				".$bddsql."  ";
	}

//echo $v_sql1;				
$v_xsql1=pg_exec($conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);

$chk='checked';
$v_irow1=0;

if($v_ilimit1>0) {
	while($v_irow1<$v_ilimit1) {
		$c_codigo=pg_result($v_xsql1,$v_irow1,'ART_CODIGO');
		$c_descripcion=pg_result($v_xsql1,$v_irow1,'ART_DESCRIPCION');
		$c_cantidad=pg_result($v_xsql1,$v_irow1,'CANTIDAD');
		if ( trim($v_almacen)=="") {
			if ( $checkbox[$v_irow1]==$c_codigo ){
				$chk='checked';
				}
			else{
				$chk=' ';
				}
			}
	    echo '<tr>';
		if ( trim($v_almacen)==""){
			echo "<td><input type='checkbox' name='checkbox[$v_irow1]' value='$c_codigo' onclick='document.f_name2.submit()' $chk ></td>";
			}
		echo "<td>&nbsp;".$c_codigo."</td>";
		echo '<td bgcolor="" onMouseOver="window.status=\'Codigo de Item\'; overlib(\'-'.$c_codigo.'-\'); return true;" onMouseOut="window.status=\'\'; nd(); return true;"> ';
		echo '&nbsp;'.$c_descripcion;
		echo '</td>';
		echo "<td align='right'>&nbsp;".$c_cantidad."</td>";
		echo "</tr>";
		if ( trim($v_almacen)==""){
			if ( $checkbox[$v_irow1]==$c_codigo ){
				$v_sql2="select REQ.CH_REQ_ALMACEN, 
							SUM(REQ.NU_REQ_CANTIDAD_REQUERIDA) AS CANTIDAD 
							from COM_TA_REQUERIMIENTOS REQ 
							where REQ.DT_REQ_FECHA_REQUERIDA between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' 
								and REQ.ART_CODIGO='$c_codigo' 
							group by REQ.CH_REQ_ALMACEN 
							".$bddsql."  ";
				$v_xsql2=pg_exec($conector_id,$v_sql2);
				$v_ilimit2=pg_numrows($v_xsql2);
				$v_irow2=0;
				if($v_ilimit2>0) {
					while($v_irow2<$v_ilimit2){
						$c_almacen2=pg_result($v_xsql2,$v_irow2,'CH_REQ_ALMACEN');
						$c_cantidad2=pg_result($v_xsql2,$v_irow2,'CANTIDAD');
						// $v_sql3="select TAB_DESCRIPCION from int_tabla_general where tab_tabla='ALMA' and tab_elemento='$c_almacen2' and tab_car_02='1'";
						$v_sql3="select ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$almacen."%' and ch_clase_almacen='1' ";
						$v_xsql3=pg_exec($conector_id,$v_sql3);
						if( pg_numrows($v_xsql3)>0) {	$c_desc_almacen2=pg_result($v_xsql3,0,0); }
						else { $c_desc_almacen2='NO EXISTE'; }
						echo "<tr>";
						echo "<td>&nbsp;</td>";
						echo "<td>$c_desc_almacen2</td>";
						echo "<td align='right'>$c_cantidad2</td>";
						echo "</tr>";
						$v_irow2++;
						}
					}
				}
			}
		$v_irow1++;
		}
	}
?>
	<tr> 
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Print"></td>
	</tr>
</table>


</form>
</html>