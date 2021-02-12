<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}

// carga los almacenes en un dropdown
$v_xsqlalma=pg_exec("select trim(CH_ALMACEN) as CODIGO,CH_NOMBRE_ALMACEN from INV_TA_ALMACENES where CH_CLASE_ALMACEN='1' order by CODIGO");

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}

?>
<html>
<head>
<title>ACOSA</title>
<link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script>
function activa(){
	// carga de frente el formulario con el foco en diad
	document.f_name.v_fecha_desde.select()
	document.f_name.v_fecha_desde.focus()
	}
function enviadatos(){
	document.f_name.submit()
	}

function abrirReporte()
{
//	href='cpag_doc_por_pagar_reporte.php?v_fecha_desde='<?php echo $v_fecha_desde; ?>'&v_fecha_hasta=<?php echo $v_fecha_hasta; ?>&v_almacen=<?php echo $v_almacen; ?>&v_proveedor=<?php echo $v_proveedor; ?>'"
}

</script>

</head>

<!--- <body onfocus="mueveReloj('f_name.reloj'); activa()">
 --->
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name" action="cpag_doc_por_pagar.php" method="post">

REGISTRO DE CUENTAS POR PAGAR <?php echo $v_fecha_desde; ?> HASTA <?php echo $v_fecha_hasta; ?> <BR>

<?php
$v_sql="SELECT ".
               "trim(ch_almacen) as codigo,ch_nombre_almacen ".
       "FROM inv_ta_almacenes ".
       "WHERE ch_clase_almacen='1' ".
       "AND ch_almacen ".
       "LIKE '%".$almacen."%' ";

$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_descalma=pg_result($v_xsql,0,1);	}
?>

ALMACEN ACTUAL <?php echo $almacen;?> 	<?php echo $v_descalma; ?> <input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_name.reloj.blur()" >

<hr noshade>
  <?php
if ( is_null($v_almacen) )
	{
	$v_almacen=$almacen;
	}

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}
?>
  <table border="0" align="center" cellpadding="3" cellspacing="3">
	<tr>
		<th colspan="6" class="form_label">CONSULTAR POR RANGO DE FECHAS DE REGISTRO</th>
	</tr>
	<tr>
		<td class="form_label">DESDE </td>
		<td>:
		<input class="form_input" type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
	<!--- <input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>' >  --->
		<a href="javascript:show_calendar('f_name.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	        <img src="/sistemaweb/images/show-calendar.gif" width=15 height=15 border=0></a>
		</td>

		<td class="form_label">HASTA</td>
		<td>:
		<input class="form_input" type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_name.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	        <img src="/sistemaweb/images/show-calendar.gif" width=15 height=15 border=0></a>
		</th>

		<td class="form_label">PROVEEDOR</td>
		<td>:
		<input class="form_input" type="text" name="v_proveedor" size="8" maxlength="6" value='<?php echo $v_proveedor ; ?>'  tabindex="2" >
		<!--<a><img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>-->
		</td>
	</tr>

	<tr>
		<td class="form_label">T. DOCUM. </td>
		<td>:
		<input class="form_input" type="text" name="tip_docum" size="4" maxlength="3" value='<?php echo $_REQUEST['tip_docum'];?>'>
		</td>

		<td class="form_label">N&#186; DOCUM.</td>
		<td>:
		<input class="form_input" type="text" name="nro_docum" size="13" maxlength="13" value='<?php echo $_REQUEST['nro_docum'];?>'>
		</td>
		<td class="form_label"></td>
		<td></td>
	</tr>

	<tr>
	
	<td class="form_label">ALMACEN</td>
	<td colspan="3">:
	<select name="v_almacen" class="form_combo">
		<?php

		echo "<option value=' ' selected >&nbsp;&nbsp;&nbsp;&nbsp; -- TODOS </option>";
		for($i=0;$i<pg_numrows($v_xsqlalma);$i++){
		    $k_alma1 = pg_result($v_xsqlalma,$i,0);
		    $k_alma2 = pg_result($v_xsqlalma,$i,1);
		    if ($k_alma1==$v_almacen) {
		      echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";
		    }
		    else {
		      echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";
		    }
		}
		?>
        </select></td>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="6" align="center">
	<input class="form_button" type="submit" name="boton" value="Consultar">
	&nbsp;&nbsp;&nbsp;
	<input class="form_button" type="button" name="agregar" value="Agregar" onClick="javascript:document.location.href='cpag_inclu_fac.php'">
	&nbsp;&nbsp;&nbsp;
	<input class="form_button" type="button" value="Reporte" onClick="window.open('<?php
	$url_reporte = "cpag_doc_por_pagar_reporte.php?v_fecha_desde=$v_fecha_desde&v_fecha_hasta=$v_fecha_hasta&v_almacen=$v_almacen&v_proveedor=$v_proveedor','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0";
	echo $url_reporte;
	?>');"></td>
</table>
	<iframe name="frame_cpag_doc_por_pagar" src="cpag_doc_por_pagar_1.php?v_fecha_desde=<?php echo $v_fecha_desde; ?>&v_fecha_hasta=<?php echo $v_fecha_hasta; ?>&v_almacen=<?php echo $v_almacen; ?>&nro_docum=<?php echo $_REQUEST['nro_docum'];?>&tip_docum=<?php echo $_REQUEST['tip_docum'];?>&v_proveedor=<?php echo $v_proveedor; ?>" width="1200" height="320" scrolling="auto" frameborder="0"></iframe>
	<br/><br/><br/><br/>
</form>
</body>
</html>
<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
