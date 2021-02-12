<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
//$clase_error = new OpensoftError;

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


switch($boton){

	case "Consultar Pendientes":
		$v_filtro_pre = "S";
	break;

	case "Consultar Todos":
		$v_filtro_pre = "N";
	break;
	
	default:
		$v_filtro_pre = "N";
	break;

}
?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>acosa.css" type="text/css">
<head>
<title>ACOSA</title>
<script language="JavaScript" src="/acosa/clases/calendario.js"></script>
<script language="JavaScript" src="/acosa/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/acosa/clases/reloj.js"></script>
<script language="JavaScript" src="/acosa/compras/validacion.js"></script>
<script language="JavaScript" src="/acosa/compras/valfecha.js"></script>
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
//	href='cpag_doc_por_pagar_reporte.php?v_fecha_desde='<?php echo $v_fecha_desde; ?>'&v_fecha_hasta=<?php echo $v_fecha_hasta; ?>&v_almacen=<?php echo $v_almacen ?>&v_proveedor=<?php echo $v_proveedor ?>'"
}

</script>

</head>

<!--- <body onfocus="mueveReloj('f_name.reloj'); activa()">
 --->
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name" action="ccob_doc_por_pagar.php" method="post">

REGISTRO DE CUENTAS POR PAGAR <?php echo $v_fecha_desde; ?> HASTA <?php echo $v_fecha_hasta; ?> <BR>

<?php
$v_sql="select trim(CH_ALMACEN) as CODIGO,CH_NOMBRE_ALMACEN
		from INV_TA_ALMACENES
		where CH_CLASE_ALMACEN='1' and CH_ALMACEN like '%".$almacen."%' ";
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
  <table border="1">
	<tr>
		<th colspan="6">CONSULTAR POR RANGO DE FECHAS DE REGISTRO</th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
	<!--- <input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>' >  --->
		<a href="javascript:show_calendar('f_name.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/acosa/images/show-calendar.gif" width=24 height=22 border=0></a>
		</th>

		<th>HASTA:</th>
		<th>
		<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_name.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/acosa/images/show-calendar.gif" width=24 height=22 border=0></a>
		</th>

		<th>Proveedor:</th>
		<th>
		<input type="text" name="v_proveedor" size="16" maxlength="6" value='<?php echo $v_proveedor ; ?>'  tabindex="2" >
		<a><img src="/acosa/images/show-calendar.gif" width=24 height=22 border=0></a>

		</th>
	<tr>
		<th colspan="4">ALMACEN:
		<select name="v_almacen" >
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
		<th colspan="2"><input type="submit" name="boton" value="Consultar Todos">
		</th>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<th><!--<input type="button" name="agregar" value="Agregar" onClick="javascript:document.location.href='cpag_inclu_fac.php'">--></td>
		<td>&nbsp;</td>
		<th><!--<input type="button" value="Reporte" onClick="window.open('<?php
		$url_reporte = "ccob_doc_por_pagar_reporte.php?v_fecha_desde=$v_fecha_desde&v_fecha_hasta=$v_fecha_hasta&v_almacen=$v_almacen&v_proveedor=$v_proveedor','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0";
		echo $url_reporte;
		?>');">--></td>
		<td colspan="2"><div align="center">
          <input type="submit" name="boton" value="Consultar Pendientes">
        </div></td>
</table>
	<iframe name="frame_ccob_doc_por_pagar" src="ccob_doc_por_pagar_1.php?v_fecha_desde=<?php echo $v_fecha_desde; ?>&v_fecha_hasta=<?php echo $v_fecha_hasta; ?>&v_almacen=<?php echo $v_almacen ?>&v_proveedor=<?php echo $v_proveedor ?>&v_filtro_pre=<?php echo $v_filtro_pre;?>" width="1000" height="320" style="overflow:hidden" scrolling="auto" frameborder="0"></iframe>
</form>
</body>
</html>
<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
//$clase_error->_error();
