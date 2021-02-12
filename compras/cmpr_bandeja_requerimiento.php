<?php
//include("../valida_sess.php");
include("../menu_princ.php");
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


// carga los almacenes en un dropdown 
// $v_xsqlalma=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='ALMA' and tab_car_02='1' order by cod");
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}

?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
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
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=f_name.v_almacen","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}
function enviadatos(){
	document.f_name.submit()
	}

</script> 

</head>

<body onfocus="mueveReloj('f_name.reloj'); activa()"> 
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name" action="../compras/cmpr_bandeja_requerimiento.php" method="post">

BANDEJA DE REQUERIMIENTOS DE LAS ESTACIONES <?php echo $v_fecha_desde; ?> HASTA <?php echo $v_fecha_hasta; ?> <BR>

<?php
// $v_sql="select  TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$almacen."%' ";
$v_sql="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$almacen."%' and ch_clase_almacen='1' ";

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

		<th>ALMACEN:</th>
		<td width="118" valign="top">  
		<select name="v_almacen" >
			<?php 

			echo "<option value='    ' selected >&nbsp;&nbsp;&nbsp;&nbsp; -- TODOS </option>";	
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

		
		<th><input type="submit" name="boton" value="Consultar">
		</th>
	</tr>
</table>

<p>
  <iframe name="hola" src="cmpr_bandeja_requerimiento_1.php?v_fecha_desde=<?php echo $v_fecha_desde; ?>&v_fecha_hasta=<?php echo $v_fecha_hasta; ?>&v_almacen=<?php echo $v_almacen?>" width="1000" height="320" style="overflow:hidden" scrolling="auto" frameborder="0"></iframe>
</p>
</form>
</body>
</html>
<?php 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
