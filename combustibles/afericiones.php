<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

extract($_GET);
extract($_POST);
extract($_REQUEST);

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="") {
	$almacen="001";
}

if($boton=="Imprimir" or $boton=="Print") {
	echo('<script languaje="JavaScript">');
	echo("	location.href='afericiones_reporte.php' " );
	echo('</script>');
}

if(strlen($v_fecha_desde)==0 or strlen($v_fecha_hasta)==0 ) {
	$dia_actual=1;
	$mes=date("m");
	$ano=date("Y");
	$v_fecha_desde=date("Y-m")."-01";
	$ultimo_dia=ultimoDia($mes,$ano);
	$v_fecha_hasta=date("Y/m")."-".$ultimo_dia;
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
}

if (false) {
	$sqladd=" AND dia BETWEEN '$v_fecha_desde' AND '$v_fecha_hasta' ";	
	$v_sql="SELECT
				es,
				caja,
				trans,
				dia,
				fecha,
				turno,
				pump,
				veloc,
				lineas,
				codigo,
				cantidad,
				precio,
				igv,
				importe,
				responsable
			FROM
				pos_ta_afericiones  
			WHERE
				true ".$sqladd." ".$bddsql."
			";
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_num_rows($v_xsql);
	if($v_ilimit>0) { $numeroRegistros=$v_ilimit; }
}

if($boton=="Consultar" OR (strlen($v_fecha_desde)>0 AND strlen($v_fecha_hasta)>0) ) {
	$sqladd=" AND dia between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' "; 	
	$v_sql="SELECT
				es,
				caja,
				trans,
				dia,
				fecha,
				turno,
				pump,
				veloc,
				lineas,
				codigo,
				cantidad,
				precio,
				igv,
				importe,
				responsable
			FROM
				pos_ta_afericiones  
			WHERE
				true ".$sqladd." ".$bddsql."
			";
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_num_rows($v_xsql);
	if($v_ilimit>0) { $numeroRegistros=$v_ilimit; }
}
?>

<html>
	<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
	<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
	<head>
		<title>sistemaweb</title>
		<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
		<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
		<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
		<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
		<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
		<script> 
		function activa() {
			// carga de frente el formulario con el foco en diad
			document.f_name.v_fecha_desde.select()
			document.f_name.v_fecha_desde.focus()
		}
		function cargavalor(valor) {
			// carga de frente el formulario con el foco en diad
			document.f_name.m_clave.value = valor;
			//document.f_name.submit();
		}
		</script> 
	</head>
	<body onfocus="mueveReloj('f_name.reloj'); activa()"> 
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<form name="f_name" action="" method="post" style="background:#FFFFFF">
		<h2 align="center" style="color:#336699"><b> AFERICIONES DESDE <?php echo $v_fecha_desde ; ?> HASTA <?php echo $v_fecha_hasta; ?><br></b></h2>
		<?php
		$v_sql="SELECT ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen LIKE '%".$almacen."%' AND ch_clase_almacen='1' ";
		$v_xsql=pg_query($conector_id,$v_sql);
		if(pg_numrows($v_xsql)>0) {	$m_descalma=pg_result($v_xsql,0,0);	}
		?>
		<h2 align="center" style="color:#336699"><b> 
			ALMACEN ORIGEN <?php echo $almacen;?> <?php echo $m_descalma; ?>
			<input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_name.reloj.blur()" >
			<input type="hidden" name="m_clave" size="16" maxlength="30" value='<?php echo $m_clave ; ?>'  tabindex="1"   >
		</b></h2>
		<table align="center">
			<tr> 
				<th colspan="5">CONSULTAR POR RANGO DE FECHAS </th>
			</tr>
			<tr> 
				<th>DESDE:</th>
				<th>
					<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" readonly >
					<a href="javascript:show_calendar('f_name.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
						<img src="/sistemaweb/images/showcalendar.gif">
					</a>
				</th>
				<th>HASTA:</th>
				<th>
					<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" readonly>
						<a href="javascript:show_calendar('f_name.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
							<img src="/sistemaweb/images/showcalendar.gif">
						</a>
				</th>
				<th><input type="submit" name="boton" value="Consultar"></th>
			</tr>
		</table>

	<?php
	$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta;
	include("../maestros/pagina.php");
	$bddsql=" limit $tamPag offset $limitInf ";
	?>

		<table align="center">
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><input type="submit" name="boton" value="Imprimir"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th class="grid_cabecera">E/S</th>
				<th class="grid_cabecera">POS</th>
				<th class="grid_cabecera">TRANSAC</th>
				<th class="grid_cabecera">DIA</th>
				<th class="grid_cabecera">FECHA/HORA</th>
				<th class="grid_cabecera">TURNO</th>
				<th class="grid_cabecera">LADO</th>
				<th class="grid_cabecera">VELOC</th>
				<th class="grid_cabecera">LINEAS</th>
				<th class="grid_cabecera">CÓDIGO</th>
				<th class="grid_cabecera">CANTIDAD</th>
				<th class="grid_cabecera">PRECIO</th>
				<th class="grid_cabecera">IGV</th>
				<th class="grid_cabecera">TOTAL</th>
				<th class="grid_cabecera">RESPONSABLE</th>
			</tr>
		<?php
		if(strlen($v_fecha_desde)==0 or strlen($v_fecha_hasta)==0 ) {
			$dia_actual = 1; 
			$mes=date("m");
			$ano=date("Y");
			$v_fecha_desde=date("Y-m")."-01";
			$ultimo_dia = ultimoDia($mes,$ano);
			$v_fecha_hasta=date("Y-m")."-".$ultimo_dia;
			$v_fecha_desde=date("d/m/Y");
			$v_fecha_hasta=date("d/m/Y");
		}
		$sqladd=" AND dia between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' "; 

		$v_sql="SELECT
					es,
					caja,
					trans,
					dia,
					fecha,
					turno,
					pump,
					veloc,
					lineas,
					codigo,
					cantidad,
					precio,
					igv,
					importe,
					responsable
				FROM
					pos_ta_afericiones
				WHERE
					true ".$sqladd." ".$bddsql."
				";

		$v_xsql=pg_exec($conector_id,$v_sql);
		$ilimit=pg_numrows($v_xsql);

		if($ilimit>0) {
			while($irow2<$ilimit) {
				$a0=pg_result($v_xsql,$irow2,0);
				$a1=pg_result($v_xsql,$irow2,1);
				$a2=pg_result($v_xsql,$irow2,2);
				$a3=pg_result($v_xsql,$irow2,3);
				$a4=pg_result($v_xsql,$irow2,4);
				$a5=pg_result($v_xsql,$irow2,5);
				$a6=pg_result($v_xsql,$irow2,6);
				$a7=pg_result($v_xsql,$irow2,7);
				$a8=pg_result($v_xsql,$irow2,8);
				$a9=pg_result($v_xsql,$irow2,9);
				$a10=pg_result($v_xsql,$irow2,10);
				$a11=pg_result($v_xsql,$irow2,11);
				$a12=pg_result($v_xsql,$irow2,12);
				$a13=pg_result($v_xsql,$irow2,13);
				$a14=pg_result($v_xsql,$irow2,14);

				echo "<tr><td>".$a0."</td>";
				echo "<td>".$a1."</td>";
				echo "<td>".$a2."</td>";
				echo "<td>".$a3."</td>";
				echo "<td>".$a4."</td>"; 
				echo "<td>".$a5."</td>";
				echo "<td>".$a6."</td>";
				echo "<td>".$a7."</td>";
				echo "<td>".$a8."</td>";
				echo "<td>".$a9."</td>";
				echo "<td>".$a10."</td>";
				echo "<td>".$a11."</td>";
				echo "<td>".$a12."</td>";
				echo "<td>".$a13."</td>";
				echo "<td>".$a14."</td></tr>";

				$irow2++;
			}
		}
		?>
		</table>
		</form>
	</body>
</html>

<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
//$clase_error->_error();

